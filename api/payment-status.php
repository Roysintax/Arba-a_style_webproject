<?php
/**
 * API - Check Payment Status
 * Returns whether an order has been paid
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$orderNumber = $_GET['order'] ?? '';

if (!$orderNumber) {
    echo json_encode(['paid' => false, 'message' => 'Order number required']);
    exit;
}

// Check if payment token has been used (payment completed)
$stmt = db()->prepare("SELECT is_used FROM payment_tokens WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$token = $stmt->fetch();

if ($token && $token['is_used']) {
    // Clear session if exists
    if (isset($_SESSION['pending_payment']) && $_SESSION['pending_payment']['order_number'] === $orderNumber) {
        unset($_SESSION['pending_payment']);
        clearCart();
        $_SESSION['order_success'] = ['order_number' => $orderNumber, 'total' => 0];
    }
    
    echo json_encode(['paid' => true]);
} else {
    // Also check order status directly
    $stmt = db()->prepare("SELECT status FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    if ($order && $order['status'] === 'processing') {
        echo json_encode(['paid' => true]);
    } else {
        echo json_encode(['paid' => false]);
    }
}

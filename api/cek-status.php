<?php
/**
 * API - Cek Status Pembayaran
 * Digunakan untuk AJAX polling
 */

require_once '../config/database.php';

$orderNumber = $_GET['order'] ?? '';

if (!$orderNumber) {
    echo 'error';
    exit;
}

// Cek status order di database
$stmt = db()->prepare("SELECT status FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if ($order && $order['status'] === 'processing') {
    echo 'success';
} else {
    echo 'pending';
}

<?php
/**
 * API - Generate QR Code for Payment
 * Creates QR code image and returns as base64
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$orderNumber = $_GET['order'] ?? '';
$amount = $_GET['amount'] ?? 0;

if (!$orderNumber) {
    echo json_encode(['success' => false, 'message' => 'Order number required']);
    exit;
}

// Create payment token and store in database
$token = bin2hex(random_bytes(16));
$expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Check if token exists, update or insert
$stmt = db()->prepare("SELECT id FROM payment_tokens WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$existing = $stmt->fetch();

if ($existing) {
    db()->prepare("UPDATE payment_tokens SET token = ?, expires_at = ?, is_used = 0 WHERE order_number = ?")
        ->execute([$token, $expiresAt, $orderNumber]);
} else {
    db()->prepare("INSERT INTO payment_tokens (order_number, token, amount, expires_at) VALUES (?, ?, ?, ?)")
        ->execute([$orderNumber, $token, $amount, $expiresAt]);
}

// Generate QR code URL - this URL will be encoded in QR
$scanUrl = BASE_URL . '/api/payment-verify.php?token=' . $token;

// Use Google Charts API for QR code generation (reliable, no library needed)
$qrSize = 200;
$qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=' . $qrSize . 'x' . $qrSize . '&chl=' . urlencode($scanUrl) . '&choe=UTF-8';

echo json_encode([
    'success' => true,
    'qr_url' => $qrUrl,
    'token' => $token,
    'scan_url' => $scanUrl,
    'expires_at' => $expiresAt
]);

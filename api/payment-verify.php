<?php
/**
 * API - Verify Payment via QR Scan
 * When user scans QR code, this endpoint processes the payment
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';
$orderNumber = $_GET['order'] ?? '';

// Helper function to show success page
function showSuccessPage($orderNumber, $amount) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pembayaran Berhasil - Toko Islami</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: linear-gradient(135deg, #1A5276, #27AE60);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .success-card {
                background: white;
                border-radius: 20px;
                padding: 50px;
                text-align: center;
                max-width: 450px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .check-icon {
                width: 100px;
                height: 100px;
                background: linear-gradient(135deg, #27AE60, #2ECC71);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 25px;
                font-size: 50px;
                color: white;
                animation: scaleIn 0.5s ease-out;
            }
            @keyframes scaleIn {
                0% { transform: scale(0); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            h1 { color: #27AE60; margin-bottom: 15px; }
            .order-info {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 12px;
                margin: 25px 0;
            }
            .order-number {
                font-size: 1.3rem;
                font-weight: 700;
                color: #1A5276;
                margin-bottom: 10px;
            }
            .amount {
                font-size: 1.8rem;
                font-weight: 700;
                color: #27AE60;
            }
            .message { color: #666; margin-bottom: 25px; line-height: 1.6; }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #1A5276, #2980B9);
                color: white;
                padding: 15px 40px;
                border-radius: 30px;
                text-decoration: none;
                font-weight: 600;
            }
            .close-note { margin-top: 20px; font-size: 0.85rem; color: #999; }
        </style>
    </head>
    <body>
        <div class="success-card">
            <div class="check-icon">✓</div>
            <h1>Pembayaran Berhasil!</h1>
            <p class="message">Alhamdulillah, pembayaran Anda telah kami terima. Pesanan sedang diproses.</p>
            
            <div class="order-info">
                <div class="order-number">#<?= htmlspecialchars($orderNumber) ?></div>
                <div class="amount"><?= formatRupiah($amount) ?></div>
            </div>
            
            <a href="<?= BASE_URL ?>" class="btn">Kembali ke Beranda</a>
            <p class="close-note">Anda dapat menutup halaman ini.</p>
        </div>
        
        <script>
            if (window.opener) {
                window.opener.postMessage({ type: 'payment_success', order: '<?= $orderNumber ?>' }, '*');
            }
        </script>
    </body>
    </html>
    <?php
}

// Helper function to show error page
function showErrorPage($message) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Toko Islami</title>
        <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: linear-gradient(135deg, #E74C3C, #C0392B);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-card {
                background: white;
                border-radius: 20px;
                padding: 50px;
                text-align: center;
                max-width: 450px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .error-icon { font-size: 4rem; margin-bottom: 20px; }
            h1 { color: #E74C3C; margin-bottom: 15px; }
            p { color: #666; margin-bottom: 25px; }
            .btn {
                display: inline-block;
                background: #3498DB;
                color: white;
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">❌</div>
            <h1>Pembayaran Gagal</h1>
            <p><?= htmlspecialchars($message) ?></p>
            <a href="<?= BASE_URL ?>" class="btn">Kembali ke Beranda</a>
        </div>
    </body>
    </html>
    <?php
}

// If order number is provided directly (from QR scan)
if ($orderNumber && !$token) {
    // Verify order exists
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ? AND status = 'pending'");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    if ($order) {
        // Update order status
        db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
        
        // Show success page
        showSuccessPage($orderNumber, $order['total_amount']);
        exit;
    } else {
        showErrorPage('Pesanan tidak ditemukan atau sudah diproses.');
        exit;
    }
}

// Token-based verification
if (!$token) {
    showErrorPage('Token Tidak Valid');
    exit;
}

// Get payment token from database (try-catch for if table doesn't exist)
try {
    $stmt = db()->prepare("SELECT * FROM payment_tokens WHERE token = ? AND is_used = 0 AND expires_at > NOW()");
    $stmt->execute([$token]);
    $paymentToken = $stmt->fetch();
    
    if (!$paymentToken) {
        showErrorPage('Link pembayaran sudah digunakan atau kadaluarsa.');
        exit;
    }
    
    $orderNumber = $paymentToken['order_number'];
    
    // Mark token as used
    db()->prepare("UPDATE payment_tokens SET is_used = 1, used_at = NOW() WHERE id = ?")->execute([$paymentToken['id']]);
    
    // Update order status
    db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
    
    // Get order details
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    showSuccessPage($orderNumber, $order ? $order['total_amount'] : 0);
    
} catch (Exception $e) {
    showErrorPage('Terjadi kesalahan sistem.');
}

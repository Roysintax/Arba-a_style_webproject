<?php
/**
 * Halaman Bayar via QR Scan
 * Ketika HP scan QR, halaman ini akan terbuka dan memproses pembayaran
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$orderNumber = $_GET['order'] ?? '';

if (!$orderNumber) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Toko Islami</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #f44336; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
            .card { background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 400px; }
            .icon { font-size: 4rem; }
            h1 { color: #f44336; }
            a { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #2196F3; color: white; text-decoration: none; border-radius: 25px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">❌</div>
            <h1>Order Tidak Valid</h1>
            <p>Link pembayaran tidak valid.</p>
            <a href="<?= BASE_URL ?>">Kembali</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Cek order di database
$stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Toko Islami</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #f44336; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
            .card { background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 400px; }
            .icon { font-size: 4rem; }
            h1 { color: #f44336; }
            a { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #2196F3; color: white; text-decoration: none; border-radius: 25px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">❌</div>
            <h1>Order Tidak Ditemukan</h1>
            <p>Pesanan dengan nomor #<?= htmlspecialchars($orderNumber) ?> tidak ditemukan.</p>
            <a href="<?= BASE_URL ?>">Kembali</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Jika order sudah diproses
if ($order['status'] !== 'pending') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sudah Dibayar - Toko Islami</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #4CAF50, #8BC34A); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
            .card { background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
            .icon { font-size: 4rem; }
            h1 { color: #4CAF50; }
            a { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #1A5276; color: white; text-decoration: none; border-radius: 25px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">✅</div>
            <h1>Sudah Dibayar</h1>
            <p>Order #<?= htmlspecialchars($orderNumber) ?> sudah diproses sebelumnya.</p>
            <a href="<?= BASE_URL ?>">Kembali ke Beranda</a>
            <p style="margin-top: 15px; font-size: 0.85rem; color: #666;">Redirect dalam <span id="countdown">3</span> detik...</p>
        </div>
        
        <script>
            let seconds = 3;
            const countdownEl = document.getElementById('countdown');
            const interval = setInterval(function() {
                seconds--;
                countdownEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = '<?= BASE_URL ?>';
                }
            }, 1000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Proses pembayaran - Update status jadi 'processing'
db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);

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
            border-radius: 25px;
            padding: 50px 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .check-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #27AE60, #2ECC71);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.6s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .check-circle::after {
            content: '✓';
            font-size: 50px;
            color: white;
        }
        h1 {
            color: #27AE60;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .order-box {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .order-label {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .order-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1A5276;
            margin-bottom: 15px;
        }
        .amount {
            font-size: 2rem;
            font-weight: 700;
            color: #27AE60;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1A5276, #2980B9);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(26, 82, 118, 0.4);
        }
        .note {
            margin-top: 25px;
            font-size: 0.85rem;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="check-circle"></div>
        <h1>Pembayaran Berhasil!</h1>
        <p class="message">Alhamdulillah, pembayaran Anda telah kami terima.<br>Pesanan sedang diproses.</p>
        
        <div class="order-box">
            <div class="order-label">Nomor Pesanan</div>
            <div class="order-number">#<?= htmlspecialchars($orderNumber) ?></div>
            <div class="order-label">Total Pembayaran</div>
            <div class="amount"><?= formatRupiah($order['total_amount']) ?></div>
        </div>
        
        <a href="<?= BASE_URL ?>" class="btn">Kembali ke Beranda</a>
        
        <p class="note">Anda akan diarahkan ke halaman utama dalam <span id="countdown">3</span> detik...</p>
    </div>
    
    <script>
        // Countdown dan redirect otomatis ke halaman utama
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        
        const interval = setInterval(function() {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = '<?= BASE_URL ?>';
            }
        }, 1000);
    </script>
</body>
</html>

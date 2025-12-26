<?php
/**
 * Payment - E-Wallet
 * Toko Islami - Payment Page
 */

$pageTitle = 'Pembayaran E-Wallet';
require_once '../../includes/header.php';

// Get order from session
if (!isset($_SESSION['pending_payment'])) {
    setFlash('warning', 'Tidak ada pesanan yang menunggu pembayaran');
    header('Location: cart.php');
    exit;
}

$pendingPayment = $_SESSION['pending_payment'];
$orderNumber = $pendingPayment['order_number'];
$total = $pendingPayment['total'];

// E-wallet list
$ewallets = [
    ['id' => 'gopay', 'name' => 'GoPay', 'logo' => '🟢', 'color' => '#00AA13'],
    ['id' => 'ovo', 'name' => 'OVO', 'logo' => '🟣', 'color' => '#4C3494'],
    ['id' => 'dana', 'name' => 'DANA', 'logo' => '🔵', 'color' => '#118EEA'],
    ['id' => 'shopeepay', 'name' => 'ShopeePay', 'logo' => '🟠', 'color' => '#EE4D2D'],
    ['id' => 'linkaja', 'name' => 'LinkAja', 'logo' => '🔴', 'color' => '#E02B20'],
];

$selectedWallet = isset($_GET['wallet']) ? $_GET['wallet'] : 'gopay';

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
    unset($_SESSION['pending_payment']);
    clearCart();
    
    $_SESSION['order_success'] = [
        'order_number' => $orderNumber,
        'total' => $total
    ];
    header('Location: checkout.php?success=1');
    exit;
}

// Handle QR scan verification (AJAX)
if (isset($_GET['verify_qr'])) {
    header('Content-Type: application/json');
    $code = $_GET['verify_qr'];
    
    if ($code === $orderNumber) {
        db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
        unset($_SESSION['pending_payment']);
        clearCart();
        
        echo json_encode(['success' => true, 'redirect' => 'checkout.php?success=1']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode tidak valid']);
    }
    exit;
}

// Get selected wallet info
$currentWallet = array_filter($ewallets, fn($w) => $w['id'] === $selectedWallet);
$currentWallet = reset($currentWallet) ?: $ewallets[0];
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, <?= $currentWallet['color'] ?>, <?= adjustBrightness($currentWallet['color'], -20) ?>); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <a href="checkout.php" style="color: rgba(255,255,255,0.7);">Checkout</a>
            <span>›</span>
            <span style="color: #FFD700;">E-Wallet</span>
        </div>
        <h1>📱 Pembayaran E-Wallet</h1>
        <p>Selesaikan pembayaran untuk pesanan #<?= $orderNumber ?></p>
    </div>
</section>

<?php
function adjustBrightness($hex, $steps) {
    $hex = ltrim($hex, '#');
    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?>

<!-- Payment Content -->
<section class="section">
    <div class="container">
        <!-- Wallet Selector -->
        <div class="wallet-selector">
            <?php foreach ($ewallets as $wallet): ?>
            <a href="?wallet=<?= $wallet['id'] ?>" 
               class="wallet-pill <?= $selectedWallet === $wallet['id'] ? 'active' : '' ?>"
               style="<?= $selectedWallet === $wallet['id'] ? 'background:' . $wallet['color'] . '; color: white;' : '' ?>">
                <?= $wallet['logo'] ?> <?= $wallet['name'] ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div class="payment-grid">
            <!-- Left: Manual Input -->
            <div class="payment-box">
                <div class="payment-header" style="border-color: <?= $currentWallet['color'] ?>;">
                    <div class="wallet-icon" style="background: <?= $currentWallet['color'] ?>;">
                        <?= $currentWallet['logo'] ?>
                    </div>
                    <h2>Bayar via <?= $currentWallet['name'] ?></h2>
                    <p>Input manual nomor HP</p>
                </div>
                
                <!-- Amount -->
                <div class="amount-box" style="background: linear-gradient(135deg, <?= $currentWallet['color'] ?>, <?= adjustBrightness($currentWallet['color'], -30) ?>);">
                    <div class="amount-label">Total Pembayaran</div>
                    <div class="amount-value"><?= formatRupiah($total) ?></div>
                </div>
                
                <!-- Phone Input Form -->
                <form method="POST" class="manual-form">
                    <div class="form-group">
                        <label>Nomor HP Terdaftar <?= $currentWallet['name'] ?></label>
                        <div class="phone-input">
                            <span class="phone-prefix">+62</span>
                            <input type="tel" name="phone" class="form-control" placeholder="8xxxxxxxxxx" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>PIN <?= $currentWallet['name'] ?> (6 digit)</label>
                        <div class="pin-input">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 1)">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 2)">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 3)">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 4)">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 5)">
                            <input type="password" maxlength="1" class="pin-box" oninput="moveFocus(this, 6)">
                        </div>
                    </div>
                    
                    <button type="submit" name="confirm_payment" class="btn-pay" style="background: <?= $currentWallet['color'] ?>;">
                        💳 Bayar Sekarang
                    </button>
                </form>
                
                <div class="security-note">
                    🔒 Transaksi aman & terenkripsi
                </div>
            </div>
            
            <!-- Right: QR Code -->
            <div class="payment-box qr-box">
                <div class="payment-header">
                    <h2>📱 Scan untuk Bayar</h2>
                    <p>Buka aplikasi <?= $currentWallet['name'] ?></p>
                </div>
                
                <div class="qr-container" style="border-color: <?= $currentWallet['color'] ?>;">
                    <div class="qr-badge" style="background: <?= $currentWallet['color'] ?>;">
                        <?= $currentWallet['logo'] ?> <?= $currentWallet['name'] ?>
                    </div>
                    <?php 
                    // Generate QR URL - Link ke halaman bayar.php
                    // Menggunakan BASE_URL agar sesuai dengan domain (localhost atau arbastyle.wuaze.com)
                    $qrData = BASE_URL . "/pages/shop/bayar.php?order=" . urlencode($orderNumber);
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                    ?>
                    <div class="qr-code" id="qr-code">
                        <img src="<?= $qrUrl ?>" alt="QR Code" style="width: 200px; height: 200px;">
                    </div>
                    <div class="qr-amount"><?= formatRupiah($total) ?></div>
                    <div class="qr-order">#<?= $orderNumber ?></div>
                    <div style="font-size: 0.8rem; color: #666; margin-top: 10px;">
                        📱 Scan dengan HP
                    </div>
                </div>
                
                <div class="qr-steps">
                    <div class="step">
                        <span class="step-num">1</span>
                        <span>Buka aplikasi <?= $currentWallet['name'] ?></span>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <span>Pilih menu "Scan" atau "Bayar"</span>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <span>Arahkan ke QR Code</span>
                    </div>
                    <div class="step">
                        <span class="step-num">4</span>
                        <span>Konfirmasi & selesai!</span>
                    </div>
                </div>
                
                <div class="scan-status" id="scan-status">
                    <div class="pulse"></div>
                    <span>Menunggu pembayaran...</span>
                </div>
                
                <button class="btn-simulate" onclick="simulateScan()" style="background: <?= $currentWallet['color'] ?>;">
                    ✨ Simulasi Scan (Demo)
                </button>
            </div>
        </div>
    </div>
</section>

<style>
.wallet-selector {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.wallet-pill {
    padding: 10px 20px;
    border: 2px solid #ddd;
    border-radius: 25px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: all 0.3s;
}

.wallet-pill:hover, .wallet-pill.active {
    border-color: transparent;
    transform: scale(1.05);
}

.payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.payment-box {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.payment-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #f0f0f0;
}

.wallet-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 15px;
}

.payment-header h2 {
    color: var(--primary-dark);
    margin-bottom: 5px;
}

.amount-box {
    color: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 30px;
}

.amount-label {
    opacity: 0.9;
    font-size: 0.9rem;
}

.amount-value {
    font-size: 2.2rem;
    font-weight: 700;
    margin-top: 5px;
}

.manual-form .form-group {
    margin-bottom: 25px;
}

.manual-form label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
}

.phone-input {
    display: flex;
    align-items: center;
    border: 2px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
}

.phone-prefix {
    background: #f0f0f0;
    padding: 15px;
    font-weight: 600;
    color: #666;
}

.phone-input .form-control {
    border: none;
    border-radius: 0;
}

.pin-input {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.pin-box {
    width: 50px;
    height: 55px;
    text-align: center;
    font-size: 1.5rem;
    border: 2px solid #ddd;
    border-radius: 10px;
    outline: none;
    transition: border-color 0.3s;
}

.pin-box:focus {
    border-color: var(--primary);
}

.btn-pay {
    width: 100%;
    color: white;
    border: none;
    padding: 18px;
    border-radius: 12px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.security-note {
    text-align: center;
    margin-top: 20px;
    color: #27AE60;
    font-size: 0.9rem;
}

/* QR Section */
.qr-box {
    text-align: center;
}

.qr-container {
    background: white;
    border: 4px solid #ddd;
    border-radius: 20px;
    padding: 25px;
    display: inline-block;
    margin-bottom: 25px;
    position: relative;
}

.qr-badge {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 20px;
    border-radius: 20px;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.qr-code {
    width: 200px;
    height: 200px;
    margin: 20px auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-dark);
}

.qr-order {
    color: #666;
    font-family: monospace;
}

.qr-steps {
    text-align: left;
    margin-bottom: 25px;
}

.step {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.step-num {
    width: 30px;
    height: 30px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
}

.scan-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
    color: #666;
}

.pulse {
    width: 12px;
    height: 12px;
    background: #F39C12;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.btn-simulate {
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .payment-grid {
        grid-template-columns: 1fr;
    }
    
    .pin-box {
        width: 40px;
        height: 45px;
    }
}
</style>

<!-- jQuery untuk AJAX Polling -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Cek status pembayaran setiap 2 detik (Realtime simulation)
$(document).ready(function() {
    setInterval(function() {
        $.get("<?= BASE_URL ?>/api/cek-status.php?order=<?= urlencode($orderNumber) ?>", function(data) {
            if (data.trim() == "success") {
                $("#scan-status").html(`
                    <span style="font-size: 1.5rem;">✅</span>
                    <span style="color: #27AE60; font-weight: 700;">PEMBAYARAN BERHASIL!</span>
                `);
                // Redirect ke halaman utama (index) setelah 2 detik
                setTimeout(function() {
                    window.location.href = '<?= BASE_URL ?>';
                }, 2000);
            }
        });
    }, 2000); // 2000ms = 2 detik
});

function moveFocus(current, nextIndex) {
    if (current.value.length === 1) {
        const pins = document.querySelectorAll('.pin-box');
        if (nextIndex < pins.length) {
            pins[nextIndex].focus();
        }
    }
}

function simulateScan() {
    $("#scan-status").html(`
        <div class="pulse" style="background: #3498DB;"></div>
        <span>Memverifikasi...</span>
    `);
    
    // Buka halaman bayar di tab baru (simulasi scan QR)
    // Menggunakan Full URL agar aman di live server
    window.open('<?= BASE_URL ?>/pages/shop/bayar.php?order=<?= urlencode($orderNumber) ?>', '_blank');
}
</script>

<?php require_once '../../includes/footer.php'; ?>

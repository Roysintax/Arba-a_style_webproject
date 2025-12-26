<?php
/**
 * Payment - Transfer Bank
 * Toko Islami - Payment Page
 */

$pageTitle = 'Pembayaran Transfer Bank';
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

// Generate unique payment code
$uniqueCode = rand(100, 999);
$totalWithCode = $total + $uniqueCode;

// Generate virtual account number
$virtualAccount = '888' . substr(str_replace('-', '', $orderNumber), 0, 10) . rand(100, 999);

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Mark order as processing
    db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
    
    // Clear session
    unset($_SESSION['pending_payment']);
    clearCart();
    
    // Set success
    $_SESSION['order_success'] = [
        'order_number' => $orderNumber,
        'total' => $totalWithCode
    ];
    header('Location: checkout.php?success=1');
    exit;
}

// Handle QR scan verification (AJAX)
if (isset($_GET['verify_qr'])) {
    header('Content-Type: application/json');
    $code = $_GET['verify_qr'];
    
    // Check if QR code matches
    if ($code === $orderNumber) {
        // Mark order as processing
        db()->prepare("UPDATE orders SET status = 'processing' WHERE order_number = ?")->execute([$orderNumber]);
        unset($_SESSION['pending_payment']);
        clearCart();
        
        echo json_encode(['success' => true, 'redirect' => 'checkout.php?success=1']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode tidak valid']);
    }
    exit;
}

// Bank list
$banks = [
    ['name' => 'BCA', 'logo' => '🏦', 'account' => '1234567890', 'holder' => 'Toko Islami'],
    ['name' => 'Mandiri', 'logo' => '🏦', 'account' => '0987654321', 'holder' => 'Toko Islami'],
    ['name' => 'BNI', 'logo' => '🏦', 'account' => '1122334455', 'holder' => 'Toko Islami'],
    ['name' => 'BRI', 'logo' => '🏦', 'account' => '5566778899', 'holder' => 'Toko Islami'],
];
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, #1A5276, #2980B9); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <a href="checkout.php" style="color: rgba(255,255,255,0.7);">Checkout</a>
            <span>›</span>
            <span style="color: var(--gold);">Pembayaran</span>
        </div>
        <h1>🏦 Transfer Bank</h1>
        <p>Selesaikan pembayaran untuk pesanan #<?= $orderNumber ?></p>
    </div>
</section>

<!-- Payment Content -->
<section class="section">
    <div class="container">
        <div class="payment-grid">
            <!-- Left: Manual Payment -->
            <div class="payment-box">
                <div class="payment-header">
                    <h2>📝 Pembayaran Manual</h2>
                    <p>Transfer ke rekening berikut</p>
                </div>
                
                <!-- Amount -->
                <div class="amount-box">
                    <div class="amount-label">Total Pembayaran</div>
                    <div class="amount-value"><?= formatRupiah($totalWithCode) ?></div>
                    <div class="amount-note">*Termasuk kode unik Rp <?= number_format($uniqueCode, 0, ',', '.') ?></div>
                </div>
                
                <!-- Bank List -->
                <div class="bank-list">
                    <?php foreach ($banks as $bank): ?>
                    <div class="bank-item" onclick="selectBank(this, '<?= $bank['name'] ?>')">
                        <div class="bank-logo"><?= $bank['logo'] ?></div>
                        <div class="bank-info">
                            <div class="bank-name"><?= $bank['name'] ?></div>
                            <div class="bank-account"><?= $bank['account'] ?></div>
                            <div class="bank-holder">a/n <?= $bank['holder'] ?></div>
                        </div>
                        <button class="btn-copy" onclick="event.stopPropagation(); copyText('<?= $bank['account'] ?>')">📋 Copy</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Virtual Account -->
                <div class="va-box">
                    <div class="va-label">Virtual Account Number</div>
                    <div class="va-number" id="va-number"><?= $virtualAccount ?></div>
                    <button class="btn-copy-va" onclick="copyText('<?= $virtualAccount ?>')">📋 Salin</button>
                </div>
                
                <!-- Confirm Button -->
                <form method="POST">
                    <button type="submit" name="confirm_payment" class="btn-confirm">
                        ✅ Saya Sudah Transfer
                    </button>
                </form>
                
                <div class="payment-note">
                    <p>⚠️ Pastikan transfer sesuai nominal termasuk kode unik</p>
                    <p>⏰ Batas waktu pembayaran: 24 jam</p>
                </div>
            </div>
            
            <!-- Right: QR Code -->
            <div class="payment-box qr-box">
                <div class="payment-header">
                    <h2>📱 Scan QR Code</h2>
                    <p>Pembayaran otomatis via scan</p>
                </div>
                
                <div class="qr-container">
                    <?php 
                    // Generate QR URL - Link ke halaman bayar.php
                    // Menggunakan BASE_URL agar sesuai dengan domain (localhost atau arbastyle.wuaze.com)
                    $qrData = BASE_URL . "/pages/shop/bayar.php?order=" . urlencode($orderNumber);
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                    ?>
                    <div class="qr-code" id="qr-code">
                        <img src="<?= $qrUrl ?>" alt="QR Code" style="width: 200px; height: 200px;">
                    </div>
                    <div class="qr-amount"><?= formatRupiah($totalWithCode) ?></div>
                    <div class="qr-order">#<?= $orderNumber ?></div>
                    <div style="font-size: 0.8rem; color: #666; margin-top: 10px;">
                        📱 Scan dengan HP
                    </div>
                </div>
                
                <div class="qr-instructions">
                    <h4>Cara Pembayaran:</h4>
                    <ol>
                        <li>Buka aplikasi Mobile Banking</li>
                        <li>Pilih menu Scan/QR</li>
                        <li>Arahkan kamera ke QR Code</li>
                        <li>Konfirmasi pembayaran</li>
                        <li>Pembayaran otomatis terverifikasi</li>
                    </ol>
                </div>
                
                <div class="scan-status" id="scan-status">
                    <div class="status-icon">⏳</div>
                    <div class="status-text">Menunggu pembayaran...</div>
                </div>
                
                <!-- Simulate Scan Button (Demo) -->
                <button class="btn-simulate" onclick="simulateScan()">
                    🔍 Simulasi Scan (Demo)
                </button>
            </div>
        </div>
    </div>
</section>

<style>
.payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.payment-box {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.payment-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.payment-header h2 {
    color: var(--primary-dark);
    margin-bottom: 5px;
}

.payment-header p {
    color: #666;
}

.amount-box {
    background: linear-gradient(135deg, #27AE60, #2ECC71);
    color: white;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 25px;
}

.amount-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.amount-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 10px 0;
}

.amount-note {
    font-size: 0.8rem;
    opacity: 0.8;
}

.bank-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.bank-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.bank-item:hover, .bank-item.selected {
    border-color: var(--primary);
    background: #f8fffe;
}

.bank-logo {
    font-size: 2rem;
}

.bank-info {
    flex: 1;
}

.bank-name {
    font-weight: 700;
    color: var(--primary-dark);
}

.bank-account {
    font-family: monospace;
    font-size: 1.1rem;
    color: #333;
}

.bank-holder {
    font-size: 0.85rem;
    color: #666;
}

.btn-copy {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.8rem;
}

.va-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}

.va-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 10px;
}

.va-number {
    font-family: monospace;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-dark);
    letter-spacing: 2px;
    margin-bottom: 10px;
}

.btn-copy-va {
    background: var(--gold);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    cursor: pointer;
}

.btn-confirm {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), #2ECC71);
    color: white;
    border: none;
    padding: 18px;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-confirm:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
}

.payment-note {
    margin-top: 20px;
    padding: 15px;
    background: #FFF9E6;
    border-radius: 10px;
    font-size: 0.85rem;
    color: #856404;
}

.payment-note p {
    margin: 5px 0;
}

/* QR Section */
.qr-box {
    text-align: center;
}

.qr-container {
    background: white;
    border: 3px solid var(--primary);
    border-radius: 15px;
    padding: 25px;
    display: inline-block;
    margin-bottom: 25px;
}

.qr-code {
    width: 200px;
    height: 200px;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    border-radius: 10px;
}

.qr-order {
    font-family: monospace;
    font-size: 1rem;
    color: var(--primary);
    font-weight: 600;
}

.qr-instructions {
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.qr-instructions h4 {
    color: var(--primary-dark);
    margin-bottom: 10px;
}

.qr-instructions ol {
    padding-left: 20px;
    color: #666;
}

.qr-instructions li {
    margin: 8px 0;
}

.scan-status {
    padding: 20px;
    background: #f0f4f8;
    border-radius: 10px;
    margin-bottom: 15px;
}

.status-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.status-text {
    color: #666;
}

.btn-simulate {
    background: #3498DB;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .payment-grid {
        grid-template-columns: 1fr;
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
                    <div class="status-icon">✅</div>
                    <div class="status-text" style="color: #27AE60;">PEMBAYARAN BERHASIL!</div>
                `);
                // Redirect ke halaman utama (index) setelah 2 detik
                setTimeout(function() {
                    window.location.href = '<?= BASE_URL ?>';
                }, 2000);
            }
        });
    }, 2000); // 2000ms = 2 detik
});

function selectBank(el, name) {
    document.querySelectorAll('.bank-item').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Berhasil disalin: ' + text);
    });
}

function simulateScan() {
    $("#scan-status").html(`
        <div class="status-icon">⏳</div>
        <div class="status-text">Memverifikasi pembayaran...</div>
    `);
    
    // Buka halaman bayar di tab baru (simulasi scan QR)
    // Menggunakan Full URL agar aman di live server
    window.open('<?= BASE_URL ?>/pages/shop/bayar.php?order=<?= urlencode($orderNumber) ?>', '_blank');
}
</script>

<?php require_once '../../includes/footer.php'; ?>

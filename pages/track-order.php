<?php
/**
 * Order Tracking Page - Lacak Pesanan
 * Toko Islami - Online Shop
 */

$pageTitle = 'Lacak Pesanan';
require_once '../includes/header.php';

$order = null;
$searchPerformed = false;
$orderItems = [];

if (isset($_GET['order']) && !empty($_GET['order'])) {
    $searchPerformed = true;
    $orderNumber = sanitize($_GET['order']);
    
    // Get order (compatible with older MySQL without JSON_ARRAYAGG)
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    // Get order items separately
    if ($order) {
        $stmt = db()->prepare("SELECT product_name as name, quantity as qty, price FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $orderItems = $stmt->fetchAll();
    }
}

// Status steps
$statusSteps = [
    'pending' => ['icon' => '📋', 'label' => 'Menunggu Konfirmasi', 'color' => '#FFA500'],
    'processing' => ['icon' => '⚙️', 'label' => 'Sedang Diproses', 'color' => '#3498DB'],
    'shipped' => ['icon' => '🚚', 'label' => 'Dalam Pengiriman', 'color' => '#9B59B6'],
    'delivered' => ['icon' => '📦', 'label' => 'Sampai Tujuan', 'color' => '#27AE60'],
    'completed' => ['icon' => '✅', 'label' => 'Selesai', 'color' => '#00695C'],
    'cancelled' => ['icon' => '❌', 'label' => 'Dibatalkan', 'color' => '#E74C3C']
];

// Courier logos
$couriers = [
    'jne' => ['name' => 'JNE', 'logo' => '🟠', 'tracking_url' => 'https://www.jne.co.id/id/tracking/trace'],
    'jnt' => ['name' => 'J&T', 'logo' => '🔴', 'tracking_url' => 'https://www.jet.co.id/track'],
    'sicepat' => ['name' => 'SiCepat', 'logo' => '🟡', 'tracking_url' => 'https://www.sicepat.com/checkAwb'],
    'anteraja' => ['name' => 'AnterAja', 'logo' => '🟢', 'tracking_url' => 'https://anteraja.id/tracking'],
    'pos' => ['name' => 'POS Indonesia', 'logo' => '🔵', 'tracking_url' => 'https://www.posindonesia.co.id/id/tracking'],
    'tiki' => ['name' => 'TIKI', 'logo' => '🟤', 'tracking_url' => 'https://tiki.id/id/tracking'],
    'grab' => ['name' => 'GrabExpress', 'logo' => '💚', 'tracking_url' => ''],
    'gojek' => ['name' => 'GoSend', 'logo' => '💚', 'tracking_url' => ''],
];
?>

<!-- Page Header -->
<section class="tracking-hero">
    <div class="container">
        <div class="tracking-icon">🚚</div>
        <h1>Lacak Pesanan Anda</h1>
        <p>Masukkan nomor pesanan untuk melihat status pengiriman</p>
        
        <!-- Search Form -->
        <form class="tracking-form" method="GET" action="">
            <div class="tracking-input-group">
                <span class="input-icon">📦</span>
                <input type="text" name="order" placeholder="Masukkan Nomor Pesanan (contoh: ORD-xxxxx)" value="<?= isset($_GET['order']) ? htmlspecialchars($_GET['order']) : '' ?>" required>
                <button type="submit" class="btn-track">
                    <span>🔍</span> Lacak
                </button>
            </div>
        </form>
    </div>
</section>

<?php if ($searchPerformed): ?>
<section class="section">
    <div class="container">
        <?php if ($order): ?>
        <!-- Order Found -->
        <div class="tracking-result">
            <!-- Status Timeline -->
            <div class="status-card">
                <div class="status-header">
                    <div>
                        <span class="order-badge">Pesanan #<?= htmlspecialchars($order['order_number']) ?></span>
                        <div class="order-date">📅 <?= formatDate($order['created_at']) ?></div>
                    </div>
                    <div class="status-current" style="background: <?= $statusSteps[$order['status']]['color'] ?>;">
                        <?= $statusSteps[$order['status']]['icon'] ?> <?= $statusSteps[$order['status']]['label'] ?>
                    </div>
                </div>
                
                <!-- Timeline -->
                <div class="status-timeline">
                    <?php 
                    $steps = ['pending', 'processing', 'shipped', 'delivered', 'completed'];
                    $currentIndex = array_search($order['status'], $steps);
                    if ($order['status'] === 'cancelled') $currentIndex = -1;
                    ?>
                    <?php foreach ($steps as $index => $step): ?>
                    <div class="timeline-step <?= $index <= $currentIndex ? 'completed' : '' ?> <?= $index === $currentIndex ? 'current' : '' ?>">
                        <div class="step-icon"><?= $statusSteps[$step]['icon'] ?></div>
                        <div class="step-label"><?= $statusSteps[$step]['label'] ?></div>
                        <?php if ($index < count($steps) - 1): ?>
                        <div class="step-line"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Shipping Info -->
            <?php if ($order['courier_name'] && $order['tracking_number']): ?>
            <div class="shipping-card">
                <h3>📦 Informasi Pengiriman</h3>
                <div class="shipping-details">
                    <div class="courier-info">
                        <span class="courier-logo"><?= $couriers[strtolower($order['courier_name'])]['logo'] ?? '📦' ?></span>
                        <div>
                            <strong><?= htmlspecialchars(strtoupper($order['courier_name'])) ?></strong>
                            <div class="tracking-num"><?= htmlspecialchars($order['tracking_number']) ?></div>
                        </div>
                    </div>
                    <?php 
                    $courierKey = strtolower($order['courier_name']);
                    if (isset($couriers[$courierKey]) && $couriers[$courierKey]['tracking_url']): 
                    ?>
                    <a href="<?= $couriers[$courierKey]['tracking_url'] ?>" target="_blank" class="btn btn-primary">
                        🔗 Lacak di <?= $couriers[$courierKey]['name'] ?>
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php if ($order['shipped_at']): ?>
                <div class="ship-date">🚚 Dikirim pada: <?= formatDate($order['shipped_at']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Order Details -->
            <div class="order-details-card">
                <h3>🛒 Detail Pesanan</h3>
                
                <div class="customer-info">
                    <div><strong>👤 Nama:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                    <div><strong>📞 Telepon:</strong> <?= htmlspecialchars($order['customer_phone']) ?></div>
                    <div><strong>📍 Alamat:</strong> <?= htmlspecialchars($order['customer_address']) ?></div>
                </div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($orderItems as $item): 
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= formatRupiah($item['price']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">Subtotal</td>
                            <td><?= formatRupiah($order['subtotal']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">Ongkos Kirim</td>
                            <td><?= formatRupiah($order['shipping_cost']) ?></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong><?= formatRupiah($order['total_amount']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Order Not Found -->
        <div class="not-found">
            <span style="font-size: 4rem;">🔍</span>
            <h2>Pesanan Tidak Ditemukan</h2>
            <p>Nomor pesanan "<strong><?= htmlspecialchars($_GET['order']) ?></strong>" tidak ditemukan.</p>
            <p style="color: #666;">Pastikan nomor pesanan yang dimasukkan sudah benar.</p>
            <a href="track-order.php" class="btn btn-primary" style="margin-top: 20px;">Coba Lagi</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<!-- Info Section -->
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div class="section-title">
            <h2>📱 Cara Melacak Pesanan</h2>
        </div>
        <div class="how-to-grid">
            <div class="how-to-item">
                <span class="how-icon">1️⃣</span>
                <h4>Dapatkan Nomor Pesanan</h4>
                <p>Cek email konfirmasi atau WhatsApp untuk nomor pesanan Anda</p>
            </div>
            <div class="how-to-item">
                <span class="how-icon">2️⃣</span>
                <h4>Masukkan Nomor Pesanan</h4>
                <p>Ketik nomor pesanan pada form di atas</p>
            </div>
            <div class="how-to-item">
                <span class="how-icon">3️⃣</span>
                <h4>Lihat Status Pesanan</h4>
                <p>Pantau progres pengiriman pesanan Anda</p>
            </div>
        </div>
        
        <div class="courier-list">
            <h3 style="text-align: center; margin-bottom: 20px;">🚚 Kurir Partner Kami</h3>
            <div class="courier-logos">
                <?php foreach ($couriers as $key => $courier): ?>
                <div class="courier-badge">
                    <span><?= $courier['logo'] ?></span>
                    <span><?= $courier['name'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.tracking-hero {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    color: white;
    padding: 60px 0;
    text-align: center;
}

.tracking-icon {
    font-size: 4rem;
    margin-bottom: 15px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.tracking-hero h1 {
    color: white;
    margin-bottom: 10px;
}

.tracking-hero p {
    color: rgba(255,255,255,0.8);
    margin-bottom: 30px;
}

.tracking-form {
    max-width: 600px;
    margin: 0 auto;
}

.tracking-input-group {
    display: flex;
    background: white;
    border-radius: 50px;
    padding: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.input-icon {
    padding: 12px 15px;
    font-size: 1.2rem;
}

.tracking-input-group input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1rem;
    padding: 12px 0;
}

.btn-track {
    background: linear-gradient(135deg, var(--gold), #D4A84B);
    color: #333;
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.3s;
}

.btn-track:hover {
    transform: scale(1.05);
}

/* Status Card */
.tracking-result {
    max-width: 800px;
    margin: 0 auto;
}

.status-card, .shipping-card, .order-details-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.order-badge {
    background: var(--primary);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
}

.order-date {
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
}

.status-current {
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
}

/* Timeline */
.status-timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
}

.step-icon {
    width: 50px;
    height: 50px;
    background: #eee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
}

.timeline-step.completed .step-icon {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}

.timeline-step.current .step-icon {
    box-shadow: 0 0 0 5px rgba(0, 105, 92, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 5px rgba(0, 105, 92, 0.3); }
    50% { box-shadow: 0 0 0 10px rgba(0, 105, 92, 0.1); }
}

.step-label {
    font-size: 0.75rem;
    text-align: center;
    color: #666;
}

.timeline-step.completed .step-label {
    color: var(--primary);
    font-weight: 600;
}

.step-line {
    position: absolute;
    top: 25px;
    left: 50%;
    width: 100%;
    height: 3px;
    background: #eee;
    z-index: 0;
}

.timeline-step.completed .step-line {
    background: var(--primary);
}

/* Shipping Card */
.shipping-card h3, .order-details-card h3 {
    color: var(--primary-dark);
    margin-bottom: 20px;
}

.shipping-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.courier-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.courier-logo {
    font-size: 2.5rem;
}

.tracking-num {
    color: var(--primary);
    font-family: monospace;
    font-size: 1.1rem;
}

.ship-date {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    color: #666;
}

/* Items Table */
.customer-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: grid;
    gap: 8px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th, .items-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.items-table th {
    background: #f8f9fa;
    color: var(--primary-dark);
}

.items-table tfoot td {
    font-size: 0.9rem;
}

.items-table .total-row td {
    border-top: 2px solid var(--primary);
    font-size: 1.1rem;
    color: var(--primary-dark);
}

/* Not Found */
.not-found {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.not-found h2 {
    color: #E74C3C;
    margin: 15px 0;
}

/* How To */
.how-to-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

.how-to-item {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.how-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 15px;
}

.how-to-item h4 {
    color: var(--primary-dark);
    margin-bottom: 10px;
}

.how-to-item p {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

/* Courier List */
.courier-list {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.courier-logos {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 15px;
}

.courier-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .how-to-grid {
        grid-template-columns: 1fr;
    }
    
    .status-timeline {
        flex-direction: column;
        gap: 20px;
    }
    
    .timeline-step {
        flex-direction: row;
        gap: 15px;
    }
    
    .step-line {
        display: none;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>

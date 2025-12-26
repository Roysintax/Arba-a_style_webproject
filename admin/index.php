<?php
/**
 * Admin Dashboard
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Dashboard';
require_once 'includes/admin-header.php';

// Get statistics
$pdo = db();

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Get recent orders
$recentOrders = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get top products
$topProducts = $pdo->query("
    SELECT p.name, p.image, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY oi.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
")->fetchAll();
?>

<!-- Statistics Cards -->
<div class="stat-cards">
    <div class="stat-card">
        <div class="icon">📦</div>
        <h3><?= number_format($totalProducts) ?></h3>
        <p>Total Produk</p>
    </div>
    <div class="stat-card">
        <div class="icon">🧾</div>
        <h3><?= number_format($totalOrders) ?></h3>
        <p>Total Pesanan</p>
    </div>
    <div class="stat-card">
        <div class="icon">📝</div>
        <h3><?= number_format($totalArticles) ?></h3>
        <p>Total Artikel</p>
    </div>
    <div class="stat-card">
        <div class="icon">💰</div>
        <h3><?= formatRupiah($totalRevenue) ?></h3>
        <p>Pendapatan</p>
    </div>
</div>

<?php if ($pendingOrders > 0): ?>
<div class="alert alert-success" style="display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 1.5rem;">📬</span>
    <span>Ada <strong><?= $pendingOrders ?></strong> pesanan baru menunggu diproses. <a href="orders.php">Lihat Pesanan →</a></span>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Recent Orders -->
    <div class="panel">
        <div class="panel-header">
            <h2>🧾 Pesanan Terbaru</h2>
            <a href="orders.php" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px;">Belum ada pesanan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><strong><?= $order['order_number'] ?></strong></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= formatRupiah($order['total_amount']) ?></td>
                        <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                        <td><?= formatDate($order['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div>
        <div class="panel">
            <div class="panel-header">
                <h2>⚡ Aksi Cepat</h2>
            </div>
            <div class="panel-body">
                <a href="add-product.php" class="btn btn-primary btn-block" style="margin-bottom: 10px; justify-content: center;">
                    ➕ Tambah Produk
                </a>
                <a href="add-article.php" class="btn btn-success btn-block" style="margin-bottom: 10px; justify-content: center;">
                    ✏️ Tulis Artikel
                </a>
                <a href="categories.php" class="btn btn-warning btn-block" style="margin-bottom: 10px; justify-content: center;">
                    📂 Kelola Kategori
                </a>
                <a href="orders.php" class="btn btn-secondary btn-block" style="justify-content: center;">
                    📋 Lihat Pesanan
                </a>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <h2>🏆 Produk Terlaris</h2>
            </div>
            <div class="panel-body">
                <?php if (empty($topProducts)): ?>
                <p style="text-align: center; color: #666;">Belum ada data</p>
                <?php else: ?>
                <?php foreach ($topProducts as $product): ?>
                <div style="display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eee;">
                    <img src="<?= getImageUrl($product['image']) ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500;"><?= htmlspecialchars($product['name']) ?></div>
                        <small style="color: #666;">Terjual: <?= $product['total_sold'] ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Circular Statistics Charts -->
<div class="panel" style="margin-top: 30px;">
    <div class="panel-header">
        <h2>📊 Statistik Visual</h2>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px;">
            <!-- Total Produk -->
            <div style="text-align: center;">
                <div class="donut-chart" style="--percentage: <?= min($totalProducts, 100) ?>; --color: #00695C;">
                    <div class="donut-center">
                        <span class="donut-number"><?= number_format($totalProducts) ?></span>
                    </div>
                </div>
                <p style="margin-top: 15px; font-weight: 600; color: #00695C;">📦 Total Produk</p>
            </div>
            
            <!-- Total Pesanan -->
            <div style="text-align: center;">
                <div class="donut-chart" style="--percentage: <?= min($totalOrders, 100) ?>; --color: #E74C3C;">
                    <div class="donut-center">
                        <span class="donut-number"><?= number_format($totalOrders) ?></span>
                    </div>
                </div>
                <p style="margin-top: 15px; font-weight: 600; color: #E74C3C;">🧾 Total Pesanan</p>
            </div>
            
            <!-- Total Artikel -->
            <div style="text-align: center;">
                <div class="donut-chart" style="--percentage: <?= min($totalArticles, 100) ?>; --color: #3498DB;">
                    <div class="donut-center">
                        <span class="donut-number"><?= number_format($totalArticles) ?></span>
                    </div>
                </div>
                <p style="margin-top: 15px; font-weight: 600; color: #3498DB;">📝 Total Artikel</p>
            </div>
            
            <!-- Pendapatan -->
            <div style="text-align: center;">
                <div class="donut-chart" style="--percentage: <?= min(($totalRevenue / 10000000) * 100, 100) ?>; --color: #D4AF37;">
                    <div class="donut-center">
                        <span class="donut-number" style="font-size: 0.9rem;"><?= formatRupiah($totalRevenue) ?></span>
                    </div>
                </div>
                <p style="margin-top: 15px; font-weight: 600; color: #D4AF37;">💰 Pendapatan</p>
            </div>
        </div>
    </div>
</div>

<style>
.donut-chart {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: conic-gradient(
        var(--color) calc(var(--percentage) * 3.6deg),
        #e8e8e8 calc(var(--percentage) * 3.6deg)
    );
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    animation: rotateIn 1s ease-out;
}

@keyframes rotateIn {
    from {
        transform: rotate(-90deg);
        opacity: 0;
    }
    to {
        transform: rotate(0);
        opacity: 1;
    }
}

.donut-center {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);
}

.donut-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
}

.donut-chart:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}
</style>

<?php require_once 'includes/admin-footer.php'; ?>

<?php
/**
 * Admin Orders
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete order
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        $deleteId = (int)$_GET['delete'];
        // Delete order items first
        db()->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$deleteId]);
        // Delete order
        db()->prepare("DELETE FROM orders WHERE id = ?")->execute([$deleteId]);
        setFlash('success', 'Pesanan berhasil dihapus');
    }
    header('Location: orders.php');
    exit;
}

// Handle status update BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    if (isAdmin()) {
        $orderId = (int)$_POST['order_id'];
        $status = sanitize($_POST['status']);
        
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        if (in_array($status, $validStatuses)) {
            db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $orderId]);
            setFlash('success', 'Status pesanan berhasil diperbarui');
        }
    }
    header('Location: orders.php');
    exit;
}

$pageTitle = 'Kelola Pesanan';

// Handle CSV Download
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    if (isAdmin()) {
        $month = isset($_GET['month']) ? sanitize($_GET['month']) : '';
        
        // Build query
        $csvSql = "SELECT order_number, customer_name, customer_phone, customer_email, customer_address, total_amount, payment_method, status, created_at FROM orders";
        $params = [];
        
        if ($month) {
            $csvSql .= " WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
            $params[] = $month;
        }
        $csvSql .= " ORDER BY created_at DESC";
        
        $stmt = db()->prepare($csvSql);
        $stmt->execute($params);
        $csvOrders = $stmt->fetchAll();
        
        // Calculate total
        $totalRevenue = array_sum(array_column($csvOrders, 'total_amount'));
        
        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rekap_pesanan_' . ($month ?: 'semua') . '.csv');
        
        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['No Pesanan', 'Nama', 'Telepon', 'Email', 'Alamat', 'Total', 'Pembayaran', 'Status', 'Tanggal']);
        
        // Data
        foreach ($csvOrders as $o) {
            fputcsv($output, [
                $o['order_number'],
                $o['customer_name'],
                $o['customer_phone'],
                $o['customer_email'],
                $o['customer_address'],
                $o['total_amount'],
                $o['payment_method'],
                $o['status'],
                $o['created_at']
            ]);
        }
        
        // Empty row and Total
        fputcsv($output, []);
        fputcsv($output, ['', '', '', '', 'TOTAL PENDAPATAN:', $totalRevenue, '', '', '']);
        
        fclose($output);
        exit;
    }
}

require_once 'includes/admin-header.php';

// Filter by status
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$monthFilter = isset($_GET['month']) ? sanitize($_GET['month']) : '';

// Build query with filters
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($monthFilter) {
    $sql .= " AND DATE_FORMAT(created_at, '%Y-%m') = ?";
    $params[] = $monthFilter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Calculate total revenue for current filter
$totalRevenue = array_sum(array_column($orders, 'total_amount'));

// Get counts by status
$statusCounts = db()->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get available months
$availableMonths = db()->query("
    SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month 
    FROM orders 
    ORDER BY month DESC
")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Status Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="orders.php<?= $monthFilter ? '?month='.$monthFilter : '' ?>" class="btn <?= !$statusFilter ? 'btn-primary' : 'btn-secondary' ?>">
        Semua (<?= array_sum($statusCounts) ?>)
    </a>
    <a href="orders.php?status=pending<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">
        ⏳ Pending (<?= $statusCounts['pending'] ?? 0 ?>)
    </a>
    <a href="orders.php?status=processing<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn <?= $statusFilter === 'processing' ? 'btn-primary' : 'btn-secondary' ?>">
        🔄 Processing (<?= $statusCounts['processing'] ?? 0 ?>)
    </a>
    <a href="orders.php?status=shipped<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn <?= $statusFilter === 'shipped' ? 'btn-primary' : 'btn-secondary' ?>">
        🚚 Shipped (<?= $statusCounts['shipped'] ?? 0 ?>)
    </a>
    <a href="orders.php?status=completed<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn <?= $statusFilter === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">
        ✅ Completed (<?= $statusCounts['completed'] ?? 0 ?>)
    </a>
    <a href="orders.php?status=cancelled<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn <?= $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">
        ❌ Cancelled (<?= $statusCounts['cancelled'] ?? 0 ?>)
    </a>
</div>

<!-- Monthly Filter & Export Buttons -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <label style="font-weight: 600;">📅 Rekap Bulanan:</label>
        <select onchange="filterByMonth(this.value)" class="form-control" style="width: auto; padding: 8px 15px;">
            <option value="">Semua Waktu</option>
            <?php foreach ($availableMonths as $m): ?>
            <option value="<?= $m ?>" <?= $monthFilter === $m ? 'selected' : '' ?>>
                <?= date('F Y', strtotime($m . '-01')) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div style="display: flex; align-items: center; gap: 15px;">
        <!-- Total Revenue Card -->
        <div style="background: linear-gradient(135deg, #00695C, #004D40); color: white; padding: 12px 25px; border-radius: 10px;">
            <div style="font-size: 0.8rem; opacity: 0.8;">💰 Total Pendapatan</div>
            <div style="font-size: 1.3rem; font-weight: 700;"><?= formatRupiah($totalRevenue) ?></div>
        </div>
        
        <!-- Download CSV Button -->
        <a href="orders.php?download=csv<?= $monthFilter ? '&month='.$monthFilter : '' ?>" class="btn" style="background: #27AE60; color: white; display: flex; align-items: center; gap: 8px;">
            📥 Download CSV
        </a>
    </div>
</div>

<script>
function filterByMonth(month) {
    let url = 'orders.php';
    let params = [];
    <?php if ($statusFilter): ?>
    params.push('status=<?= $statusFilter ?>');
    <?php endif; ?>
    if (month) params.push('month=' + month);
    if (params.length > 0) url += '?' + params.join('&');
    window.location.href = url;
}
</script>

<div class="panel">
    <div class="panel-header">
        <h2>🧾 Daftar Pesanan</h2>
    </div>
    <div class="panel-body" style="padding: 0; overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Telepon</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🧾</div>
                        <p>Belum ada pesanan</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?= $order['order_number'] ?></strong></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        <small style="color: #666;"><?= htmlspecialchars($order['customer_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                    <td><?= formatRupiah($order['total_amount']) ?></td>
                    <td><?= ucfirst($order['payment_method']) ?></td>
                    <td><span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><?= formatDate($order['created_at']) ?></td>
                    <td>
                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">👁️ Detail</a>
                        
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" class="form-control" style="width: auto; display: inline; padding: 5px; font-size: 0.8rem;" onchange="this.form.submit()">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                        <a href="?delete=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesanan ini? Data order items juga akan terhapus.')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

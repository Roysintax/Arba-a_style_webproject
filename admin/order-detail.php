<?php
/**
 * Admin Order Detail
 * Toko Islami - Admin Panel
 */

require_once 'includes/admin-header.php';

// Get order
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Pesanan tidak ditemukan');
    header('Location: orders.php');
    exit;
}

$pageTitle = 'Detail Pesanan #' . $order['order_number'];

// Get order items
$items = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$orderItems = $items->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = sanitize($_POST['status']);
    $validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    
    if (in_array($status, $validStatuses)) {
        db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
        setFlash('success', 'Status pesanan berhasil diperbarui');
        header('Location: order-detail.php?id=' . $id);
        exit;
    }
}
?>

<div style="margin-bottom: 20px;">
    <a href="orders.php" class="btn btn-secondary">← Kembali ke Daftar Pesanan</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Order Details -->
    <div>
        <div class="panel">
            <div class="panel-header">
                <h2>📦 Item Pesanan</h2>
            </div>
            <div class="panel-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= formatRupiah($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatRupiah($item['subtotal']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8f9fa;">
                            <td colspan="3" style="text-align: right;"><strong>Subtotal</strong></td>
                            <td><strong><?= formatRupiah($order['subtotal']) ?></strong></td>
                        </tr>
                        <tr style="background: #f8f9fa;">
                            <td colspan="3" style="text-align: right;">Ongkos Kirim</td>
                            <td><?= formatRupiah($order['shipping_cost']) ?></td>
                        </tr>
                        <tr style="background: var(--primary); color: white;">
                            <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                            <td><strong><?= formatRupiah($order['total_amount']) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <h2>👤 Informasi Pelanggan</h2>
            </div>
            <div class="panel-body">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 8px 0; width: 150px;"><strong>Nama</strong></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Email</strong></td>
                        <td><?= htmlspecialchars($order['customer_email']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Telepon</strong></td>
                        <td><?= htmlspecialchars($order['customer_phone']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top;"><strong>Alamat</strong></td>
                        <td><?= nl2br(htmlspecialchars($order['customer_address'])) ?></td>
                    </tr>
                    <?php if ($order['notes']): ?>
                    <tr>
                        <td style="padding: 8px 0; vertical-align: top;"><strong>Catatan</strong></td>
                        <td><?= nl2br(htmlspecialchars($order['notes'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div>
        <div class="panel">
            <div class="panel-header">
                <h2>📋 Ringkasan Pesanan</h2>
            </div>
            <div class="panel-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 0.9rem; color: #666;">No. Pesanan</div>
                    <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-dark);"><?= $order['order_number'] ?></div>
                </div>
                
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 8px 0;"><strong>Tanggal</strong></td>
                        <td style="text-align: right;"><?= formatDate($order['created_at']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Pembayaran</strong></td>
                        <td style="text-align: right;"><?= ucfirst($order['payment_method']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0;"><strong>Status</strong></td>
                        <td style="text-align: right;">
                            <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </td>
                    </tr>
                </table>
                
                <hr style="margin: 20px 0;">
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Update Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>🔄 Processing</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>🚚 Shipped</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>✅ Completed</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">💾 Update Status</button>
                </form>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <h2>📞 Aksi</h2>
            </div>
            <div class="panel-body">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['customer_phone']) ?>?text=<?= urlencode('Halo ' . $order['customer_name'] . ', pesanan Anda #' . $order['order_number'] . ' sedang diproses.') ?>" target="_blank" class="btn btn-success btn-block" style="margin-bottom: 10px;">
                    📱 Hubungi via WhatsApp
                </a>
                <a href="mailto:<?= $order['customer_email'] ?>?subject=Pesanan%20<?= $order['order_number'] ?>" class="btn btn-secondary btn-block">
                    ✉️ Kirim Email
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

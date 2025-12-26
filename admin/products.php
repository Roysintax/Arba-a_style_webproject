<?php
/**
 * Admin Products List
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Kelola Produk';
require_once 'includes/admin-header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        if ($product['image']) {
            deleteImage($product['image']);
        }
        db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        setFlash('success', 'Produk berhasil dihapus');
    }
    header('Location: products.php');
    exit;
}

// Get products
$products = db()->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<div class="panel">
    <div class="panel-header">
        <h2>📦 Daftar Produk (<?= count($products) ?>)</h2>
        <a href="add-product.php" class="btn btn-primary">➕ Tambah Produk</a>
    </div>
    <div class="panel-body" style="padding: 0; overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th width="60">Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
                        <p>Belum ada produk. <a href="add-product.php">Tambah produk pertama</a></p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?= getImageUrl($product['image']) ?>" alt="">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-completed">Unggulan</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                    <td><?= formatRupiah($product['price']) ?></td>
                    <td>
                        <?php if ($product['stock'] > 0): ?>
                        <span style="color: green;"><?= $product['stock'] ?></span>
                        <?php else: ?>
                        <span style="color: red;">Habis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['is_active']): ?>
                        <span class="badge badge-completed">Aktif</span>
                        <?php else: ?>
                        <span class="badge badge-cancelled">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a href="products.php?delete=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus produk ini?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

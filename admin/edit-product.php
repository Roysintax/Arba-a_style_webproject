<?php
/**
 * Admin Edit Product
 * Toko Islami - Admin Panel
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check admin login
requireAdmin();

$pageTitle = 'Edit Produk';

// Get product
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Produk tidak ditemukan');
    header('Location: products.php');
    exit;
}

// Get categories
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Nama produk harus diisi';
    if ($price <= 0) $errors[] = 'Harga harus lebih dari 0';
    
    // Handle image upload
    $image = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'products');
        if ($upload['success']) {
            // Delete old image
            if ($product['image']) {
                deleteImage($product['image']);
            }
            $image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($name);
        
        // Check slug uniqueness
        $stmt = db()->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        $stmt = db()->prepare("
            UPDATE products SET 
                category_id = ?, name = ?, slug = ?, description = ?, 
                price = ?, stock = ?, image = ?, is_featured = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $category_id ?: null,
            $name,
            $slug,
            $description,
            $price,
            $stock,
            $image,
            $is_featured,
            $is_active,
            $id
        ]);
        
        setFlash('success', 'Produk berhasil diperbarui');
        header('Location: products.php');
        exit;
    }
} else {
    $name = $product['name'];
    $category_id = $product['category_id'];
    $description = $product['description'];
    $price = $product['price'];
    $stock = $product['stock'];
    $is_featured = $product['is_featured'];
    $is_active = $product['is_active'];
}

require_once 'includes/admin-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>✏️ Edit Produk</h2>
        <a href="products.php" class="btn btn-secondary">← Kembali</a>
    </div>
    <div class="panel-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div>
                    <div class="form-group">
                        <label>Nama Produk *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($description) ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Harga (Rp) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $price ?>" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" class="form-control" value="<?= $stock ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label>Gambar Produk</label>
                        <?php if ($product['image']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getImageUrl($product['image']) ?>" alt="" class="image-preview" style="display: block;">
                            <small>Gambar saat ini</small>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" src="" alt="" class="image-preview" style="display: none;">
                        <small style="color: #666;">Kosongkan jika tidak ingin mengubah gambar</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" <?= $is_featured ? 'checked' : '' ?>>
                            <span>⭐ Produk Unggulan</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= $is_active ? 'checked' : '' ?>>
                            <span>✓ Produk Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="products.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

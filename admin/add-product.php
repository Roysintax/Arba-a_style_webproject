<?php
/**
 * Admin Add Product
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Tambah Produk';
require_once 'includes/admin-header.php';

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
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'products');
        if ($upload['success']) {
            $image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($name);
        
        // Check slug uniqueness
        $stmt = db()->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        $stmt = db()->prepare("
            INSERT INTO products (category_id, name, slug, description, price, stock, image, is_featured, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $is_active
        ]);
        
        setFlash('success', 'Produk berhasil ditambahkan');
        header('Location: products.php');
        exit;
    }
}
?>

<div class="panel">
    <div class="panel-header">
        <h2>➕ Tambah Produk Baru</h2>
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
                        <input type="text" name="name" class="form-control" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= isset($category_id) && $category_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="5"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Harga (Rp) *</label>
                            <input type="number" name="price" class="form-control" value="<?= isset($price) ? $price : '' ?>" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" class="form-control" value="<?= isset($stock) ? $stock : 0 ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label>Gambar Produk</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" src="" alt="" class="image-preview" style="display: none;">
                        <small style="color: #666;">Format: JPG, PNG, GIF. Max: 5MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" <?= isset($is_featured) && $is_featured ? 'checked' : '' ?>>
                            <span>⭐ Produk Unggulan</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= !isset($is_active) || $is_active ? 'checked' : '' ?>>
                            <span>✓ Produk Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">💾 Simpan Produk</button>
                <a href="products.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

<?php
/**
 * Admin Categories
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Kelola Kategori';
require_once 'includes/admin-header.php';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (!empty($name)) {
        $slug = createSlug($name);
        
        if ($id > 0) {
            // Update
            $stmt = db()->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $id]);
            setFlash('success', 'Kategori berhasil diperbarui');
        } else {
            // Insert
            $stmt = db()->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            setFlash('success', 'Kategori berhasil ditambahkan');
        }
    }
    header('Location: categories.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category has products
    $stmt = db()->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        setFlash('danger', 'Kategori tidak dapat dihapus karena masih memiliki produk');
    } else {
        db()->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        setFlash('success', 'Kategori berhasil dihapus');
    }
    header('Location: categories.php');
    exit;
}

// Get category for edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editCategory = $stmt->fetch();
}

// Get all categories with product count
$categories = db()->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
    <!-- Categories List -->
    <div class="panel">
        <div class="panel-header">
            <h2>📂 Daftar Kategori (<?= count($categories) ?>)</h2>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Slug</th>
                        <th>Jumlah Produk</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            <div style="font-size: 3rem; margin-bottom: 10px;">📂</div>
                            <p>Belum ada kategori</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td><code><?= $cat['slug'] ?></code></td>
                        <td><?= $cat['product_count'] ?> produk</td>
                        <td>
                            <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                            <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus kategori ini?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add/Edit Form -->
    <div class="panel">
        <div class="panel-header">
            <h2><?= $editCategory ? '✏️ Edit Kategori' : '➕ Tambah Kategori' ?></h2>
        </div>
        <div class="panel-body">
            <form action="" method="POST">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Kategori *</label>
                    <input type="text" name="name" class="form-control" value="<?= $editCategory ? htmlspecialchars($editCategory['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= $editCategory ? htmlspecialchars($editCategory['description']) : '' ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    💾 <?= $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                </button>
                
                <?php if ($editCategory): ?>
                <a href="categories.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

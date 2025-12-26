<?php
/**
 * Admin Edit Article
 * Toko Islami - Admin Panel
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Check admin login
requireAdmin();

$pageTitle = 'Edit Artikel';

// Get article
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('danger', 'Artikel tidak ditemukan');
    header('Location: articles.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Allow HTML
    $author = sanitize($_POST['author'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($title)) $errors[] = 'Judul artikel harus diisi';
    if (empty($content)) $errors[] = 'Konten artikel harus diisi';
    
    // Handle image upload
    $image = $article['image'];
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'articles');
        if ($upload['success']) {
            // Delete old image
            if ($article['image']) {
                deleteImage($article['image']);
            }
            $image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($title);
        
        // Check slug uniqueness
        $stmt = db()->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        $stmt = db()->prepare("
            UPDATE articles SET 
                title = ?, slug = ?, excerpt = ?, content = ?, 
                image = ?, author = ?, is_published = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title,
            $slug,
            $excerpt,
            $content,
            $image,
            $author ?: 'Admin',
            $is_published,
            $id
        ]);
        
        setFlash('success', 'Artikel berhasil diperbarui');
        header('Location: articles.php');
        exit;
    }
} else {
    $title = $article['title'];
    $excerpt = $article['excerpt'];
    $content = $article['content'];
    $author = $article['author'];
    $is_published = $article['is_published'];
}

require_once 'includes/admin-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>✏️ Edit Artikel</h2>
        <a href="articles.php" class="btn btn-secondary">← Kembali</a>
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
                        <label>Judul Artikel *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ringkasan (Excerpt)</label>
                        <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($excerpt) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Konten Artikel *</label>
                        <!-- Quill Editor Toolbar -->
                        <div id="toolbar-container">
                            <span class="ql-formats">
                                <select class="ql-font"></select>
                                <select class="ql-size"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                                <button class="ql-strike"></button>
                            </span>
                            <span class="ql-formats">
                                <select class="ql-color"></select>
                                <select class="ql-background"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-script" value="sub"></button>
                                <button class="ql-script" value="super"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-header" value="1"></button>
                                <button class="ql-header" value="2"></button>
                                <button class="ql-blockquote"></button>
                                <button class="ql-code-block"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                                <button class="ql-indent" value="-1"></button>
                                <button class="ql-indent" value="+1"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-direction" value="rtl"></button>
                                <select class="ql-align"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link"></button>
                                <button class="ql-image"></button>
                                <button class="ql-video"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean"></button>
                            </span>
                        </div>
                        <!-- Quill Editor Container -->
                        <div id="editor" style="min-height: 300px; background: white;"><?= $content ?></div>
                        <!-- Hidden input to store HTML content -->
                        <input type="hidden" name="content" id="content-input">
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label>Gambar Utama</label>
                        <?php if ($article['image']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getImageUrl($article['image']) ?>" alt="" class="image-preview" style="display: block;">
                            <small>Gambar saat ini</small>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" src="" alt="" class="image-preview" style="display: none;">
                        <small style="color: #666;">Kosongkan jika tidak ingin mengubah gambar</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Penulis</label>
                        <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($author) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_published" value="1" <?= $is_published ? 'checked' : '' ?>>
                            <span>✓ Publikasikan Artikel</span>
                        </label>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <h4 style="margin-bottom: 10px; font-size: 0.9rem;">📊 Statistik</h4>
                        <p style="margin: 5px 0; font-size: 0.9rem;">Views: <?= number_format($article['views']) ?>x</p>
                        <p style="margin: 5px 0; font-size: 0.9rem;">Dibuat: <?= formatDate($article['created_at']) ?></p>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="articles.php" class="btn btn-secondary">Batal</a>
                <a href="<?= BASE_URL ?>/article-detail.php?slug=<?= $article['slug'] ?>" class="btn btn-secondary" target="_blank">👁️ Lihat Artikel</a>
            </div>
        </form>
    </div>
</div>

<!-- Quill Editor CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<style>
#toolbar-container {
    background: #f8f9fa;
    border: 1px solid #ccc;
    border-bottom: none;
    border-radius: 5px 5px 0 0;
}

#editor {
    border: 1px solid #ccc;
    border-radius: 0 0 5px 5px;
    font-size: 1rem;
}

.ql-editor {
    min-height: 300px;
    font-family: inherit;
    font-size: 1rem;
    line-height: 1.7;
}

.ql-editor p {
    margin-bottom: 1em;
}

.ql-toolbar.ql-snow {
    border: none;
    padding: 10px;
}
</style>

<script>
// Initialize Quill editor
const quill = new Quill('#editor', {
    modules: {
        syntax: true,
        toolbar: '#toolbar-container',
    },
    placeholder: 'Tulis konten artikel di sini...',
    theme: 'snow',
});

// Copy content to hidden input before form submit
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>

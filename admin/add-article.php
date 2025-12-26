<?php
/**
 * Admin Add Article
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Tulis Artikel';
require_once 'includes/admin-header.php';

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
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'articles');
        if ($upload['success']) {
            $image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($title);
        
        // Check slug uniqueness
        $stmt = db()->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        $stmt = db()->prepare("
            INSERT INTO articles (title, slug, excerpt, content, image, author, is_published) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $slug,
            $excerpt,
            $content,
            $image,
            $author ?: 'Admin',
            $is_published
        ]);
        
        setFlash('success', 'Artikel berhasil dipublikasikan');
        header('Location: articles.php');
        exit;
    }
}
?>

<div class="panel">
    <div class="panel-header">
        <h2>✏️ Tulis Artikel Baru</h2>
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
                        <input type="text" name="title" class="form-control" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ringkasan (Excerpt)</label>
                        <textarea name="excerpt" class="form-control" rows="3" placeholder="Ringkasan singkat artikel..."><?= isset($excerpt) ? htmlspecialchars($excerpt) : '' ?></textarea>
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
                        <div id="editor" style="min-height: 300px; background: white;"><?= isset($content) ? $content : '' ?></div>
                        <!-- Hidden input to store HTML content -->
                        <input type="hidden" name="content" id="content-input">
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label>Gambar Utama</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" src="" alt="" class="image-preview" style="display: none;">
                        <small style="color: #666;">Format: JPG, PNG, GIF. Max: 5MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Penulis</label>
                        <input type="text" name="author" class="form-control" value="<?= isset($author) ? htmlspecialchars($author) : 'Admin' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_published" value="1" <?= !isset($is_published) || $is_published ? 'checked' : '' ?>>
                            <span>✓ Publikasikan Artikel</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">📤 Publikasikan</button>
                <a href="articles.php" class="btn btn-secondary">Batal</a>
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

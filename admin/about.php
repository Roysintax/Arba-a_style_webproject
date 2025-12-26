<?php
/**
 * Admin - Edit About Page
 * Toko Islami - Admin Panel
 */

require_once 'includes/admin-header.php';

// Get current about content
$about = getAboutPage();

$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subtitle = sanitize($_POST['subtitle'] ?? '');
    $content = $_POST['content'] ?? '';
    $vision = sanitize($_POST['vision'] ?? '');
    $mission = sanitize($_POST['mission'] ?? '');
    
    // Validation
    if (empty($title)) $errors[] = 'Judul harus diisi';
    if (empty($content)) $errors[] = 'Konten harus diisi';
    
    // Handle photo upload
    $photo = $about['photo'] ?? '';
    if (!empty($_FILES['photo']['name'])) {
        $upload = uploadImage($_FILES['photo'], 'about');
        if ($upload['success']) {
            // Delete old photo
            if (!empty($about['photo'])) {
                deleteImage($about['photo']);
            }
            $photo = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        if ($about) {
            // Update existing
            $stmt = db()->prepare("
                UPDATE about_page 
                SET title = ?, subtitle = ?, content = ?, photo = ?, vision = ?, mission = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $subtitle, $content, $photo, $vision, $mission, $about['id']]);
        } else {
            // Insert new
            $stmt = db()->prepare("
                INSERT INTO about_page (title, subtitle, content, photo, vision, mission) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $subtitle, $content, $photo, $vision, $mission]);
        }
        
        $success = 'Halaman Tentang Kami berhasil diperbarui';
        
        // Refresh data
        $about = getAboutPage();
    }
}
?>

<div class="admin-content">
    <div class="content-header">
        <h1>📖 Edit Halaman Tentang Kami</h1>
        <p>Kelola konten halaman Tentang Kami</p>
    </div>
    
    <?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
    <div class="alert danger">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
            <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Left Column - Content -->
                <div>
                    <div class="form-group">
                        <label>Judul *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?= htmlspecialchars($about['title'] ?? 'Tentang Toko Islami') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Subtitle</label>
                        <input type="text" name="subtitle" class="form-control" 
                               value="<?= htmlspecialchars($about['subtitle'] ?? '') ?>" 
                               placeholder="Tagline atau subjudul">
                    </div>
                    
                    <div class="form-group">
                        <label>Konten *</label>
                        <div id="toolbar-content" class="ql-toolbar-custom">
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-header" value="1"></button>
                                <button class="ql-header" value="2"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link"></button>
                                <button class="ql-clean"></button>
                            </span>
                        </div>
                        <div id="editor-content" class="quill-editor"><?= $about['content'] ?? '' ?></div>
                        <input type="hidden" name="content" id="input-content">
                    </div>
                    
                    <div class="form-group">
                        <label>Visi</label>
                        <div id="toolbar-vision" class="ql-toolbar-custom">
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean"></button>
                            </span>
                        </div>
                        <div id="editor-vision" class="quill-editor-small"><?= $about['vision'] ?? '' ?></div>
                        <input type="hidden" name="vision" id="input-vision">
                    </div>
                    
                    <div class="form-group">
                        <label>Misi</label>
                        <div id="toolbar-mission" class="ql-toolbar-custom">
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean"></button>
                            </span>
                        </div>
                        <div id="editor-mission" class="quill-editor-small"><?= $about['mission'] ?? '' ?></div>
                        <input type="hidden" name="mission" id="input-mission">
                    </div>
                </div>
                
                <!-- Right Column - Photo -->
                <div>
                    <div class="form-group">
                        <label>Foto Profil / Logo</label>
                        <div style="border: 2px dashed #ddd; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 15px;">
                            <?php if (!empty($about['photo'])): ?>
                            <img src="<?= UPLOAD_URL . $about['photo'] ?>" alt="Current Photo" 
                                 style="max-width: 100%; max-height: 250px; border-radius: 10px; margin-bottom: 15px;">
                            <?php else: ?>
                            <div style="padding: 40px; color: #999;">
                                <span style="font-size: 4rem;">📷</span>
                                <p>Belum ada foto</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <small style="color: #666;">Format: JPG, PNG, GIF. Maks 5MB</small>
                    </div>
                    
                    <div style="background: #f8f9fc; padding: 20px; border-radius: 10px; margin-top: 20px;">
                        <h4 style="margin-top: 0;">💡 Tips</h4>
                        <ul style="color: #666; font-size: 0.9rem; padding-left: 20px; margin: 0;">
                            <li>Gunakan foto berkualitas tinggi</li>
                            <li>Ukuran ideal: 400x400 pixel</li>
                            <li>Konten bisa menggunakan HTML</li>
                            <li>Pastikan visi dan misi singkat tapi bermakna</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="<?= BASE_URL ?>/pages/about.php" target="_blank" class="btn btn-secondary" style="margin-left: 10px;">
                    👁️ Lihat Halaman
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quill Editor CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<style>
.ql-toolbar-custom {
    background: #f8f9fa;
    border: 1px solid #ccc;
    border-bottom: none;
    border-radius: 5px 5px 0 0;
}

.quill-editor, .quill-editor-small {
    border: 1px solid #ccc;
    border-radius: 0 0 5px 5px;
    background: white;
}

.quill-editor .ql-editor {
    min-height: 200px;
    font-size: 1rem;
    line-height: 1.7;
}

.quill-editor-small .ql-editor {
    min-height: 100px;
    font-size: 1rem;
    line-height: 1.6;
}

.ql-toolbar.ql-snow {
    border: none;
    padding: 8px;
}
</style>

<script>
// Initialize Quill editors
const quillContent = new Quill('#editor-content', {
    modules: { toolbar: '#toolbar-content' },
    placeholder: 'Tulis konten tentang kami...',
    theme: 'snow',
});

const quillVision = new Quill('#editor-vision', {
    modules: { toolbar: '#toolbar-vision' },
    placeholder: 'Tulis visi perusahaan...',
    theme: 'snow',
});

const quillMission = new Quill('#editor-mission', {
    modules: { toolbar: '#toolbar-mission' },
    placeholder: 'Tulis misi perusahaan...',
    theme: 'snow',
});

// Copy content to hidden inputs before form submit
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('input-content').value = quillContent.root.innerHTML;
    document.getElementById('input-vision').value = quillVision.root.innerHTML;
    document.getElementById('input-mission').value = quillMission.root.innerHTML;
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>

<?php
/**
 * Admin - Kelola Kegiatan Sosial
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM social_activities WHERE id = ?")->execute([$_GET['delete']]);
        setFlash('success', 'Kegiatan berhasil dihapus');
    }
    header('Location: social-activities.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $eventDate = $_POST['event_date'] ?? null;
    $location = sanitize($_POST['location'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image uploads
    $images = [];
    
    // Keep selected existing images (ones not deleted by user)
    if (!empty($_POST['keep_images'])) {
        $images = $_POST['keep_images'];
    }
    
    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            if ($_FILES['images']['error'][$key] === 0 && !empty($_FILES['images']['name'][$key])) {
                $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $filename = 'activity_' . time() . '_' . $key . '.' . $ext;
                $uploadPath = __DIR__ . '/../uploads/' . $filename;
                if (move_uploaded_file($tmp, $uploadPath)) {
                    $images[] = $filename;
                }
            }
        }
    }
    
    $imagesJson = json_encode($images);
    
    if ($id) {
        // Update
        $stmt = db()->prepare("UPDATE social_activities SET title=?, slug=?, description=?, content=?, images=?, event_date=?, location=?, is_active=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $content, $imagesJson, $eventDate ?: null, $location, $isActive, $id]);
        setFlash('success', 'Kegiatan berhasil diperbarui');
    } else {
        // Insert
        $stmt = db()->prepare("INSERT INTO social_activities (title, slug, description, content, images, event_date, location, is_active) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $description, $content, $imagesJson, $eventDate ?: null, $location, $isActive]);
        setFlash('success', 'Kegiatan berhasil ditambahkan');
    }
    header('Location: social-activities.php');
    exit;
}

$pageTitle = 'Kegiatan Sosial';
require_once 'includes/admin-header.php';

// Get all activities
$activities = db()->query("SELECT * FROM social_activities ORDER BY event_date DESC, created_at DESC")->fetchAll();

// Edit mode
$editActivity = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM social_activities WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editActivity = $stmt->fetch();
}
?>

<div class="content-header">
    <h1>🤝 Kegiatan Sosial</h1>
    <p>Kelola kegiatan sosial dan keagamaan</p>
</div>

<!-- Add/Edit Form -->
<div class="panel">
    <div class="panel-header">
        <h2><?= $editActivity ? '✏️ Edit Kegiatan' : '➕ Tambah Kegiatan' ?></h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <?php if ($editActivity): ?>
            <input type="hidden" name="id" value="<?= $editActivity['id'] ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Judul Kegiatan *</label>
                    <input type="text" name="title" class="form-control" required value="<?= $editActivity ? htmlspecialchars($editActivity['title']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Kegiatan</label>
                    <input type="date" name="event_date" class="form-control" value="<?= $editActivity ? $editActivity['event_date'] : '' ?>">
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="location" class="form-control" value="<?= $editActivity ? htmlspecialchars($editActivity['location']) : '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <div id="toolbar-description" class="ql-toolbar-custom">
                    <span class="ql-formats">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
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
                <div id="editor-description" class="quill-editor-small"><?= $editActivity ? $editActivity['description'] : '' ?></div>
                <input type="hidden" name="description" id="input-description">
            </div>
            
            <div class="form-group">
                <label>Konten Detail</label>
                <div id="toolbar-content" class="ql-toolbar-custom">
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
                        <button class="ql-header" value="1"></button>
                        <button class="ql-header" value="2"></button>
                        <button class="ql-blockquote"></button>
                    </span>
                    <span class="ql-formats">
                        <button class="ql-list" value="ordered"></button>
                        <button class="ql-list" value="bullet"></button>
                        <button class="ql-indent" value="-1"></button>
                        <button class="ql-indent" value="+1"></button>
                    </span>
                    <span class="ql-formats">
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
                <div id="editor-content" class="quill-editor"><?= $editActivity ? $editActivity['content'] : '' ?></div>
                <input type="hidden" name="content" id="input-content">
            </div>
            
            <div class="form-group">
                <label>Upload Gambar</label>
                
                <!-- Existing Images with Delete Option -->
                <?php if ($editActivity && $editActivity['images']): 
                    $existingImages = json_decode($editActivity['images'], true);
                    if (!empty($existingImages)):
                ?>
                <div style="margin-bottom: 15px;">
                    <small style="color: #666;">Gambar saat ini (klik × untuk hapus):</small>
                    <div id="existing-images" style="margin-top: 8px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php foreach ($existingImages as $index => $img): ?>
                        <div class="existing-img-item" style="position: relative;">
                            <img src="<?= UPLOAD_URL . $img ?>" alt="" style="width: 80px; height: 60px; object-fit: cover; border-radius: 5px; border: 2px solid #ddd;">
                            <input type="hidden" name="keep_images[]" value="<?= $img ?>">
                            <button type="button" onclick="removeExistingImage(this)" style="position: absolute; top: -8px; right: -8px; background: #E74C3C; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 1;">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; endif; ?>
                
                <!-- Dynamic New Image Inputs -->
                <div id="image-inputs-container">
                    <div class="image-input-row" style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                        <input type="file" name="images[]" class="form-control" accept="image/*" style="flex: 1;">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeImageInput(this)" style="display: none;">×</button>
                    </div>
                </div>
                
                <button type="button" class="btn btn-sm btn-secondary" onclick="addImageInput()" style="margin-top: 5px;">
                    ➕ Tambah Gambar Lagi
                </button>
                <small style="display: block; margin-top: 8px; color: #666;">Bisa tambah gambar satu per satu atau pilih beberapa sekaligus</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" <?= !$editActivity || $editActivity['is_active'] ? 'checked' : '' ?>> Aktif
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary"><?= $editActivity ? 'Update' : 'Tambah' ?> Kegiatan</button>
            <?php if ($editActivity): ?>
            <a href="social-activities.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Activities List -->
<div class="panel">
    <div class="panel-header">
        <h2>📋 Daftar Kegiatan</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th width="100">Gambar</th>
                    <th>Judul</th>
                    <th width="120">Tanggal</th>
                    <th>Lokasi</th>
                    <th width="80">Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">Belum ada kegiatan</td>
                </tr>
                <?php else: ?>
                <?php foreach ($activities as $activity): 
                    $imgs = json_decode($activity['images'], true);
                    $firstImg = !empty($imgs) ? $imgs[0] : '';
                ?>
                <tr>
                    <td>
                        <?php if ($firstImg): ?>
                        <img src="<?= UPLOAD_URL . $firstImg ?>" alt="" style="width: 80px; height: 60px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                        <div style="width: 80px; height: 60px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">🤝</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($activity['title']) ?></strong>
                        <br><small style="color: #666;"><?= truncateText($activity['description'], 60) ?></small>
                    </td>
                    <td><?= $activity['event_date'] ? formatDate($activity['event_date']) : '-' ?></td>
                    <td><?= htmlspecialchars($activity['location'] ?: '-') ?></td>
                    <td>
                        <span class="badge badge-<?= $activity['is_active'] ? 'completed' : 'cancelled' ?>">
                            <?= $activity['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?= $activity['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $activity['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kegiatan ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quill Editor CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
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
    min-height: 80px;
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
const quillDescription = new Quill('#editor-description', {
    modules: { toolbar: '#toolbar-description' },
    placeholder: 'Tulis deskripsi singkat...',
    theme: 'snow',
});

const quillContent = new Quill('#editor-content', {
    modules: { 
        syntax: true,
        toolbar: '#toolbar-content' 
    },
    placeholder: 'Tulis konten detail kegiatan...',
    theme: 'snow',
});

// Copy content to hidden inputs before form submit
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('input-description').value = quillDescription.root.innerHTML;
    document.getElementById('input-content').value = quillContent.root.innerHTML;
});

// Dynamic Image Input Functions
function addImageInput() {
    const container = document.getElementById('image-inputs-container');
    const row = document.createElement('div');
    row.className = 'image-input-row';
    row.style.cssText = 'display: flex; gap: 10px; align-items: center; margin-bottom: 10px;';
    row.innerHTML = `
        <input type="file" name="images[]" class="form-control" accept="image/*" style="flex: 1;">
        <button type="button" class="btn btn-sm btn-danger" onclick="removeImageInput(this)">×</button>
    `;
    container.appendChild(row);
    
    // Show delete button on first row if there are multiple rows
    updateDeleteButtons();
}

function removeImageInput(btn) {
    btn.closest('.image-input-row').remove();
    updateDeleteButtons();
}

function updateDeleteButtons() {
    const rows = document.querySelectorAll('.image-input-row');
    rows.forEach((row, index) => {
        const deleteBtn = row.querySelector('.btn-danger');
        if (deleteBtn) {
            deleteBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
    });
}

function removeExistingImage(btn) {
    if (confirm('Hapus gambar ini?')) {
        btn.closest('.existing-img-item').remove();
    }
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>

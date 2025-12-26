<?php
/**
 * Admin - Kelola Video Dakwah
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM dakwah_videos WHERE id = ?")->execute([$_GET['delete']]);
        setFlash('success', 'Video berhasil dihapus');
    }
    header('Location: dakwah-videos.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = sanitize($_POST['title']);
    $youtubeUrl = sanitize($_POST['youtube_url']);
    $description = $_POST['description'] ?? '';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    // Extract YouTube ID
    $youtubeId = '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $matches)) {
        $youtubeId = $matches[1];
    }
    
    if ($id) {
        // Update
        $stmt = db()->prepare("UPDATE dakwah_videos SET title=?, youtube_url=?, youtube_id=?, description=?, is_featured=?, is_active=?, sort_order=? WHERE id=?");
        $stmt->execute([$title, $youtubeUrl, $youtubeId, $description, $isFeatured, $isActive, $sortOrder, $id]);
        setFlash('success', 'Video berhasil diperbarui');
    } else {
        // Insert
        $stmt = db()->prepare("INSERT INTO dakwah_videos (title, youtube_url, youtube_id, description, is_featured, is_active, sort_order) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$title, $youtubeUrl, $youtubeId, $description, $isFeatured, $isActive, $sortOrder]);
        setFlash('success', 'Video berhasil ditambahkan');
    }
    header('Location: dakwah-videos.php');
    exit;
}

$pageTitle = 'Video Dakwah';
require_once 'includes/admin-header.php';

// Get all videos
$videos = db()->query("SELECT * FROM dakwah_videos ORDER BY sort_order ASC, created_at DESC")->fetchAll();

// Edit mode
$editVideo = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM dakwah_videos WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editVideo = $stmt->fetch();
}
?>

<div class="content-header">
    <h1>🎬 Video Dakwah</h1>
    <p>Kelola video YouTube dakwah</p>
</div>

<!-- Add/Edit Form -->
<div class="panel">
    <div class="panel-header">
        <h2><?= $editVideo ? '✏️ Edit Video' : '➕ Tambah Video' ?></h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="">
            <?php if ($editVideo): ?>
            <input type="hidden" name="id" value="<?= $editVideo['id'] ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Judul Video *</label>
                    <input type="text" name="title" class="form-control" required value="<?= $editVideo ? htmlspecialchars($editVideo['title']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>URL YouTube *</label>
                    <input type="url" name="youtube_url" class="form-control" required placeholder="https://www.youtube.com/watch?v=..." value="<?= $editVideo ? htmlspecialchars($editVideo['youtube_url']) : '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= $editVideo ? htmlspecialchars($editVideo['description']) : '' ?></textarea>
            </div>
            
            <div style="display: flex; gap: 30px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_featured" <?= $editVideo && $editVideo['is_featured'] ? 'checked' : '' ?>> Video Utama (Featured)
                </label>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" <?= !$editVideo || $editVideo['is_active'] ? 'checked' : '' ?>> Aktif
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label>Urutan:</label>
                    <input type="number" name="sort_order" class="form-control" style="width: 80px;" value="<?= $editVideo ? $editVideo['sort_order'] : 0 ?>">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><?= $editVideo ? 'Update' : 'Tambah' ?> Video</button>
            <?php if ($editVideo): ?>
            <a href="dakwah-videos.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Videos List -->
<div class="panel">
    <div class="panel-header">
        <h2>📺 Daftar Video</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th width="100">Preview</th>
                    <th>Judul</th>
                    <th width="80">Urutan</th>
                    <th width="80">Featured</th>
                    <th width="80">Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($videos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">Belum ada video</td>
                </tr>
                <?php else: ?>
                <?php foreach ($videos as $video): ?>
                <tr>
                    <td>
                        <img src="https://img.youtube.com/vi/<?= $video['youtube_id'] ?>/mqdefault.jpg" alt="" style="width: 100px; border-radius: 5px;">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($video['title']) ?></strong>
                        <br><small style="color: #666;"><?= truncateText($video['description'], 60) ?></small>
                    </td>
                    <td><?= $video['sort_order'] ?></td>
                    <td><?= $video['is_featured'] ? '⭐' : '-' ?></td>
                    <td>
                        <span class="badge badge-<?= $video['is_active'] ? 'completed' : 'cancelled' ?>">
                            <?= $video['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?= $video['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $video['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus video ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

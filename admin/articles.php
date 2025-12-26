<?php
/**
 * Admin Articles List
 * Toko Islami - Admin Panel
 */

$pageTitle = 'Kelola Artikel';
require_once 'includes/admin-header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    
    if ($article) {
        if ($article['image']) {
            deleteImage($article['image']);
        }
        db()->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
        setFlash('success', 'Artikel berhasil dihapus');
    }
    header('Location: articles.php');
    exit;
}

// Get articles
$articles = db()->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();
?>

<div class="panel">
    <div class="panel-header">
        <h2>📝 Daftar Artikel (<?= count($articles) ?>)</h2>
        <a href="add-article.php" class="btn btn-primary">✏️ Tulis Artikel</a>
    </div>
    <div class="panel-body" style="padding: 0; overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th width="80">Gambar</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
                        <p>Belum ada artikel. <a href="add-article.php">Tulis artikel pertama</a></p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td>
                        <img src="<?= getImageUrl($article['image'], 'assets/images/article-default.png') ?>" alt="">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($article['title']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($article['author']) ?></td>
                    <td><?= number_format($article['views']) ?>x</td>
                    <td>
                        <?php if ($article['is_published']): ?>
                        <span class="badge badge-completed">Published</span>
                        <?php else: ?>
                        <span class="badge badge-pending">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?= formatDate($article['created_at']) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/article-detail.php?slug=<?= $article['slug'] ?>" class="btn btn-sm btn-secondary" target="_blank">👁️</a>
                        <a href="edit-article.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <a href="articles.php?delete=<?= $article['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus artikel ini?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

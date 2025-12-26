<?php
/**
 * Article Detail Page
 * Toko Islami - Online Shop & Artikel
 */

require_once '../../includes/header.php';

// Get article by slug
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("SELECT * FROM articles WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    setFlash('danger', 'Artikel tidak ditemukan');
    header('Location: index.php');
    exit;
}

$pageTitle = $article['title'];

// Update views
db()->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

// Get related articles
$relatedStmt = db()->prepare("
    SELECT * FROM articles 
    WHERE id != ? AND is_published = 1 
    ORDER BY created_at DESC 
    LIMIT 3
");
$relatedStmt->execute([$article['id']]);
$relatedArticles = $relatedStmt->fetchAll();
?>

<!-- Breadcrumb -->
<section style="background: var(--cream-dark); padding: 20px 0;">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Beranda</a>
            <span>›</span>
            <a href="<?= BASE_URL ?>/articles.php">Artikel</a>
            <span>›</span>
            <span style="color: var(--primary);"><?= truncateText($article['title'], 50) ?></span>
        </div>
    </div>
</section>

<!-- Article Detail -->
<section class="article-detail">
    <div class="container">
        <div class="article-detail-content">
            <!-- Meta -->
            <div class="meta">
                <span>📅 <?= formatDate($article['created_at']) ?></span>
                <span>👤 <?= htmlspecialchars($article['author']) ?></span>
                <span>👁️ <?= $article['views'] ?> kali dibaca</span>
            </div>
            
            <!-- Title -->
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            
            <!-- Featured Image -->
            <?php if ($article['image']): ?>
            <img src="<?= getImageUrl($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="featured-image">
            <?php endif; ?>
            
            <!-- Content -->
            <div class="content">
                <?= $article['content'] ?>
            </div>
            
            <!-- Share -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid var(--cream);">
                <h4 style="margin-bottom: 15px;">Bagikan Artikel:</h4>
                <div style="display: flex; gap: 10px;">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/article-detail.php?slug=' . $article['slug']) ?>" target="_blank" class="btn btn-sm" style="background: #3b5998; color: white;">
                        📘 Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/article-detail.php?slug=' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="btn btn-sm" style="background: #1da1f2; color: white;">
                        🐦 Twitter
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . BASE_URL . '/article-detail.php?slug=' . $article['slug']) ?>" target="_blank" class="btn btn-sm" style="background: #25d366; color: white;">
                        📱 WhatsApp
                    </a>
                </div>
            </div>
            
            <!-- Author Box -->
            <div style="margin-top: 40px; padding: 30px; background: var(--cream); border-radius: var(--radius-md); display: flex; align-items: center; gap: 20px;">
                <div style="width: 80px; height: 80px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white;">
                    👤
                </div>
                <div>
                    <h4 style="margin-bottom: 5px;">Ditulis oleh <?= htmlspecialchars($article['author']) ?></h4>
                    <p style="color: var(--text-secondary); margin: 0;">
                        Tim penulis Toko Islami yang berkomitmen untuk menyebarkan ilmu dan inspirasi Islami.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Articles -->
<?php if (!empty($relatedArticles)): ?>
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div class="section-title">
            <h2>Artikel Lainnya</h2>
            <p>Baca juga artikel menarik lainnya</p>
        </div>
        
        <div class="articles-grid">
            <?php foreach ($relatedArticles as $related): ?>
            <article class="card">
                <div class="card-image" style="aspect-ratio: 16/9;">
                    <img src="<?= getImageUrl($related['image'], 'assets/images/article-default.png') ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                </div>
                <div class="card-body">
                    <div class="card-meta">
                        <span>📅 <?= formatDate($related['created_at']) ?></span>
                        <span>👤 <?= htmlspecialchars($related['author']) ?></span>
                    </div>
                    <h3 class="card-title">
                        <a href="detail.php?slug=<?= $related['slug'] ?>"><?= htmlspecialchars($related['title']) ?></a>
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">
                        <?= truncateText(strip_tags($related['excerpt'] ?? $related['content']), 100) ?>
                    </p>
                    <a href="detail.php?slug=<?= $related['slug'] ?>" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                        Baca Selengkapnya →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>

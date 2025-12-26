<?php
/**
 * Articles Listing Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Artikel Islami';
require_once '../../includes/header.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;

// Count total articles
$totalArticles = db()->query("SELECT COUNT(*) FROM articles WHERE is_published = 1")->fetchColumn();
$pagination = paginate($totalArticles, $page, $perPage);

// Get articles
$stmt = db()->prepare("
    SELECT * FROM articles 
    WHERE is_published = 1 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $pagination['per_page'], PDO::PARAM_INT);
$stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <span style="color: var(--gold);">Artikel</span>
        </div>
        <h1 style="color: white;">📖 Artikel Islami</h1>
        <p style="color: rgba(255,255,255,0.8);">Inspirasi dan pengetahuan untuk kehidupan yang lebih barokah</p>
    </div>
</section>

<!-- Articles Section -->
<section class="section">
    <div class="container">
        <?php if (empty($articles)): ?>
        
        <div class="empty-state">
            <div style="font-size: 4rem;">📚</div>
            <h3>Belum Ada Artikel</h3>
            <p>Artikel akan segera hadir, InsyaAllah.</p>
            <a href="<?= BASE_URL ?>" class="btn btn-primary">Kembali ke Beranda</a>
        </div>
        
        <?php else: ?>
        
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="card">
                <div class="card-image" style="aspect-ratio: 16/9;">
                    <img src="<?= getImageUrl($article['image'], 'assets/images/article-default.png') ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
                <div class="card-body">
                    <div class="card-meta">
                        <span>📅 <?= formatDate($article['created_at']) ?></span>
                        <span>👤 <?= htmlspecialchars($article['author']) ?></span>
                        <?php if ($article['views'] > 0): ?>
                        <span>👁️ <?= $article['views'] ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="card-title">
                        <a href="detail.php?slug=<?= $article['slug'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6;">
                        <?= truncateText(strip_tags($article['excerpt'] ?? $article['content']), 150) ?>
                    </p>
                    <a href="detail.php?slug=<?= $article['slug'] ?>" class="btn btn-secondary btn-sm" style="margin-top: 15px;">
                        Baca Selengkapnya →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?= renderPagination($pagination, 'articles.php') ?>
        
        <?php endif; ?>
    </div>
</section>

<!-- Quote Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; text-align: center;">
    <div class="container">
        <span style="font-size: 3rem; color: var(--gold);">۞</span>
        <blockquote style="max-width: 700px; margin: 20px auto; font-size: 1.3rem; font-style: italic; line-height: 1.8;">
            "Barangsiapa yang menempuh jalan untuk mencari ilmu, maka Allah akan memudahkan baginya jalan menuju surga."
        </blockquote>
        <cite style="color: var(--gold);">— HR. Muslim</cite>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

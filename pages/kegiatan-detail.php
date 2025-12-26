<?php
/**
 * Social Activity Detail Page - Kegiatan Sosial
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Kegiatan Sosial';
require_once '../includes/header.php';

// Get activity by slug
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: aktivitas.php');
    exit;
}

$stmt = db()->prepare("SELECT * FROM social_activities WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$activity = $stmt->fetch();

if (!$activity) {
    setFlash('danger', 'Kegiatan tidak ditemukan');
    header('Location: aktivitas.php');
    exit;
}

$pageTitle = $activity['title'];
$images = json_decode($activity['images'], true) ?: [];

// Get related activities
$relatedStmt = db()->prepare("
    SELECT * FROM social_activities 
    WHERE id != ? AND is_active = 1 
    ORDER BY event_date DESC 
    LIMIT 3
");
$relatedStmt->execute([$activity['id']]);
$relatedActivities = $relatedStmt->fetchAll();
?>

<!-- Breadcrumb -->
<section style="background: var(--cream-dark); padding: 20px 0;">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Beranda</a>
            <span>›</span>
            <a href="<?= BASE_URL ?>/pages/aktivitas.php">Aktivitas</a>
            <span>›</span>
            <span style="color: var(--primary);"><?= truncateText($activity['title'], 50) ?></span>
        </div>
    </div>
</section>

<!-- Activity Detail -->
<section class="section">
    <div class="container">
        <div class="activity-detail-layout">
            <!-- Main Content -->
            <div class="activity-main">
                <!-- Meta Info -->
                <div class="activity-meta-info">
                    <?php if ($activity['event_date']): ?>
                    <span>📅 <?= formatDate($activity['event_date']) ?></span>
                    <?php endif; ?>
                    <?php if ($activity['location']): ?>
                    <span>📍 <?= htmlspecialchars($activity['location']) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Title -->
                <h1 class="activity-detail-title"><?= htmlspecialchars($activity['title']) ?></h1>
                
                <!-- Description -->
                <?php if ($activity['description']): ?>
                <div class="activity-excerpt">
                    <?= $activity['description'] ?>
                </div>
                <?php endif; ?>
                
                <!-- Image Gallery -->
                <?php if (!empty($images)): ?>
                <div class="activity-gallery">
                    <div class="gallery-main">
                        <img id="main-gallery-image" src="<?= UPLOAD_URL . $images[0] ?>" alt="<?= htmlspecialchars($activity['title']) ?>">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $index => $img): ?>
                        <img src="<?= UPLOAD_URL . $img ?>" alt="" class="thumb <?= $index === 0 ? 'active' : '' ?>" onclick="changeMainImage(this, '<?= UPLOAD_URL . $img ?>')">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="activity-content">
                    <?= $activity['content'] ?: '<p>Tidak ada konten detail untuk kegiatan ini.</p>' ?>
                </div>
                
                <!-- Share -->
                <div class="share-section">
                    <h4>📤 Bagikan Kegiatan</h4>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?= urlencode($activity['title'] . ' - ' . BASE_URL . '/pages/kegiatan-detail.php?slug=' . $activity['slug']) ?>" 
                           target="_blank" class="share-btn whatsapp">WhatsApp</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/pages/kegiatan-detail.php?slug=' . $activity['slug']) ?>" 
                           target="_blank" class="share-btn facebook">Facebook</a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($activity['title']) ?>&url=<?= urlencode(BASE_URL . '/pages/kegiatan-detail.php?slug=' . $activity['slug']) ?>" 
                           target="_blank" class="share-btn twitter">Twitter</a>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="activity-sidebar">
                <div class="sidebar-card">
                    <h3>🤝 Kegiatan Lainnya</h3>
                    <?php if (empty($relatedActivities)): ?>
                    <p style="color: #666;">Belum ada kegiatan lainnya</p>
                    <?php else: ?>
                    <div class="related-list">
                        <?php foreach ($relatedActivities as $related): 
                            $relatedImgs = json_decode($related['images'], true);
                            $relatedImg = !empty($relatedImgs) ? $relatedImgs[0] : '';
                        ?>
                        <a href="kegiatan-detail.php?slug=<?= $related['slug'] ?>" class="related-item">
                            <div class="related-thumb">
                                <?php if ($relatedImg): ?>
                                <img src="<?= UPLOAD_URL . $relatedImg ?>" alt="">
                                <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white;">🤝</div>
                                <?php endif; ?>
                            </div>
                            <div class="related-info">
                                <div class="related-title"><?= htmlspecialchars(truncateText($related['title'], 40)) ?></div>
                                <?php if ($related['event_date']): ?>
                                <div class="related-date">📅 <?= formatDate($related['event_date']) ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <a href="aktivitas.php" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                    ← Kembali ke Aktivitas
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.activity-detail-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

.activity-meta-info {
    display: flex;
    gap: 20px;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.activity-detail-title {
    color: var(--primary-dark);
    font-size: 2rem;
    line-height: 1.3;
    margin-bottom: 20px;
}

.activity-excerpt {
    font-size: 1.1rem;
    color: var(--text-secondary);
    padding: 20px;
    background: var(--cream-dark);
    border-left: 4px solid var(--gold);
    border-radius: 0 10px 10px 0;
    margin-bottom: 25px;
    line-height: 1.7;
}

.activity-gallery {
    margin-bottom: 30px;
}

.gallery-main {
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.gallery-main img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

.gallery-thumbs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.gallery-thumbs .thumb {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.3s;
    opacity: 0.7;
}

.gallery-thumbs .thumb:hover,
.gallery-thumbs .thumb.active {
    border-color: var(--gold);
    opacity: 1;
}

.activity-content {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--text-primary);
    margin-bottom: 30px;
}

.activity-content p {
    margin-bottom: 1em;
}

.activity-content h2, .activity-content h3 {
    color: var(--primary-dark);
    margin: 1.5em 0 0.5em;
}

.activity-content ul, .activity-content ol {
    padding-left: 25px;
    margin-bottom: 1em;
}

.share-section {
    padding-top: 25px;
    border-top: 1px solid #eee;
}

.share-section h4 {
    color: var(--primary-dark);
    margin-bottom: 15px;
}

.share-buttons {
    display: flex;
    gap: 10px;
}

.share-btn {
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    color: white;
    font-size: 0.85rem;
    transition: transform 0.3s;
}

.share-btn:hover {
    transform: scale(1.05);
}

.share-btn.whatsapp { background: #25D366; }
.share-btn.facebook { background: #1877F2; }
.share-btn.twitter { background: #1DA1F2; }

/* Sidebar */
.sidebar-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.sidebar-card h3 {
    color: var(--primary-dark);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--gold);
}

.related-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.related-item {
    display: flex;
    gap: 12px;
    text-decoration: none;
    padding: 10px;
    border-radius: 8px;
    transition: background 0.3s;
}

.related-item:hover {
    background: var(--cream-dark);
}

.related-thumb {
    width: 70px;
    height: 50px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.related-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-title {
    color: var(--text-primary);
    font-weight: 500;
    font-size: 0.9rem;
    line-height: 1.3;
}

.related-date {
    color: #666;
    font-size: 0.75rem;
    margin-top: 3px;
}

@media (max-width: 768px) {
    .activity-detail-layout {
        grid-template-columns: 1fr;
    }
    
    .activity-detail-title {
        font-size: 1.5rem;
    }
    
    .gallery-main img {
        height: 250px;
    }
    
    .share-buttons {
        flex-wrap: wrap;
    }
}
</style>

<script>
function changeMainImage(thumb, src) {
    document.getElementById('main-gallery-image').src = src;
    document.querySelectorAll('.gallery-thumbs .thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
</script>

<?php require_once '../includes/footer.php'; ?>

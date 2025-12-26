<?php
/**
 * Aktivitas Page - Acara Dakwah & Kegiatan Sosial
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Aktivitas Kami';
require_once '../includes/header.php';

// Get featured video
$featuredVideo = db()->query("SELECT * FROM dakwah_videos WHERE is_featured = 1 AND is_active = 1 ORDER BY sort_order ASC LIMIT 1")->fetch();

// Get playlist videos
$playlistVideos = db()->query("SELECT * FROM dakwah_videos WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6")->fetchAll();

// Get social activities
$activities = db()->query("SELECT * FROM social_activities WHERE is_active = 1 ORDER BY event_date DESC, created_at DESC LIMIT 6")->fetchAll();

// Extract YouTube ID from URL
function getYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return '';
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 50px 0; text-align: center;">
    <div class="container">
        <span style="font-size: 3rem; color: var(--gold);">۞</span>
        <h1 style="color: white; margin: 15px 0;">Aktivitas Kami</h1>
        <p style="color: rgba(255,255,255,0.8);">Acara Dakwah & Kegiatan Sosial Keagamaan</p>
    </div>
</section>

<!-- Acara Dakwah Section -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>🎬 Acara Dakwah</h2>
            <p>Video kajian dan ceramah Islami</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Main Video -->
            <div>
                <?php if ($featuredVideo || !empty($playlistVideos)): ?>
                <?php 
                    // Use featured or first video
                    $mainVideo = $featuredVideo ?: $playlistVideos[0];
                ?>
                <div class="video-main" id="main-video-container">
                    <div class="video-wrapper">
                        <iframe 
                            id="main-video-iframe"
                            src="https://www.youtube.com/embed/<?= getYouTubeId($mainVideo['youtube_url']) ?>" 
                            title="<?= htmlspecialchars($mainVideo['title']) ?>"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div style="padding: 20px 0;">
                        <h3 id="main-video-title" style="color: var(--primary-dark); margin-bottom: 10px;"><?= htmlspecialchars($mainVideo['title']) ?></h3>
                        <p id="main-video-desc" style="color: var(--text-secondary); line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($mainVideo['description'])) ?>
                        </p>
                    </div>
                </div>
                <?php else: ?>
                <div style="background: #f8f9fa; border-radius: 12px; padding: 60px; text-align: center;">
                    <span style="font-size: 4rem;">🎬</span>
                    <p style="color: #666; margin-top: 15px;">Belum ada video dakwah</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar Playlist -->
            <div>
                <h4 style="color: var(--primary-dark); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--gold);">
                    📺 Video Lainnya
                </h4>
                <div class="video-playlist">
                    <?php if (empty($playlistVideos)): ?>
                    <p style="color: #666; text-align: center; padding: 20px;">Belum ada video</p>
                    <?php else: ?>
                    <?php foreach ($playlistVideos as $video): ?>
                    <a href="javascript:void(0)" 
                       class="playlist-item <?= ($mainVideo && $mainVideo['id'] == $video['id']) ? 'active' : '' ?>" 
                       data-video-id="<?= getYouTubeId($video['youtube_url']) ?>"
                       data-title="<?= htmlspecialchars($video['title']) ?>"
                       data-desc="<?= htmlspecialchars($video['description']) ?>"
                       onclick="playVideo(this)">
                        <div class="playlist-thumb">
                            <img src="https://img.youtube.com/vi/<?= getYouTubeId($video['youtube_url']) ?>/mqdefault.jpg" alt="">
                            <span class="play-icon">▶</span>
                        </div>
                        <div class="playlist-info">
                            <div class="playlist-title"><?= htmlspecialchars(truncateText($video['title'], 50)) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kegiatan Sosial Section -->
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div class="section-title">
            <h2>🤝 Kegiatan Sosial dan Agama</h2>
            <p>Dokumentasi kegiatan sosial dan keagamaan</p>
        </div>
        
        <?php if (empty($activities)): ?>
        <div style="text-align: center; padding: 60px;">
            <span style="font-size: 4rem;">🤝</span>
            <p style="color: #666; margin-top: 15px;">Belum ada kegiatan sosial</p>
        </div>
        <?php else: ?>
        <div class="activities-grid">
            <?php foreach ($activities as $activity): ?>
            <?php 
                $images = json_decode($activity['images'], true) ?: [];
                $firstImage = !empty($images) ? $images[0] : '';
            ?>
            <div class="activity-card">
                <div class="activity-carousel" data-images='<?= json_encode($images) ?>'>
                    <?php if (!empty($images)): ?>
                    <div class="carousel-container">
                        <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= UPLOAD_URL . $img ?>" alt="">
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($images) > 1): ?>
                        <button class="carousel-btn prev" onclick="slideCarousel(this, -1)">❮</button>
                        <button class="carousel-btn next" onclick="slideCarousel(this, 1)">❯</button>
                        <div class="carousel-dots">
                            <?php foreach ($images as $index => $img): ?>
                            <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(this, <?= $index ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 3rem; color: white;">🤝</span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="activity-body">
                    <div class="activity-meta">
                        <?php if ($activity['event_date']): ?>
                        <span>📅 <?= formatDate($activity['event_date']) ?></span>
                        <?php endif; ?>
                        <?php if ($activity['location']): ?>
                        <span>📍 <?= htmlspecialchars($activity['location']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="activity-title"><?= htmlspecialchars($activity['title']) ?></h3>
                    <p class="activity-desc"><?= htmlspecialchars(truncateText(strip_tags($activity['description']), 120)) ?></p>
                    <a href="kegiatan-detail.php?slug=<?= $activity['slug'] ?>" class="btn-read-more">
                        Baca Selengkapnya →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Video Styles */
.video-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Playlist Styles */
.video-playlist {
    max-height: 450px;
    overflow-y: auto;
}

.playlist-item {
    display: flex;
    gap: 12px;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
    margin-bottom: 8px;
}

.playlist-item:hover, .playlist-item.active {
    background: #f0f9f7;
}

.playlist-item.active {
    border-left: 3px solid var(--gold);
}

.playlist-thumb {
    width: 120px;
    height: 68px;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}

.playlist-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.playlist-thumb .play-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.7);
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.playlist-title {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1.4;
}

/* Activities Grid */
.activities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
}

.activity-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.activity-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* Carousel */
.carousel-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 10px 12px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s;
}

.carousel-btn:hover {
    background: rgba(0,0,0,0.8);
}

.carousel-btn.prev { left: 10px; border-radius: 5px; }
.carousel-btn.next { right: 10px; border-radius: 5px; }

.carousel-dots {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
}

.carousel-dots .dot {
    width: 10px;
    height: 10px;
    background: rgba(255,255,255,0.5);
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.3s;
}

.carousel-dots .dot.active {
    background: white;
}

.activity-body {
    padding: 20px;
}

.activity-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.activity-title {
    font-size: 1.1rem;
    color: var(--primary-dark);
    margin-bottom: 10px;
    line-height: 1.4;
}

.activity-desc {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 0 0 15px 0;
}

.btn-read-more {
    display: inline-block;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-read-more:hover {
    color: var(--primary-dark);
    transform: translateX(5px);
}

/* Responsive */
@media (max-width: 768px) {
    .activities-grid {
        grid-template-columns: 1fr;
    }
    
    section .container > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function slideCarousel(btn, direction) {
    const container = btn.closest('.carousel-container');
    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.dot');
    let current = 0;
    
    slides.forEach((slide, i) => {
        if (slide.classList.contains('active')) current = i;
    });
    
    slides[current].classList.remove('active');
    if (dots[current]) dots[current].classList.remove('active');
    
    let next = current + direction;
    if (next >= slides.length) next = 0;
    if (next < 0) next = slides.length - 1;
    
    slides[next].classList.add('active');
    if (dots[next]) dots[next].classList.add('active');
}

function goToSlide(dot, index) {
    const container = dot.closest('.carousel-container');
    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.dot');
    
    slides.forEach(s => s.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));
    
    slides[index].classList.add('active');
    dots[index].classList.add('active');
}

// Play selected video in main player
function playVideo(element) {
    const videoId = element.dataset.videoId;
    const title = element.dataset.title;
    const desc = element.dataset.desc;
    
    // Update iframe
    const iframe = document.getElementById('main-video-iframe');
    iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
    
    // Update title and description
    document.getElementById('main-video-title').textContent = title;
    document.getElementById('main-video-desc').innerHTML = desc.replace(/\n/g, '<br>');
    
    // Update active state in playlist
    document.querySelectorAll('.playlist-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
    
    // Scroll to video
    document.getElementById('main-video-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php require_once '../includes/footer.php'; ?>

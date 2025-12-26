<?php
/**
 * Quiz Page - Kuis Islami
 * Toko Islami - Inspired by Ruang Guru
 */

$pageTitle = 'Kuis Islami';
require_once '../includes/header.php';

// Get all active quizzes with question counts
$quizzes = db()->query("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
    FROM quizzes q 
    WHERE q.is_active = 1 
    ORDER BY q.created_at DESC
")->fetchAll();

// Get categories
$categories = db()->query("SELECT DISTINCT category FROM quizzes WHERE category IS NOT NULL AND category != '' AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Hero Section -->
<section class="quiz-hero">
    <div class="container">
        <div class="quiz-hero-content">
            <span class="quiz-label">🕌 KUIS ISLAMI</span>
            <h1>Uji Pengetahuan Agamamu!</h1>
            <p>Pelajari dan uji pemahaman tentang Islam melalui kuis interaktif. Dapatkan skor terbaik dan tingkatkan ilmu agamamu!</p>
            <div class="quiz-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= count($quizzes) ?></span>
                    <span class="stat-label">Kuis</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= array_sum(array_column($quizzes, 'question_count')) ?></span>
                    <span class="stat-label">Soal</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= array_sum(array_column($quizzes, 'attempt_count')) ?></span>
                    <span class="stat-label">Peserta</span>
                </div>
            </div>
        </div>
        <div class="quiz-hero-image">
            <div class="hero-icon">📖</div>
        </div>
    </div>
</section>

<!-- Category Filter -->
<?php if (!empty($categories)): ?>
<section class="quiz-categories">
    <div class="container">
        <div class="category-pills">
            <button class="pill active" onclick="filterQuiz('all')">Semua</button>
            <?php foreach ($categories as $cat): ?>
            <button class="pill" onclick="filterQuiz('<?= htmlspecialchars($cat) ?>')"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Quiz Grid -->
<section class="section quiz-section">
    <div class="container">
        <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <span>📝</span>
            <h3>Belum Ada Kuis</h3>
            <p>Kuis sedang dalam persiapan. Nantikan segera!</p>
        </div>
        <?php else: ?>
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card" data-category="<?= htmlspecialchars($quiz['category']) ?>">
                <div class="quiz-card-image">
                    <?php if ($quiz['image']): ?>
                    <img src="<?= UPLOAD_URL . $quiz['image'] ?>" alt="<?= htmlspecialchars($quiz['title']) ?>">
                    <?php else: ?>
                    <div class="quiz-placeholder">
                        <?php
                        $icons = ['📖', '🕌', '☪️', '📿', '🤲', '📚'];
                        echo $icons[array_rand($icons)];
                        ?>
                    </div>
                    <?php endif; ?>
                    <div class="quiz-difficulty <?= $quiz['difficulty'] ?>">
                        <?php 
                        $diffLabels = ['mudah' => '🟢 Mudah', 'sedang' => '🟡 Sedang', 'sulit' => '🔴 Sulit'];
                        echo $diffLabels[$quiz['difficulty']];
                        ?>
                    </div>
                </div>
                <div class="quiz-card-body">
                    <?php if ($quiz['category']): ?>
                    <span class="quiz-category"><?= htmlspecialchars($quiz['category']) ?></span>
                    <?php endif; ?>
                    <h3 class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></h3>
                    <p class="quiz-desc"><?= htmlspecialchars(truncateText($quiz['description'], 80)) ?></p>
                    <div class="quiz-meta">
                        <span>📋 <?= $quiz['question_count'] ?> Soal</span>
                        <?php if ($quiz['time_limit'] > 0): ?>
                        <span>⏱️ <?= $quiz['time_limit'] ?> Menit</span>
                        <?php endif; ?>
                        <span>👥 <?= $quiz['attempt_count'] ?>x</span>
                    </div>
                </div>
                <div class="quiz-card-footer">
                    <a href="quiz-play.php?slug=<?= $quiz['slug'] ?>" class="btn-start-quiz">
                        Mulai Kuis →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Leaderboard Preview -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary));">
    <div class="container">
        <div class="leaderboard-section">
            <div class="leaderboard-header">
                <h2 style="color: white;">🏆 Top Scorer</h2>
                <p style="color: rgba(255,255,255,0.8);">Peserta dengan nilai tertinggi</p>
            </div>
            <div class="leaderboard-list">
                <?php
                $topScorers = db()->query("
                    SELECT qa.*, q.title as quiz_title
                    FROM quiz_attempts qa
                    JOIN quizzes q ON qa.quiz_id = q.id
                    ORDER BY qa.score DESC
                    LIMIT 5
                ")->fetchAll();
                
                if (empty($topScorers)):
                ?>
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.7);">
                    <p>Belum ada peserta. Jadilah yang pertama!</p>
                </div>
                <?php else: ?>
                <?php foreach ($topScorers as $index => $scorer): ?>
                <div class="leaderboard-item">
                    <div class="rank">
                        <?php if ($index === 0): ?>🥇
                        <?php elseif ($index === 1): ?>🥈
                        <?php elseif ($index === 2): ?>🥉
                        <?php else: ?><?= $index + 1 ?>
                        <?php endif; ?>
                    </div>
                    <div class="scorer-info">
                        <div class="scorer-name"><?= htmlspecialchars($scorer['user_name'] ?: 'Anonymous') ?></div>
                        <div class="scorer-quiz"><?= htmlspecialchars($scorer['quiz_title']) ?></div>
                    </div>
                    <div class="scorer-score"><?= $scorer['score'] ?>%</div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Quiz Hero */
.quiz-hero {
    background: linear-gradient(135deg, #1A5276, #2ECC71);
    padding: 60px 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.quiz-hero .container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.quiz-hero-content {
    max-width: 600px;
}

.quiz-label {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.quiz-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: white;
}

.quiz-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 30px;
    line-height: 1.7;
}

.quiz-stats {
    display: flex;
    gap: 40px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--gold);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.quiz-hero-image {
    width: 250px;
    height: 250px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-icon {
    font-size: 8rem;
}

/* Categories */
.quiz-categories {
    background: white;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.category-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.pill {
    padding: 10px 25px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 30px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.pill:hover, .pill.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

/* Quiz Grid */
.quiz-section {
    background: #f8f9fa;
}

.quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.quiz-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.quiz-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.quiz-card-image {
    position: relative;
    height: 160px;
}

.quiz-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.quiz-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1A5276, #2ECC71);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
}

.quiz-difficulty {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
}

.quiz-card-body {
    padding: 20px;
}

.quiz-category {
    display: inline-block;
    background: var(--cream-dark);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.quiz-title {
    font-size: 1.2rem;
    color: var(--primary-dark);
    margin-bottom: 10px;
    line-height: 1.3;
}

.quiz-desc {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
}

.quiz-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #999;
}

.quiz-card-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.btn-start-quiz {
    display: block;
    text-align: center;
    background: linear-gradient(135deg, var(--primary), #2ECC71);
    color: white;
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-start-quiz:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
}

/* Leaderboard */
.leaderboard-section {
    max-width: 600px;
    margin: 0 auto;
}

.leaderboard-header {
    text-align: center;
    margin-bottom: 30px;
}

.leaderboard-list {
    background: rgba(255,255,255,0.1);
    border-radius: 15px;
    overflow: hidden;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.leaderboard-item:last-child {
    border-bottom: none;
}

.rank {
    width: 40px;
    font-size: 1.5rem;
    text-align: center;
}

.scorer-info {
    flex: 1;
    margin-left: 15px;
}

.scorer-name {
    color: white;
    font-weight: 600;
}

.scorer-quiz {
    color: rgba(255,255,255,0.6);
    font-size: 0.8rem;
}

.scorer-score {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gold);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-state span {
    font-size: 4rem;
}

.empty-state h3 {
    color: var(--primary-dark);
    margin: 20px 0 10px;
}

.empty-state p {
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .quiz-hero .container {
        flex-direction: column;
        text-align: center;
    }
    
    .quiz-hero h1 {
        font-size: 1.8rem;
    }
    
    .quiz-hero-image {
        display: none;
    }
    
    .quiz-stats {
        justify-content: center;
    }
    
    .quiz-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function filterQuiz(category) {
    const pills = document.querySelectorAll('.pill');
    pills.forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    
    const cards = document.querySelectorAll('.quiz-card');
    cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>

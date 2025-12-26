<?php
/**
 * Admin - Kelola Skor Kuis
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM quiz_attempts WHERE id = ?")->execute([$_GET['delete']]);
        setFlash('success', 'Skor berhasil dihapus');
    }
    header('Location: quiz-scores.php' . (isset($_GET['quiz_id']) ? '?quiz_id=' . $_GET['quiz_id'] : ''));
    exit;
}

// Handle delete all for a quiz
if (isset($_GET['delete_all']) && is_numeric($_GET['delete_all'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?")->execute([$_GET['delete_all']]);
        setFlash('success', 'Semua skor untuk kuis ini berhasil dihapus');
    }
    header('Location: quiz-scores.php');
    exit;
}

$pageTitle = 'Skor Kuis';
require_once 'includes/admin-header.php';

// Filter by quiz
$quizFilter = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Get all quizzes for filter
$quizzes = db()->query("SELECT id, title FROM quizzes ORDER BY title")->fetchAll();

// Get scores
$whereClause = $quizFilter ? "WHERE qa.quiz_id = $quizFilter" : "";
$scores = db()->query("
    SELECT qa.*, q.title as quiz_title, q.passing_score
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    $whereClause
    ORDER BY qa.completed_at DESC
    LIMIT 100
")->fetchAll();

// Statistics
$stats = db()->query("
    SELECT 
        COUNT(*) as total_attempts,
        AVG(score) as avg_score,
        MAX(score) as max_score,
        MIN(score) as min_score
    FROM quiz_attempts
")->fetch();
?>

<div class="content-header">
    <h1>🏆 Skor Kuis</h1>
    <p>Lihat dan kelola hasil kuis peserta</p>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #3498DB, #2980B9); color: white; padding: 20px; border-radius: 12px;">
        <div style="font-size: 2rem; font-weight: 700;"><?= $stats['total_attempts'] ?? 0 ?></div>
        <div style="opacity: 0.8;">Total Percobaan</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #27AE60, #229954); color: white; padding: 20px; border-radius: 12px;">
        <div style="font-size: 2rem; font-weight: 700;"><?= number_format($stats['avg_score'] ?? 0, 1) ?>%</div>
        <div style="opacity: 0.8;">Rata-rata Skor</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #F39C12, #E67E22); color: white; padding: 20px; border-radius: 12px;">
        <div style="font-size: 2rem; font-weight: 700;"><?= $stats['max_score'] ?? 0 ?>%</div>
        <div style="opacity: 0.8;">Skor Tertinggi</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #E74C3C, #C0392B); color: white; padding: 20px; border-radius: 12px;">
        <div style="font-size: 2rem; font-weight: 700;"><?= $stats['min_score'] ?? 0 ?>%</div>
        <div style="opacity: 0.8;">Skor Terendah</div>
    </div>
</div>

<!-- Filter -->
<div class="panel">
    <div class="panel-body" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <label style="font-weight: 600;">📋 Filter Kuis:</label>
            <select onchange="window.location.href='quiz-scores.php' + (this.value ? '?quiz_id=' + this.value : '')" class="form-control" style="width: auto;">
                <option value="">Semua Kuis</option>
                <?php foreach ($quizzes as $q): ?>
                <option value="<?= $q['id'] ?>" <?= $quizFilter == $q['id'] ? 'selected' : '' ?>><?= htmlspecialchars($q['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($quizFilter): ?>
        <a href="?delete_all=<?= $quizFilter ?>" class="btn btn-danger" onclick="return confirm('Hapus SEMUA skor untuk kuis ini?')">
            🗑️ Hapus Semua Skor
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Scores Table -->
<div class="panel">
    <div class="panel-header">
        <h2>📊 Daftar Skor (<?= count($scores) ?> hasil)</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <?php if (empty($scores)): ?>
        <div style="text-align: center; padding: 50px;">
            <span style="font-size: 3rem;">📊</span>
            <p style="color: #666; margin-top: 10px;">Belum ada skor kuis</p>
        </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Peserta</th>
                    <th>Email</th>
                    <th>Kuis</th>
                    <th width="100">Skor</th>
                    <th width="80">Benar</th>
                    <th width="80">Waktu</th>
                    <th width="150">Tanggal</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $index => $score): 
                    $isPassed = $score['score'] >= $score['passing_score'];
                ?>
                <tr>
                    <td style="text-align: center;"><?= $index + 1 ?></td>
                    <td>
                        <strong><?= htmlspecialchars($score['user_name'] ?: 'Anonymous') ?></strong>
                    </td>
                    <td style="color: #666;"><?= htmlspecialchars($score['user_email'] ?: '-') ?></td>
                    <td>
                        <a href="quiz-questions.php?quiz_id=<?= $score['quiz_id'] ?>" style="color: var(--primary);">
                            <?= htmlspecialchars(truncateText($score['quiz_title'], 30)) ?>
                        </a>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; 
                            background: <?= $isPassed ? '#D5F5E3' : '#FADBD8' ?>; 
                            color: <?= $isPassed ? '#27AE60' : '#E74C3C' ?>;">
                            <?= $score['score'] ?>%
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <?= $score['correct_answers'] ?>/<?= $score['total_questions'] ?>
                    </td>
                    <td style="text-align: center;">
                        <?php 
                        $mins = floor($score['time_taken'] / 60);
                        $secs = $score['time_taken'] % 60;
                        echo $mins . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
                        ?>
                    </td>
                    <td><?= formatDate($score['completed_at']) ?></td>
                    <td>
                        <a href="?delete=<?= $score['id'] ?><?= $quizFilter ? '&quiz_id=' . $quizFilter : '' ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Hapus skor ini?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Top Scorers by Quiz -->
<div class="panel">
    <div class="panel-header">
        <h2>🏅 Top Scorer per Kuis</h2>
    </div>
    <div class="panel-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php
            $topPerQuiz = db()->query("
                SELECT qa.*, q.title as quiz_title
                FROM quiz_attempts qa
                JOIN quizzes q ON qa.quiz_id = q.id
                WHERE qa.score = (
                    SELECT MAX(score) FROM quiz_attempts WHERE quiz_id = qa.quiz_id
                )
                GROUP BY qa.quiz_id
                ORDER BY qa.score DESC
                LIMIT 6
            ")->fetchAll();
            
            if (empty($topPerQuiz)):
            ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 30px; color: #666;">
                Belum ada data scorer
            </div>
            <?php else: ?>
            <?php foreach ($topPerQuiz as $top): ?>
            <div style="background: linear-gradient(135deg, #f8f9fa, #fff); padding: 20px; border-radius: 12px; border: 1px solid #eee;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.8rem; color: #666; margin-bottom: 5px;"><?= htmlspecialchars($top['quiz_title']) ?></div>
                        <div style="font-weight: 700; color: var(--primary-dark);">🏆 <?= htmlspecialchars($top['user_name'] ?: 'Anonymous') ?></div>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--gold);"><?= $top['score'] ?>%</div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    <a href="quizzes.php" class="btn btn-secondary">← Kembali ke Daftar Kuis</a>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

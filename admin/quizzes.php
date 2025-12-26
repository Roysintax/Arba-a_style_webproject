<?php
/**
 * Admin - Kelola Kuis Islami
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete quiz
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$_GET['delete']]);
        setFlash('success', 'Kuis berhasil dihapus');
    }
    header('Location: quizzes.php');
    exit;
}

// Handle delete score
if (isset($_GET['delete_score']) && is_numeric($_GET['delete_score'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM quiz_attempts WHERE id = ?")->execute([$_GET['delete_score']]);
        setFlash('success', 'Skor peserta berhasil dihapus');
    }
    header('Location: quizzes.php#scores');
    exit;
}

// Handle CSV download
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    $scores = db()->query("
        SELECT qa.user_name, qa.user_email, q.title as quiz_title, qa.score, 
               qa.correct_answers, qa.total_questions, qa.time_taken, qa.completed_at
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        ORDER BY qa.completed_at DESC
    ")->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="skor_kuis_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    fputcsv($output, ['Nama', 'Email', 'Kuis', 'Skor (%)', 'Benar', 'Total Soal', 'Waktu (detik)', 'Tanggal']);
    
    // Data
    foreach ($scores as $s) {
        fputcsv($output, [
            $s['user_name'] ?: 'Anonymous',
            $s['user_email'] ?: '-',
            $s['quiz_title'],
            $s['score'],
            $s['correct_answers'],
            $s['total_questions'],
            $s['time_taken'],
            $s['completed_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Handle add/edit quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_quiz') {
    $id = $_POST['id'] ?? null;
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $description = $_POST['description'] ?? '';
    $category = sanitize($_POST['category'] ?? '');
    $difficulty = sanitize($_POST['difficulty'] ?? 'sedang');
    $timeLimit = (int)($_POST['time_limit'] ?? 0);
    $passingScore = (int)($_POST['passing_score'] ?? 70);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image
    $image = '';
    if ($id) {
        $stmt = db()->prepare("SELECT image FROM quizzes WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn() ?: '';
    }
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'quizzes');
        if ($upload['success']) {
            $image = $upload['filename'];
        }
    }
    
    if ($id) {
        $stmt = db()->prepare("UPDATE quizzes SET title=?, slug=?, description=?, category=?, difficulty=?, time_limit=?, passing_score=?, image=?, is_active=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $category, $difficulty, $timeLimit, $passingScore, $image, $isActive, $id]);
        setFlash('success', 'Kuis berhasil diperbarui');
    } else {
        $stmt = db()->prepare("INSERT INTO quizzes (title, slug, description, category, difficulty, time_limit, passing_score, image, is_active) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $description, $category, $difficulty, $timeLimit, $passingScore, $image, $isActive]);
        setFlash('success', 'Kuis berhasil ditambahkan');
    }
    header('Location: quizzes.php');
    exit;
}

$pageTitle = 'Kuis Islami';
require_once 'includes/admin-header.php';

// Get all quizzes with question count
$quizzes = db()->query("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
    FROM quizzes q 
    ORDER BY q.created_at DESC
")->fetchAll();

// Edit mode
$editQuiz = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editQuiz = $stmt->fetch();
}

$categories = ['Fiqih', 'Akidah', 'Sirah Nabawiyah', 'Al-Quran', 'Hadits', 'Akhlak', 'Ibadah', 'Umum'];
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>📝 Kuis Islami</h1>
        <p>Kelola kuis dan soal-soal pengetahuan Islam</p>
    </div>
    <a href="quiz-scores.php" class="btn btn-primary">🏆 Lihat Skor</a>
</div>

<!-- Add/Edit Quiz Form -->
<div class="panel">
    <div class="panel-header">
        <h2><?= $editQuiz ? '✏️ Edit Kuis' : '➕ Tambah Kuis Baru' ?></h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_quiz">
            <?php if ($editQuiz): ?>
            <input type="hidden" name="id" value="<?= $editQuiz['id'] ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div>
                    <div class="form-group">
                        <label>Judul Kuis *</label>
                        <input type="text" name="title" class="form-control" required value="<?= $editQuiz ? htmlspecialchars($editQuiz['title']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"><?= $editQuiz ? htmlspecialchars($editQuiz['description']) : '' ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="category" class="form-control">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($editQuiz && $editQuiz['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-control">
                                <option value="mudah" <?= ($editQuiz && $editQuiz['difficulty'] === 'mudah') ? 'selected' : '' ?>>🟢 Mudah</option>
                                <option value="sedang" <?= (!$editQuiz || $editQuiz['difficulty'] === 'sedang') ? 'selected' : '' ?>>🟡 Sedang</option>
                                <option value="sulit" <?= ($editQuiz && $editQuiz['difficulty'] === 'sulit') ? 'selected' : '' ?>>🔴 Sulit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batas Waktu (menit)</label>
                            <input type="number" name="time_limit" class="form-control" value="<?= $editQuiz ? $editQuiz['time_limit'] : 0 ?>" min="0">
                            <small style="color: #666;">0 = tanpa batas</small>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label>Gambar Cover</label>
                        <?php if ($editQuiz && $editQuiz['image']): ?>
                        <img src="<?= UPLOAD_URL . $editQuiz['image'] ?>" alt="" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Nilai Lulus (%)</label>
                        <input type="number" name="passing_score" class="form-control" value="<?= $editQuiz ? $editQuiz['passing_score'] : 70 ?>" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="is_active" <?= !$editQuiz || $editQuiz['is_active'] ? 'checked' : '' ?>> Aktif
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><?= $editQuiz ? 'Update' : 'Tambah' ?> Kuis</button>
                <?php if ($editQuiz): ?>
                <a href="quizzes.php" class="btn btn-secondary">Batal</a>
                <a href="quiz-questions.php?quiz_id=<?= $editQuiz['id'] ?>" class="btn btn-warning">📋 Kelola Soal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Quiz List -->
<div class="panel">
    <div class="panel-header">
        <h2>📚 Daftar Kuis</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th width="80">Cover</th>
                    <th>Judul</th>
                    <th width="100">Kategori</th>
                    <th width="80">Level</th>
                    <th width="80">Soal</th>
                    <th width="80">Peserta</th>
                    <th width="70">Status</th>
                    <th width="200">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">Belum ada kuis</td>
                </tr>
                <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                <tr>
                    <td>
                        <?php if ($quiz['image']): ?>
                        <img src="<?= UPLOAD_URL . $quiz['image'] ?>" alt="" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                        <div style="width: 60px; height: 45px; background: linear-gradient(135deg, var(--primary), var(--gold)); border-radius: 5px; display: flex; align-items: center; justify-content: center; color: white;">📝</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($quiz['title']) ?></strong>
                        <br><small style="color: #666;"><?= truncateText($quiz['description'], 50) ?></small>
                    </td>
                    <td><?= $quiz['category'] ?: '-' ?></td>
                    <td>
                        <?php 
                        $diffColors = ['mudah' => '🟢', 'sedang' => '🟡', 'sulit' => '🔴'];
                        echo $diffColors[$quiz['difficulty']] . ' ' . ucfirst($quiz['difficulty']);
                        ?>
                    </td>
                    <td style="text-align: center;"><strong><?= $quiz['question_count'] ?></strong></td>
                    <td style="text-align: center;"><?= $quiz['attempt_count'] ?></td>
                    <td>
                        <span class="badge badge-<?= $quiz['is_active'] ? 'completed' : 'cancelled' ?>">
                            <?= $quiz['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="quiz-questions.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-primary">📋 Soal</a>
                        <a href="?edit=<?= $quiz['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $quiz['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kuis ini beserta semua soalnya?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scores Table -->
<div class="panel" id="scores">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>🏆 Skor Peserta</h2>
        <a href="?download=csv" class="btn btn-sm" style="background: #27AE60; color: white;">📥 Download CSV</a>
    </div>
    <div class="panel-body" style="padding: 0;">
        <?php
        $scores = db()->query("
            SELECT qa.*, q.title as quiz_title, q.passing_score
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            ORDER BY qa.completed_at DESC
            LIMIT 20
        ")->fetchAll();
        ?>
        
        <?php if (empty($scores)): ?>
        <div style="text-align: center; padding: 40px;">
            <span style="font-size: 3rem;">📊</span>
            <p style="color: #666; margin-top: 10px;">Belum ada peserta yang mengerjakan kuis</p>
        </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Peserta</th>
                    <th>Kuis</th>
                    <th width="100">Skor</th>
                    <th width="100">Benar/Total</th>
                    <th width="80">Waktu</th>
                    <th width="150">Tanggal</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $index => $score): 
                    $isPassed = $score['score'] >= $score['passing_score'];
                    $mins = floor($score['time_taken'] / 60);
                    $secs = $score['time_taken'] % 60;
                ?>
                <tr>
                    <td style="text-align: center;"><?= $index + 1 ?></td>
                    <td>
                        <strong><?= htmlspecialchars($score['user_name'] ?: 'Anonymous') ?></strong>
                        <?php if ($score['user_email']): ?>
                        <br><small style="color: #666;"><?= htmlspecialchars($score['user_email']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(truncateText($score['quiz_title'], 25)) ?></td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9rem;
                            background: <?= $isPassed ? '#D5F5E3' : '#FADBD8' ?>; 
                            color: <?= $isPassed ? '#27AE60' : '#E74C3C' ?>;">
                            <?= $score['score'] ?>%
                        </span>
                    </td>
                    <td style="text-align: center;"><?= $score['correct_answers'] ?>/<?= $score['total_questions'] ?></td>
                    <td style="text-align: center;"><?= $mins ?>:<?= str_pad($secs, 2, '0', STR_PAD_LEFT) ?></td>
                    <td><?= formatDate($score['completed_at']) ?></td>
                    <td>
                        <a href="?delete_score=<?= $score['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus skor peserta ini?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding: 15px; text-align: center; background: #f8f9fa;">
            <a href="quiz-scores.php" class="btn btn-secondary">📊 Lihat Semua Skor</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

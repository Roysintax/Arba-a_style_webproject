<?php
/**
 * Admin - Kelola Soal Kuis
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if (!$quizId) {
    header('Location: quizzes.php');
    exit;
}

// Get quiz info
$quiz = db()->prepare("SELECT * FROM quizzes WHERE id = ?");
$quiz->execute([$quizId]);
$quiz = $quiz->fetch();

if (!$quiz) {
    setFlash('danger', 'Kuis tidak ditemukan');
    header('Location: quizzes.php');
    exit;
}

// Handle delete question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM quiz_questions WHERE id = ? AND quiz_id = ?")->execute([$_GET['delete'], $quizId]);
        setFlash('success', 'Soal berhasil dihapus');
    }
    header('Location: quiz-questions.php?quiz_id=' . $quizId);
    exit;
}

// Handle add/edit question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $question = $_POST['question'] ?? '';
    $optionA = $_POST['option_a'] ?? '';
    $optionB = $_POST['option_b'] ?? '';
    $optionC = $_POST['option_c'] ?? '';
    $optionD = $_POST['option_d'] ?? '';
    $correctAnswer = $_POST['correct_answer'] ?? 'a';
    $explanation = $_POST['explanation'] ?? '';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    if ($id) {
        $stmt = db()->prepare("UPDATE quiz_questions SET question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_answer=?, explanation=?, sort_order=? WHERE id=? AND quiz_id=?");
        $stmt->execute([$question, $optionA, $optionB, $optionC, $optionD, $correctAnswer, $explanation, $sortOrder, $id, $quizId]);
        setFlash('success', 'Soal berhasil diperbarui');
    } else {
        $stmt = db()->prepare("INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$quizId, $question, $optionA, $optionB, $optionC, $optionD, $correctAnswer, $explanation, $sortOrder]);
        setFlash('success', 'Soal berhasil ditambahkan');
    }
    header('Location: quiz-questions.php?quiz_id=' . $quizId);
    exit;
}

$pageTitle = 'Soal Kuis: ' . $quiz['title'];
require_once 'includes/admin-header.php';

// Get all questions
$questions = db()->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC");
$questions->execute([$quizId]);
$questions = $questions->fetchAll();

// Edit mode
$editQuestion = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM quiz_questions WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$_GET['edit'], $quizId]);
    $editQuestion = $stmt->fetch();
}
?>

<div class="content-header">
    <h1>📋 Soal Kuis</h1>
    <p>Kelola soal untuk: <strong><?= htmlspecialchars($quiz['title']) ?></strong></p>
</div>

<div style="margin-bottom: 20px;">
    <a href="quizzes.php" class="btn btn-secondary">← Kembali ke Daftar Kuis</a>
</div>

<!-- Add/Edit Question Form -->
<div class="panel">
    <div class="panel-header">
        <h2><?= $editQuestion ? '✏️ Edit Soal' : '➕ Tambah Soal Baru' ?></h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="">
            <?php if ($editQuestion): ?>
            <input type="hidden" name="id" value="<?= $editQuestion['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Pertanyaan *</label>
                <textarea name="question" class="form-control" rows="3" required><?= $editQuestion ? htmlspecialchars($editQuestion['question']) : '' ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div class="form-group">
                    <label style="color: <?= ($editQuestion && $editQuestion['correct_answer'] === 'a') ? '#27AE60' : 'inherit' ?>;">
                        Opsi A <?= ($editQuestion && $editQuestion['correct_answer'] === 'a') ? '✅' : '' ?>
                    </label>
                    <input type="text" name="option_a" class="form-control" required value="<?= $editQuestion ? htmlspecialchars($editQuestion['option_a']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="color: <?= ($editQuestion && $editQuestion['correct_answer'] === 'b') ? '#27AE60' : 'inherit' ?>;">
                        Opsi B <?= ($editQuestion && $editQuestion['correct_answer'] === 'b') ? '✅' : '' ?>
                    </label>
                    <input type="text" name="option_b" class="form-control" required value="<?= $editQuestion ? htmlspecialchars($editQuestion['option_b']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="color: <?= ($editQuestion && $editQuestion['correct_answer'] === 'c') ? '#27AE60' : 'inherit' ?>;">
                        Opsi C <?= ($editQuestion && $editQuestion['correct_answer'] === 'c') ? '✅' : '' ?>
                    </label>
                    <input type="text" name="option_c" class="form-control" required value="<?= $editQuestion ? htmlspecialchars($editQuestion['option_c']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="color: <?= ($editQuestion && $editQuestion['correct_answer'] === 'd') ? '#27AE60' : 'inherit' ?>;">
                        Opsi D <?= ($editQuestion && $editQuestion['correct_answer'] === 'd') ? '✅' : '' ?>
                    </label>
                    <input type="text" name="option_d" class="form-control" required value="<?= $editQuestion ? htmlspecialchars($editQuestion['option_d']) : '' ?>">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 20px;">
                <div class="form-group">
                    <label>Jawaban Benar *</label>
                    <select name="correct_answer" class="form-control" style="font-size: 1.1rem; font-weight: bold; color: #27AE60;">
                        <option value="a" <?= ($editQuestion && $editQuestion['correct_answer'] === 'a') ? 'selected' : '' ?>>A</option>
                        <option value="b" <?= ($editQuestion && $editQuestion['correct_answer'] === 'b') ? 'selected' : '' ?>>B</option>
                        <option value="c" <?= ($editQuestion && $editQuestion['correct_answer'] === 'c') ? 'selected' : '' ?>>C</option>
                        <option value="d" <?= ($editQuestion && $editQuestion['correct_answer'] === 'd') ? 'selected' : '' ?>>D</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $editQuestion ? $editQuestion['sort_order'] : count($questions) + 1 ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Penjelasan (muncul setelah menjawab)</label>
                    <input type="text" name="explanation" class="form-control" value="<?= $editQuestion ? htmlspecialchars($editQuestion['explanation']) : '' ?>" placeholder="Opsional">
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><?= $editQuestion ? 'Update' : 'Tambah' ?> Soal</button>
                <?php if ($editQuestion): ?>
                <a href="quiz-questions.php?quiz_id=<?= $quizId ?>" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Questions List -->
<div class="panel">
    <div class="panel-header">
        <h2>📝 Daftar Soal (<?= count($questions) ?> soal)</h2>
    </div>
    <div class="panel-body" style="padding: 0;">
        <?php if (empty($questions)): ?>
        <div style="text-align: center; padding: 40px;">
            <span style="font-size: 3rem;">📋</span>
            <p style="color: #666; margin-top: 10px;">Belum ada soal. Tambahkan soal pertama!</p>
        </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Pertanyaan</th>
                    <th width="80">Opsi A</th>
                    <th width="80">Opsi B</th>
                    <th width="80">Opsi C</th>
                    <th width="80">Opsi D</th>
                    <th width="70">Jawaban</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $index => $q): ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars(truncateText($q['question'], 80)) ?></td>
                    <td style="<?= $q['correct_answer'] === 'a' ? 'background: #D5F5E3; font-weight: bold;' : '' ?>"><?= truncateText($q['option_a'], 15) ?></td>
                    <td style="<?= $q['correct_answer'] === 'b' ? 'background: #D5F5E3; font-weight: bold;' : '' ?>"><?= truncateText($q['option_b'], 15) ?></td>
                    <td style="<?= $q['correct_answer'] === 'c' ? 'background: #D5F5E3; font-weight: bold;' : '' ?>"><?= truncateText($q['option_c'], 15) ?></td>
                    <td style="<?= $q['correct_answer'] === 'd' ? 'background: #D5F5E3; font-weight: bold;' : '' ?>"><?= truncateText($q['option_d'], 15) ?></td>
                    <td style="text-align: center;">
                        <span style="display: inline-block; width: 30px; height: 30px; background: #27AE60; color: white; border-radius: 50%; line-height: 30px; font-weight: bold;">
                            <?= strtoupper($q['correct_answer']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="?quiz_id=<?= $quizId ?>&edit=<?= $q['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?quiz_id=<?= $quizId ?>&delete=<?= $q['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus soal ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

<?php
/**
 * Quiz Play Page - Mengerjakan Kuis
 * Toko Islami - Interactive Quiz
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: quiz.php');
    exit;
}

// Get quiz
$stmt = db()->prepare("SELECT * FROM quizzes WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$quiz = $stmt->fetch();

if (!$quiz) {
    setFlash('danger', 'Kuis tidak ditemukan');
    header('Location: quiz.php');
    exit;
}

// Get questions
$stmt = db()->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$quiz['id']]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    setFlash('warning', 'Kuis ini belum memiliki soal');
    header('Location: quiz.php');
    exit;
}

$pageTitle = $quiz['title'];
require_once '../includes/header.php';
?>

<!-- Quiz Container -->
<div class="quiz-play-container">
    <!-- Quiz Header -->
    <div class="quiz-play-header">
        <div class="container">
            <div class="quiz-header-content">
                <div class="quiz-info">
                    <a href="quiz.php" class="back-link">← Kembali</a>
                    <h1><?= htmlspecialchars($quiz['title']) ?></h1>
                </div>
                <div class="quiz-timer" id="timer" <?= $quiz['time_limit'] <= 0 ? 'style="display:none;"' : '' ?>>
                    <span class="timer-icon">⏱️</span>
                    <span class="timer-value" id="timer-value"><?= str_pad($quiz['time_limit'], 2, '0', STR_PAD_LEFT) ?>:00</span>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>
    </div>
    
    <!-- Start Screen -->
    <div class="quiz-screen" id="start-screen">
        <div class="start-content">
            <div class="start-icon">📖</div>
            <h2><?= htmlspecialchars($quiz['title']) ?></h2>
            <p><?= htmlspecialchars($quiz['description']) ?></p>
            
            <div class="quiz-details">
                <div class="detail-item">
                    <span class="detail-icon">📋</span>
                    <span class="detail-text"><?= count($questions) ?> Soal</span>
                </div>
                <?php if ($quiz['time_limit'] > 0): ?>
                <div class="detail-item">
                    <span class="detail-icon">⏱️</span>
                    <span class="detail-text"><?= $quiz['time_limit'] ?> Menit</span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-icon">🎯</span>
                    <span class="detail-text">Nilai Lulus: <?= $quiz['passing_score'] ?>%</span>
                </div>
            </div>
            
            <div class="user-form">
                <div class="form-group">
                    <input type="text" id="user-name" class="form-control" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group">
                    <input type="email" id="user-email" class="form-control" placeholder="Email (opsional)">
                </div>
            </div>
            
            <button class="btn-begin" onclick="startQuiz()">
                🚀 Mulai Kuis
            </button>
        </div>
    </div>
    
    <!-- Questions Screen -->
    <div class="quiz-screen" id="questions-screen" style="display: none;">
        <div class="question-container">
            <div class="question-number" id="question-number">Soal 1 dari <?= count($questions) ?></div>
            <div class="question-text" id="question-text"></div>
            
            <div class="options-container" id="options-container">
                <!-- Options will be inserted here -->
            </div>
            
            <div class="question-nav">
                <button class="btn-nav prev" id="btn-prev" onclick="prevQuestion()" disabled>← Sebelumnya</button>
                <button class="btn-nav next" id="btn-next" onclick="nextQuestion()">Selanjutnya →</button>
                <button class="btn-submit" id="btn-submit" onclick="submitQuiz()" style="display: none;">✅ Selesai</button>
            </div>
        </div>
    </div>
    
    <!-- Result Screen -->
    <div class="quiz-screen" id="result-screen" style="display: none;">
        <div class="result-content">
            <div class="result-icon" id="result-icon">🎉</div>
            <h2 id="result-title">Selamat!</h2>
            <div class="score-circle" id="score-circle">
                <span class="score-value" id="score-value">0</span>
                <span class="score-label">Nilai</span>
            </div>
            <div class="result-stats">
                <div class="result-stat">
                    <span class="stat-value" id="correct-count">0</span>
                    <span class="stat-label">Benar</span>
                </div>
                <div class="result-stat">
                    <span class="stat-value" id="wrong-count">0</span>
                    <span class="stat-label">Salah</span>
                </div>
                <div class="result-stat">
                    <span class="stat-value" id="time-taken">0:00</span>
                    <span class="stat-label">Waktu</span>
                </div>
            </div>
            <div class="result-message" id="result-message"></div>
            <div class="result-actions">
                <button class="btn-review" onclick="reviewAnswers()">📝 Lihat Pembahasan</button>
                <a href="quiz.php" class="btn-back">← Kembali ke Kuis</a>
            </div>
        </div>
    </div>
    
    <!-- Review Screen -->
    <div class="quiz-screen" id="review-screen" style="display: none;">
        <div class="review-container" id="review-container">
            <!-- Review will be inserted here -->
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <a href="quiz.php" class="btn btn-primary">← Kembali ke Daftar Kuis</a>
        </div>
    </div>
</div>

<style>
.quiz-play-container {
    min-height: 100vh;
    background: #f0f4f8;
}

/* Header */
.quiz-play-header {
    background: linear-gradient(135deg, #1A5276, #2ECC71);
    padding: 20px 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.quiz-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.back-link {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.9rem;
}

.quiz-info h1 {
    color: white;
    font-size: 1.3rem;
    margin: 5px 0 0;
}

.quiz-timer {
    background: rgba(255,255,255,0.2);
    padding: 10px 20px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timer-value {
    color: white;
    font-size: 1.3rem;
    font-weight: 700;
    font-family: monospace;
}

.progress-bar-container {
    height: 6px;
    background: rgba(255,255,255,0.2);
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--gold);
    transition: width 0.3s;
}

/* Screens */
.quiz-screen {
    padding: 40px 20px;
    max-width: 800px;
    margin: 0 auto;
}

/* Start Screen */
.start-content {
    background: white;
    border-radius: 20px;
    padding: 50px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.start-icon {
    font-size: 5rem;
    margin-bottom: 20px;
}

.start-content h2 {
    color: var(--primary-dark);
    margin-bottom: 15px;
}

.start-content p {
    color: #666;
    margin-bottom: 30px;
}

.quiz-details {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-bottom: 30px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.detail-icon {
    font-size: 2rem;
}

.detail-text {
    color: #666;
    font-size: 0.9rem;
}

.user-form {
    max-width: 400px;
    margin: 0 auto 30px;
}

.user-form .form-control {
    text-align: center;
    margin-bottom: 15px;
}

.btn-begin {
    background: linear-gradient(135deg, var(--primary), #2ECC71);
    color: white;
    border: none;
    padding: 15px 50px;
    font-size: 1.2rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-begin:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
}

/* Questions */
.question-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.question-number {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 20px;
}

.question-text {
    font-size: 1.3rem;
    color: var(--text-primary);
    line-height: 1.6;
    margin-bottom: 30px;
}

.options-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.option-btn {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px 25px;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: left;
}

.option-btn:hover {
    border-color: var(--primary);
    background: #f0f9f7;
}

.option-btn.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f0f9f7, #e8f5e9);
}

.option-btn.correct {
    border-color: #27AE60;
    background: #D5F5E3;
}

.option-btn.wrong {
    border-color: #E74C3C;
    background: #FADBD8;
}

.option-letter {
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.option-btn.selected .option-letter {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.option-text {
    flex: 1;
    font-size: 1rem;
}

/* Navigation */
.question-nav {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-nav {
    padding: 12px 30px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s;
}

.btn-nav.prev {
    background: #e0e0e0;
    color: #666;
}

.btn-nav.next {
    background: var(--primary);
    color: white;
}

.btn-nav:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-submit {
    background: linear-gradient(135deg, #27AE60, #2ECC71);
    color: white;
    border: none;
    padding: 12px 40px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
}

/* Result */
.result-content {
    background: white;
    border-radius: 20px;
    padding: 50px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.result-icon {
    font-size: 5rem;
    margin-bottom: 20px;
}

.score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #2ECC71);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 30px auto;
    box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
}

.score-value {
    font-size: 3rem;
    font-weight: 700;
    color: white;
}

.score-label {
    color: rgba(255,255,255,0.8);
}

.result-stats {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin: 30px 0;
}

.result-stat {
    text-align: center;
}

.result-stat .stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-dark);
}

.result-stat .stat-label {
    color: #666;
    font-size: 0.9rem;
}

.result-message {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 30px;
}

.result-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-review {
    background: var(--gold);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
}

.btn-back {
    background: #e0e0e0;
    color: #666;
    text-decoration: none;
    padding: 12px 30px;
    border-radius: 25px;
}

/* Review */
.review-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-item {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.review-item.correct {
    border-left: 5px solid #27AE60;
}

.review-item.wrong {
    border-left: 5px solid #E74C3C;
}

.review-question {
    font-size: 1.1rem;
    color: var(--text-primary);
    margin-bottom: 15px;
}

.review-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.review-option {
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 0.95rem;
}

.review-option.correct-answer {
    background: #D5F5E3;
    color: #27AE60;
    font-weight: 600;
}

.review-option.user-wrong {
    background: #FADBD8;
    color: #E74C3C;
}

.review-explanation {
    background: #FFF9E6;
    padding: 15px;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #856404;
}

@media (max-width: 768px) {
    .quiz-details {
        flex-direction: column;
        gap: 20px;
    }
    
    .result-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .result-actions {
        flex-direction: column;
    }
}
</style>

<script>
const questions = <?= json_encode($questions) ?>;
const quizId = <?= $quiz['id'] ?>;
const timeLimit = <?= $quiz['time_limit'] ?> * 60;
const passingScore = <?= $quiz['passing_score'] ?>;

let currentQuestion = 0;
let userAnswers = {};
let timerInterval;
let timeRemaining = timeLimit;
let startTime;

function startQuiz() {
    const userName = document.getElementById('user-name').value.trim();
    if (!userName) {
        alert('Mohon masukkan nama Anda');
        return;
    }
    
    document.getElementById('start-screen').style.display = 'none';
    document.getElementById('questions-screen').style.display = 'block';
    
    startTime = new Date();
    showQuestion(0);
    
    if (timeLimit > 0) {
        startTimer();
    }
}

function showQuestion(index) {
    const q = questions[index];
    document.getElementById('question-number').textContent = `Soal ${index + 1} dari ${questions.length}`;
    document.getElementById('question-text').textContent = q.question;
    
    const optionsHtml = ['a', 'b', 'c', 'd'].map(opt => `
        <button class="option-btn ${userAnswers[index] === opt ? 'selected' : ''}" onclick="selectOption('${opt}')">
            <span class="option-letter">${opt.toUpperCase()}</span>
            <span class="option-text">${q['option_' + opt]}</span>
        </button>
    `).join('');
    
    document.getElementById('options-container').innerHTML = optionsHtml;
    
    // Update navigation
    document.getElementById('btn-prev').disabled = index === 0;
    document.getElementById('btn-next').style.display = index < questions.length - 1 ? 'block' : 'none';
    document.getElementById('btn-submit').style.display = index === questions.length - 1 ? 'block' : 'none';
    
    // Update progress
    const progress = ((index + 1) / questions.length) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
}

function selectOption(opt) {
    userAnswers[currentQuestion] = opt;
    showQuestion(currentQuestion);
}

function nextQuestion() {
    if (currentQuestion < questions.length - 1) {
        currentQuestion++;
        showQuestion(currentQuestion);
    }
}

function prevQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        showQuestion(currentQuestion);
    }
}

function startTimer() {
    timerInterval = setInterval(() => {
        timeRemaining--;
        const mins = Math.floor(timeRemaining / 60);
        const secs = timeRemaining % 60;
        document.getElementById('timer-value').textContent = 
            String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            submitQuiz();
        }
    }, 1000);
}

function submitQuiz() {
    if (timerInterval) clearInterval(timerInterval);
    
    const endTime = new Date();
    const timeTaken = Math.floor((endTime - startTime) / 1000);
    
    // Calculate score
    let correct = 0;
    questions.forEach((q, i) => {
        if (userAnswers[i] === q.correct_answer) correct++;
    });
    
    const score = Math.round((correct / questions.length) * 100);
    const passed = score >= passingScore;
    
    // Show results
    document.getElementById('questions-screen').style.display = 'none';
    document.getElementById('result-screen').style.display = 'block';
    
    document.getElementById('result-icon').textContent = passed ? '🎉' : '😔';
    document.getElementById('result-title').textContent = passed ? 'Selamat!' : 'Coba Lagi!';
    document.getElementById('score-value').textContent = score;
    document.getElementById('correct-count').textContent = correct;
    document.getElementById('wrong-count').textContent = questions.length - correct;
    document.getElementById('time-taken').textContent = 
        Math.floor(timeTaken / 60) + ':' + String(timeTaken % 60).padStart(2, '0');
    
    document.getElementById('result-message').textContent = passed ? 
        'Alhamdulillah! Anda telah lulus kuis ini.' : 
        'Jangan menyerah! Pelajari lagi dan coba kembali.';
    
    // Save to database
    const userName = document.getElementById('user-name').value;
    const userEmail = document.getElementById('user-email').value;
    
    fetch('<?= BASE_URL ?>/api/quiz-submit.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            quiz_id: quizId,
            user_name: userName,
            user_email: userEmail,
            score: score,
            total_questions: questions.length,
            correct_answers: correct,
            time_taken: timeTaken,
            answers: userAnswers
        })
    });
}

function reviewAnswers() {
    document.getElementById('result-screen').style.display = 'none';
    document.getElementById('review-screen').style.display = 'block';
    
    let reviewHtml = '';
    questions.forEach((q, i) => {
        const isCorrect = userAnswers[i] === q.correct_answer;
        reviewHtml += `
            <div class="review-item ${isCorrect ? 'correct' : 'wrong'}">
                <div class="review-question"><strong>${i + 1}.</strong> ${q.question}</div>
                <div class="review-options">
                    ${['a', 'b', 'c', 'd'].map(opt => {
                        let classes = 'review-option';
                        if (opt === q.correct_answer) classes += ' correct-answer';
                        if (opt === userAnswers[i] && opt !== q.correct_answer) classes += ' user-wrong';
                        return `<div class="${classes}">${opt.toUpperCase()}. ${q['option_' + opt]}</div>`;
                    }).join('')}
                </div>
                ${q.explanation ? `<div class="review-explanation">💡 ${q.explanation}</div>` : ''}
            </div>
        `;
    });
    
    document.getElementById('review-container').innerHTML = reviewHtml;
}
</script>

<?php require_once '../includes/footer.php'; ?>

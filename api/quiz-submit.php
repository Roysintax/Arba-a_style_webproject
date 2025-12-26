<?php
/**
 * API Endpoint - Submit Quiz Result
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = db()->prepare("
        INSERT INTO quiz_attempts (quiz_id, user_name, user_email, score, total_questions, correct_answers, time_taken, answers)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['quiz_id'],
        $data['user_name'] ?? 'Anonymous',
        $data['user_email'] ?? '',
        $data['score'] ?? 0,
        $data['total_questions'] ?? 0,
        $data['correct_answers'] ?? 0,
        $data['time_taken'] ?? 0,
        json_encode($data['answers'] ?? [])
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Score saved']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error saving score']);
}

<?php
/**
 * Chat API - Send Message
 * Handles sending messages from both users and admin
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$senderType = $input['sender_type'] ?? 'user';
$chatId = $input['chat_id'] ?? null;
$sessionId = $input['session_id'] ?? session_id();
$userName = trim($input['user_name'] ?? 'Pengunjung');

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
    exit;
}

$pdo = db();

try {
    // If user is sending message
    if ($senderType === 'user') {
        // Find or create chat session
        $stmt = $pdo->prepare("SELECT id FROM chats WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $chat = $stmt->fetch();
        
        if (!$chat) {
            // Create new chat
            $stmt = $pdo->prepare("INSERT INTO chats (session_id, user_name) VALUES (?, ?)");
            $stmt->execute([$sessionId, $userName]);
            $chatId = $pdo->lastInsertId();
        } else {
            $chatId = $chat['id'];
        }
        
        // Insert message
        $stmt = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender_type, message) VALUES (?, 'user', ?)");
        $stmt->execute([$chatId, $message]);
        
        // Update chat
        $stmt = $pdo->prepare("UPDATE chats SET last_message = ?, unread_admin = unread_admin + 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$message, $chatId]);
        
    } else if ($senderType === 'admin' && $chatId) {
        // Admin sending message
        $stmt = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender_type, message) VALUES (?, 'admin', ?)");
        $stmt->execute([$chatId, $message]);
        
        // Update chat
        $stmt = $pdo->prepare("UPDATE chats SET last_message = ?, unread_user = unread_user + 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$message, $chatId]);
    }
    
    echo json_encode(['success' => true, 'chat_id' => $chatId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

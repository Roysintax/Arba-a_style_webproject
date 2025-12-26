<?php
/**
 * Chat API - Fetch Messages
 * Get messages for a chat session
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$sessionId = $_GET['session_id'] ?? session_id();
$chatId = $_GET['chat_id'] ?? null;
$lastId = (int)($_GET['last_id'] ?? 0);
$markRead = $_GET['mark_read'] ?? 'user'; // 'user' or 'admin'

$pdo = db();

try {
    // Get chat by session or chat_id
    if ($chatId) {
        $stmt = $pdo->prepare("SELECT * FROM chats WHERE id = ?");
        $stmt->execute([$chatId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM chats WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    $chat = $stmt->fetch();
    
    if (!$chat) {
        echo json_encode(['success' => true, 'chat' => null, 'messages' => []]);
        exit;
    }
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT * FROM chat_messages 
        WHERE chat_id = ? AND id > ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$chat['id'], $lastId]);
    $messages = $stmt->fetchAll();
    
    // Mark messages as read
    if ($markRead === 'user') {
        $pdo->prepare("UPDATE chats SET unread_user = 0 WHERE id = ?")->execute([$chat['id']]);
        $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_type = 'admin'")->execute([$chat['id']]);
    } else {
        $pdo->prepare("UPDATE chats SET unread_admin = 0 WHERE id = ?")->execute([$chat['id']]);
        $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_type = 'user'")->execute([$chat['id']]);
    }
    
    echo json_encode([
        'success' => true,
        'chat' => $chat,
        'messages' => $messages
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

<?php
/**
 * Admin - Chat Management
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle delete action BEFORE including header (to allow redirect)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        $deleteId = (int)$_GET['delete'];
        db()->prepare("DELETE FROM chats WHERE id = ?")->execute([$deleteId]);
        setFlash('success', 'Chat berhasil dihapus');
    }
    header('Location: chats.php');
    exit;
}

$pageTitle = 'Kelola Chat';
require_once 'includes/admin-header.php';

// Get all chats
$chats = db()->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM chat_messages WHERE chat_id = c.id) as total_messages
    FROM chats c 
    ORDER BY c.updated_at DESC
")->fetchAll();

// Get selected chat
$selectedChat = null;
$messages = [];
if (isset($_GET['id'])) {
    $chatId = (int)$_GET['id'];
    
    // Get chat details
    $stmt = db()->prepare("SELECT * FROM chats WHERE id = ?");
    $stmt->execute([$chatId]);
    $selectedChat = $stmt->fetch();
    
    if ($selectedChat) {
        // Get messages
        $stmt = db()->prepare("SELECT * FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC");
        $stmt->execute([$chatId]);
        $messages = $stmt->fetchAll();
        
        // Mark as read
        db()->prepare("UPDATE chats SET unread_admin = 0 WHERE id = ?")->execute([$chatId]);
        db()->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_type = 'user'")->execute([$chatId]);
    }
}

// Count unread
$totalUnread = db()->query("SELECT SUM(unread_admin) FROM chats")->fetchColumn();

// Get all products for product picker
$allProducts = db()->query("SELECT id, name, price, image, slug FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
?>

<div class="admin-content">
    <div class="content-header">
        <h1>💬 Kelola Chat</h1>
        <p>Kelola percakapan dengan pelanggan</p>
    </div>
    
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 20px; height: calc(100vh - 200px);">
        
        <!-- Chat List -->
        <div class="panel" style="margin: 0; display: flex; flex-direction: column;">
            <div class="panel-header">
                <h2>📋 Daftar Chat</h2>
                <?php if ($totalUnread > 0): ?>
                <span style="background: #E74C3C; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                    <?= $totalUnread ?> baru
                </span>
                <?php endif; ?>
            </div>
            <div class="panel-body" style="flex: 1; overflow-y: auto; padding: 0;">
                <?php if (empty($chats)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <span style="font-size: 3rem;">💬</span>
                    <p>Belum ada chat</p>
                </div>
                <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                <a href="?id=<?= $chat['id'] ?>" class="chat-item <?= $selectedChat && $selectedChat['id'] == $chat['id'] ? 'active' : '' ?>">
                    <div class="chat-avatar">
                        <?= strtoupper(substr($chat['user_name'], 0, 1)) ?>
                    </div>
                    <div class="chat-info">
                        <div class="chat-name">
                            <?= htmlspecialchars($chat['user_name']) ?>
                            <?php if ($chat['unread_admin'] > 0): ?>
                            <span class="unread-badge"><?= $chat['unread_admin'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-preview"><?= htmlspecialchars(truncateText($chat['last_message'] ?? '', 40)) ?></div>
                        <div class="chat-time"><?= formatDate($chat['updated_at']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chat Detail -->
        <div class="panel" style="margin: 0; display: flex; flex-direction: column;">
            <?php if ($selectedChat): ?>
            <div class="panel-header">
                <h2>
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        <span class="chat-avatar-sm"><?= strtoupper(substr($selectedChat['user_name'], 0, 1)) ?></span>
                        <?= htmlspecialchars($selectedChat['user_name']) ?>
                    </span>
                </h2>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="color: #666; font-size: 0.85rem;">
                        Session: <?= substr($selectedChat['session_id'], 0, 8) ?>...
                    </span>
                    <a href="?delete=<?= $selectedChat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus chat ini beserta semua pesannya?')">
                        🗑️ Hapus
                    </a>
                </div>
            </div>
            
            <div class="panel-body" style="flex: 1; overflow-y: auto; background: #f8f9fa;" id="admin-chat-messages">
                <?php foreach ($messages as $msg): ?>
                <div class="admin-chat-bubble <?= $msg['sender_type'] ?>">
                    <?= htmlspecialchars($msg['message']) ?>
                    <div class="bubble-time">
                        <?= date('H:i', strtotime($msg['created_at'])) ?>
                        <?= $msg['sender_type'] === 'admin' ? '✓' : '' ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="chat-reply-form">
                <button onclick="toggleProductPicker()" class="btn btn-warning" title="Kirim Produk" style="padding: 10px 15px;">🏷️</button>
                <input type="text" id="admin-reply-input" placeholder="Ketik balasan..." onkeypress="if(event.key==='Enter')sendAdminReply()">
                <button onclick="sendAdminReply()" class="btn btn-primary">Kirim ➤</button>
            </div>
            
            <!-- Product Picker Panel -->
            <div id="product-picker" style="display: none; position: absolute; bottom: 70px; left: 50%; transform: translateX(-50%); width: 280px; background: white; border-radius: 10px; box-shadow: 0 5px 30px rgba(0,0,0,0.2); max-height: 250px; overflow: hidden; z-index: 100;">
                <div style="padding: 10px 12px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
                    <strong style="font-size: 0.85rem;">🏷️ Pilih Produk</strong>
                    <button onclick="toggleProductPicker()" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #999;">×</button>
                </div>
                <div style="padding: 8px;">
                    <input type="text" id="product-search" placeholder="Cari..." onkeyup="filterProducts()" style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.8rem; margin-bottom: 8px;">
                    <div id="product-list" style="max-height: 160px; overflow-y: auto;">
                        <?php foreach ($allProducts as $prod): ?>
                        <div class="product-picker-item" onclick="sendProductCard(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>', '<?= $prod['slug'] ?>', <?= $prod['price'] ?>, '<?= $prod['image'] ?>')">
                            <img src="<?= getImageUrl($prod['image']) ?>" alt="" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 500; font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars(truncateText($prod['name'], 25)) ?></div>
                                <div style="color: #00695C; font-size: 0.7rem;"><?= formatRupiah($prod['price']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
                <div style="text-align: center;">
                    <span style="font-size: 4rem;">💬</span>
                    <p>Pilih chat untuk melihat percakapan</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.chat-item {
    display: flex;
    gap: 12px;
    padding: 15px;
    border-bottom: 1px solid #eee;
    text-decoration: none;
    color: inherit;
    transition: background 0.3s;
}

.chat-item:hover, .chat-item.active {
    background: #f0f9f7;
}

.chat-item.active {
    border-left: 3px solid var(--primary);
}

.chat-avatar, .chat-avatar-sm {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.chat-avatar-sm {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-name {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unread-badge {
    background: #E74C3C;
    color: white;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 10px;
}

.chat-preview {
    color: #666;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-time {
    color: #999;
    font-size: 0.75rem;
    margin-top: 3px;
}

.admin-chat-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 15px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    line-height: 1.4;
}

.admin-chat-bubble.user {
    background: white;
    border: 1px solid #ddd;
    border-bottom-left-radius: 5px;
    margin-right: auto;
}

.admin-chat-bubble.admin {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 5px;
}

.bubble-time {
    font-size: 0.7rem;
    margin-top: 5px;
    opacity: 0.7;
}

.chat-reply-form {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

.chat-reply-form input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
}

.chat-reply-form input:focus {
    border-color: var(--primary);
}

.product-picker-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.product-picker-item:hover {
    background: #f0f9f7;
}
</style>

<script>
const CHAT_ID = <?= $selectedChat ? $selectedChat['id'] : 'null' ?>;

async function sendAdminReply() {
    if (!CHAT_ID) return;
    
    const input = document.getElementById('admin-reply-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add to UI
    addAdminMessage(message);
    input.value = '';
    
    try {
        const response = await fetch('<?= BASE_URL ?>/api/chat-send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                sender_type: 'admin',
                chat_id: CHAT_ID
            })
        });
        
        const result = await response.json();
        if (!result.success) {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function addAdminMessage(message) {
    const container = document.getElementById('admin-chat-messages');
    const bubble = document.createElement('div');
    bubble.className = 'admin-chat-bubble admin';
    bubble.innerHTML = message + '<div class="bubble-time">' + new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) + ' ✓</div>';
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

// Auto refresh messages
<?php if ($selectedChat): ?>
let lastAdminMsgId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;

async function fetchNewMessages() {
    try {
        const response = await fetch('<?= BASE_URL ?>/api/chat-fetch.php?chat_id=<?= $selectedChat['id'] ?>&last_id=' + lastAdminMsgId + '&mark_read=admin');
        const result = await response.json();
        
        if (result.success && result.messages.length > 0) {
            result.messages.forEach(msg => {
                if (msg.id > lastAdminMsgId && msg.sender_type === 'user') {
                    const container = document.getElementById('admin-chat-messages');
                    const bubble = document.createElement('div');
                    bubble.className = 'admin-chat-bubble user';
                    bubble.innerHTML = msg.message + '<div class="bubble-time">' + new Date(msg.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) + '</div>';
                    container.appendChild(bubble);
                    container.scrollTop = container.scrollHeight;
                }
                lastAdminMsgId = msg.id;
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

setInterval(fetchNewMessages, 3000);

// Scroll to bottom on load
document.getElementById('admin-chat-messages').scrollTop = document.getElementById('admin-chat-messages').scrollHeight;
<?php endif; ?>

// Product picker functions
function toggleProductPicker() {
    const picker = document.getElementById('product-picker');
    picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
}

function filterProducts() {
    const search = document.getElementById('product-search').value.toLowerCase();
    const items = document.querySelectorAll('.product-picker-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(search) ? 'flex' : 'none';
    });
}

async function sendProductCard(id, name, slug, price, image) {
    if (!CHAT_ID) return;
    
    // Format product card message (special format with [PRODUCT] prefix)
    const productUrl = '<?= BASE_URL ?>/pages/shop/product-detail.php?slug=' + slug;
    const message = `[PRODUCT:${id}:${name}:${price}:${image}:${slug}]`;
    
    // Add visual card to admin UI
    const container = document.getElementById('admin-chat-messages');
    const bubble = document.createElement('div');
    bubble.className = 'admin-chat-bubble admin';
    bubble.innerHTML = `
        <div style="background: #f8f9fa; border-radius: 8px; padding: 10px; margin-bottom: 5px;">
            <div style="font-weight: 600; margin-bottom: 5px;">🏷️ ${name}</div>
            <div style="color: #00695C; font-weight: 600;">${formatRupiah(price)}</div>
        </div>
        <div class="bubble-time">${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})} ✓</div>
    `;
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
    
    // Send to API
    try {
        const response = await fetch('<?= BASE_URL ?>/api/chat-send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                sender_type: 'admin',
                chat_id: CHAT_ID
            })
        });
        
        const result = await response.json();
        if (!result.success) {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    toggleProductPicker();
}

function formatRupiah(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>

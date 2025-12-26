<?php
/**
 * Floating Chat Widget Component
 * Include this in footer.php to show chat widget on all pages
 */

// Determine user name for chat
$chatUserName = 'Anonymous';
$isUserLoggedIn = isLoggedIn() && isset($_SESSION['user_name']);
if ($isUserLoggedIn) {
    $chatUserName = $_SESSION['user_name'];
}

// Get featured products for quick selection
$chatProducts = db()->query("SELECT id, name, price, image FROM products WHERE is_active = 1 ORDER BY is_featured DESC, name ASC LIMIT 6")->fetchAll();
?>

<!-- Floating Track Order Button -->
<a href="<?= BASE_URL ?>/pages/track-order.php" id="track-order-btn" title="Lacak Pesanan">
    🚚
</a>

<!-- Chat Widget Button (Floating) -->
<div id="chat-widget">
    <button id="chat-toggle" onclick="toggleChat()">
        💬
        <span id="chat-badge" style="display: none;">0</span>
    </button>
    
    <!-- Chat Panel -->
    <div id="chat-panel" style="display: none;">
        <div class="chat-header">
            <span>💬 Chat dengan Kami</span>
            <button onclick="closeChat()" class="close-btn">×</button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div class="chat-welcome">
                <span style="font-size: 2rem;">👋</span>
                <p>Assalamu'alaikum! Ada yang bisa kami bantu?</p>
            </div>
            
            <!-- Quick Action Templates -->
            <div class="quick-actions" id="quick-actions">
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Pilih topik:</p>
                <button class="quick-btn" onclick="sendQuickMessage('Saya ingin bertanya tentang produk')">🛒 Tanya Produk</button>
                <button class="quick-btn" onclick="sendQuickMessage('Saya ingin cek status pesanan saya')">📦 Cek Pesanan</button>
                <button class="quick-btn" onclick="sendQuickMessage('Saya butuh bantuan')">❓ Bantuan</button>
                <button class="quick-btn" onclick="showProducts()">🏷️ Lihat Produk</button>
                <button class="quick-btn" onclick="showRating()">⭐ Beri Rating</button>
            </div>
            
            <!-- Products Panel (hidden by default) -->
            <div class="products-panel" id="products-panel" style="display: none;">
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Pilih produk:</p>
                <div class="product-grid">
                    <?php foreach ($chatProducts as $prod): ?>
                    <div class="product-item" onclick="selectProduct(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['name'])) ?>')">
                        <img src="<?= getImageUrl($prod['image']) ?>" alt="">
                        <div class="product-name"><?= htmlspecialchars(truncateText($prod['name'], 30)) ?></div>
                        <div class="product-price"><?= formatRupiah($prod['price']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="quick-btn" onclick="hideProducts()" style="width: 100%; margin-top: 10px;">← Kembali</button>
            </div>
            
            <!-- Rating Panel (hidden by default) -->
            <div class="rating-panel" id="rating-panel" style="display: none;">
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px; text-align: center;">Bagaimana pelayanan kami?</p>
                <div class="rating-stars">
                    <span class="star" onclick="submitRating(1)">⭐</span>
                    <span class="star" onclick="submitRating(2)">⭐</span>
                    <span class="star" onclick="submitRating(3)">⭐</span>
                    <span class="star" onclick="submitRating(4)">⭐</span>
                    <span class="star" onclick="submitRating(5)">⭐</span>
                </div>
                <div class="rating-labels">
                    <span>Buruk</span>
                    <span>Sangat Baik</span>
                </div>
                <button class="quick-btn" onclick="hideRating()" style="width: 100%; margin-top: 15px;">← Kembali</button>
            </div>
        </div>
        
        <div class="chat-input">
            <input type="text" id="chat-input-field" placeholder="Ketik pesan..." onkeypress="if(event.key==='Enter')sendMessage()">
            <button onclick="sendMessage()">➤</button>
        </div>
    </div>
</div>

<style>
/* Floating Track Order Button */
#track-order-btn {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FFC107, #FF9800);
    color: white;
    text-decoration: none;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(255, 152, 0, 0.4);
    transition: all 0.3s;
    z-index: 9998;
}

#track-order-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(255, 152, 0, 0.5);
}

#chat-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    font-family: 'Segoe UI', sans-serif;
}

#chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00695C, #004D40);
    color: white;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0, 105, 92, 0.4);
    transition: all 0.3s;
    position: relative;
}

#chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(0, 105, 92, 0.5);
}

#chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #E74C3C;
    color: white;
    font-size: 0.75rem;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#chat-panel {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 360px;
    height: 500px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #00695C, #004D40);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}

.chat-header .close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
}

.chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f8f9fa;
}

.chat-welcome {
    text-align: center;
    padding: 20px;
    color: #666;
}

.quick-actions {
    margin-top: 10px;
}

.quick-btn {
    display: inline-block;
    padding: 8px 12px;
    margin: 4px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s;
}

.quick-btn:hover {
    background: #00695C;
    color: white;
    border-color: #00695C;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.product-item {
    background: white;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.product-item:hover {
    border-color: #00695C;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 8px;
}

.product-name {
    font-size: 0.75rem;
    color: #333;
    margin-bottom: 5px;
    line-height: 1.2;
}

.product-price {
    font-size: 0.7rem;
    color: #00695C;
    font-weight: 600;
}

.rating-panel {
    text-align: center;
    padding: 20px;
}

.rating-stars {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.rating-stars .star {
    font-size: 2rem;
    cursor: pointer;
    transition: transform 0.2s;
    filter: grayscale(100%);
}

.rating-stars .star:hover {
    transform: scale(1.3);
    filter: grayscale(0%);
}

.rating-stars .star.active {
    filter: grayscale(0%);
}

.rating-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: #999;
    margin-top: 10px;
}

.chat-bubble {
    max-width: 80%;
    padding: 10px 15px;
    border-radius: 15px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    line-height: 1.4;
}

.chat-bubble.user {
    background: linear-gradient(135deg, #00695C, #004D40);
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 5px;
}

.chat-bubble.admin {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    border-bottom-left-radius: 5px;
}

.chat-time {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.7);
    margin-top: 5px;
}

.chat-bubble.admin .chat-time {
    color: #999;
}

.chat-input {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
    font-size: 0.9rem;
}

.chat-input input:focus {
    border-color: #00695C;
}

.chat-input button {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00695C, #004D40);
    color: white;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
}

.chat-input button:hover {
    transform: scale(1.05);
}

@media (max-width: 480px) {
    #chat-panel {
        width: calc(100vw - 30px);
        right: -15px;
    }
}
</style>

<script>
const CHAT_API_BASE = '<?= BASE_URL ?>/api';
const IS_USER_LOGGED_IN = <?= $isUserLoggedIn ? 'true' : 'false' ?>;
let chatSessionId = localStorage.getItem('chat_session_id') || '<?= session_id() ?>';
let lastMessageId = 0;
let chatOpen = false;
let hasStartedChat = false;

// Store session ID
if (!localStorage.getItem('chat_session_id')) {
    localStorage.setItem('chat_session_id', chatSessionId);
}

function toggleChat() {
    const panel = document.getElementById('chat-panel');
    chatOpen = !chatOpen;
    panel.style.display = chatOpen ? 'flex' : 'none';
    
    if (chatOpen) {
        fetchMessages();
        document.getElementById('chat-input-field').focus();
    }
}

function closeChat() {
    const panel = document.getElementById('chat-panel');
    chatOpen = false;
    panel.style.display = 'none';
    
    // Always reset chat for anonymous users when closing
    if (!IS_USER_LOGGED_IN) {
        resetChat();
    }
}

function resetChat() {
    // Generate new session ID
    chatSessionId = 'anon_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('chat_session_id', chatSessionId);
    lastMessageId = 0;
    hasStartedChat = false;
    
    // Reset UI
    document.getElementById('chat-messages').innerHTML = `
        <div class="chat-welcome">
            <span style="font-size: 2rem;">👋</span>
            <p>Assalamu'alaikum! Ada yang bisa kami bantu?</p>
        </div>
        <div class="quick-actions" id="quick-actions">
            <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Pilih topik:</p>
            <button class="quick-btn" onclick="sendQuickMessage('Saya ingin bertanya tentang produk')">🛒 Tanya Produk</button>
            <button class="quick-btn" onclick="sendQuickMessage('Saya ingin cek status pesanan saya')">📦 Cek Pesanan</button>
            <button class="quick-btn" onclick="sendQuickMessage('Saya butuh bantuan')">❓ Bantuan</button>
            <button class="quick-btn" onclick="showProducts()">🏷️ Lihat Produk</button>
            <button class="quick-btn" onclick="showRating()">⭐ Beri Rating</button>
        </div>
    `;
}

function sendQuickMessage(msg) {
    document.getElementById('chat-input-field').value = msg;
    sendMessage();
    hideQuickActions();
}

function hideQuickActions() {
    const qa = document.getElementById('quick-actions');
    if (qa) qa.style.display = 'none';
}

function showProducts() {
    document.getElementById('quick-actions').style.display = 'none';
    document.getElementById('products-panel').style.display = 'block';
    document.getElementById('rating-panel').style.display = 'none';
}

function hideProducts() {
    document.getElementById('quick-actions').style.display = 'block';
    document.getElementById('products-panel').style.display = 'none';
}

function selectProduct(id, name) {
    sendQuickMessage('Saya tertarik dengan produk: ' + name);
    hideProducts();
}

function showRating() {
    document.getElementById('quick-actions').style.display = 'none';
    document.getElementById('products-panel').style.display = 'none';
    document.getElementById('rating-panel').style.display = 'block';
}

function hideRating() {
    document.getElementById('quick-actions').style.display = 'block';
    document.getElementById('rating-panel').style.display = 'none';
}

function submitRating(stars) {
    const ratingText = ['', 'Sangat Buruk 😞', 'Buruk 😕', 'Cukup 😐', 'Baik 😊', 'Sangat Baik 😄'];
    sendQuickMessage('⭐ Rating: ' + stars + '/5 - ' + ratingText[stars]);
    hideRating();
}

async function sendMessage() {
    const input = document.getElementById('chat-input-field');
    const message = input.value.trim();
    
    if (!message) return;
    
    hasStartedChat = true;
    hideQuickActions();
    
    // Add message to UI immediately
    addMessageToUI(message, 'user');
    input.value = '';
    
    try {
        const response = await fetch(CHAT_API_BASE + '/chat-send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                sender_type: 'user',
                session_id: chatSessionId,
                user_name: '<?= htmlspecialchars($chatUserName) ?>'
            })
        });
        
        const result = await response.json();
        if (!result.success) {
            console.error('Chat error:', result.message);
        }
    } catch (error) {
        console.error('Chat error:', error);
    }
}

async function fetchMessages() {
    try {
        const response = await fetch(CHAT_API_BASE + '/chat-fetch.php?session_id=' + chatSessionId + '&last_id=' + lastMessageId + '&mark_read=user');
        const result = await response.json();
        
        if (result.success && result.messages.length > 0) {
            const container = document.getElementById('chat-messages');
            const welcome = container.querySelector('.chat-welcome');
            if (welcome) welcome.remove();
            hideQuickActions();
            
            result.messages.forEach(msg => {
                if (msg.id > lastMessageId) {
                    // Only add admin messages (user messages already added)
                    if (msg.sender_type === 'admin') {
                        addMessageToUI(msg.message, 'admin', msg.created_at);
                    }
                    lastMessageId = msg.id;
                }
            });
        }
        
        // Update unread badge
        if (result.chat && result.chat.unread_user > 0 && !chatOpen) {
            document.getElementById('chat-badge').style.display = 'flex';
            document.getElementById('chat-badge').textContent = result.chat.unread_user;
        } else {
            document.getElementById('chat-badge').style.display = 'none';
        }
        
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

function addMessageToUI(message, type, time = null) {
    const container = document.getElementById('chat-messages');
    const welcome = container.querySelector('.chat-welcome');
    if (welcome) welcome.remove();
    hideQuickActions();
    
    // Hide panels
    const pp = document.getElementById('products-panel');
    const rp = document.getElementById('rating-panel');
    if (pp) pp.style.display = 'none';
    if (rp) rp.style.display = 'none';
    
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + type;
    
    const timeStr = time ? new Date(time).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) : new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    
    // Check if message is a product card - allow empty image field with (.*)
    const productMatch = message.match(/\[PRODUCT:(\d+):([^:]+):(\d+):([^:]*):([^\]]+)\]/);
    if (productMatch && type === 'admin') {
        const [, productId, productName, productPrice, productImage, productSlug] = productMatch;
        const productUrl = '<?= BASE_URL ?>/pages/shop/product-detail.php?slug=' + productSlug;
        
        // Handle empty or missing image
        let imageUrl = '<?= BASE_URL ?>/assets/images/no-image.png';
        if (productImage && productImage.trim() !== '') {
            imageUrl = productImage.startsWith('http') || productImage.startsWith('/') ? productImage : '<?= UPLOAD_URL ?>' + productImage;
        }
        
        bubble.style.padding = '8px';
        bubble.style.cursor = 'pointer';
        bubble.onclick = function() { window.open(productUrl, '_blank'); };
        
        bubble.innerHTML = `
            <div style="background: white; border-radius: 8px; padding: 10px; display: flex; gap: 10px; align-items: center; border: 1px solid #eee; cursor: pointer;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #00695C, #004D40); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; flex-shrink: 0;">🏷️</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: #333; font-size: 0.8rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${productName}</div>
                    <div style="color: #00695C; font-weight: 600; font-size: 0.85rem;">Rp ${parseInt(productPrice).toLocaleString('id-ID')}</div>
                    <div style="color: #0066cc; font-size: 0.7rem; margin-top: 2px;">👆 Tap untuk lihat</div>
                </div>
            </div>
            <div class="chat-time">${timeStr}</div>
        `;
    } else {
        bubble.innerHTML = message + '<div class="chat-time">' + timeStr + '</div>';
    }
    
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

// Poll for new messages every 5 seconds
setInterval(fetchMessages, 5000);

// Initial fetch
fetchMessages();
</script>

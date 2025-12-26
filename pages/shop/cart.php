<?php
/**
 * Cart Page
 * Toko Islami - Online Shop & Artikel
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Handle cart actions BEFORE including header (to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
            addToCart($productId, $quantity);
            setFlash('success', 'Produk berhasil ditambahkan ke keranjang');
            header('Location: cart.php');
            exit;
            
        case 'update':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
            updateCartItem($productId, $quantity);
            setFlash('success', 'Keranjang berhasil diperbarui');
            header('Location: cart.php');
            exit;
            
        case 'remove':
            removeFromCart($productId);
            setFlash('success', 'Produk berhasil dihapus dari keranjang');
            header('Location: cart.php');
            exit;
            
        case 'clear':
            clearCart();
            setFlash('success', 'Keranjang berhasil dikosongkan');
            header('Location: cart.php');
            exit;
    }
}

// Now include header (after all redirects are done)
$pageTitle = 'Keranjang Belanja';
require_once '../../includes/header.php';

// Get cart items with product details
$cart = getCart();
$cartItems = [];
$subtotal = 0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $cart[$product['id']];
        $itemTotal = $product['price'] * $quantity;
        $subtotal += $itemTotal;
        
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'total' => $itemTotal
        ];
    }
}

$shippingCost = (float)getSetting('shipping_cost', 15000);
$total = $subtotal + $shippingCost;
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <span style="color: var(--gold);">Keranjang Belanja</span>
        </div>
        <h1 style="color: white;">🛒 Keranjang Belanja</h1>
        <p style="color: rgba(255,255,255,0.8);"><?= count($cartItems) ?> produk dalam keranjang</p>
    </div>
</section>

<!-- Cart Content -->
<section class="cart-page">
    <div class="container">
        <?php if (empty($cartItems)): ?>
        
        <!-- Empty Cart -->
        <div class="empty-state">
            <div style="font-size: 5rem; margin-bottom: 20px;">🛒</div>
            <h3>Keranjang Belanja Kosong</h3>
            <p>Anda belum menambahkan produk apapun ke keranjang.</p>
            <a href="products.php" class="btn btn-primary btn-lg">Mulai Belanja</a>
        </div>
        
        <?php else: ?>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 40px;">
            <!-- Cart Items -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Item dalam Keranjang</h3>
                    <form action="" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-sm" style="background: var(--danger); color: white;" onclick="return confirm('Kosongkan keranjang?')">
                            🗑️ Kosongkan
                        </button>
                    </form>
                </div>
                
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="<?= getImageUrl($item['product']['image']) ?>" alt="<?= htmlspecialchars($item['product']['name']) ?>">
                    </div>
                    
                    <div class="cart-item-details">
                        <h4 class="cart-item-title">
                            <a href="product-detail.php?slug=<?= $item['product']['slug'] ?>"><?= htmlspecialchars($item['product']['name']) ?></a>
                        </h4>
                        <div class="cart-item-price"><?= formatRupiah($item['product']['price']) ?></div>
                    </div>
                    
                    <form action="" method="POST" class="cart-item-quantity">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['product']['stock'] ?>" class="qty-input" data-product-id="<?= $item['product']['id'] ?>">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </form>
                    
                    <div style="text-align: right; min-width: 120px;">
                        <div style="font-weight: 700; color: var(--primary-dark); font-size: 1.1rem;">
                            <?= formatRupiah($item['total']) ?>
                        </div>
                    </div>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                        <button type="submit" class="cart-item-remove" title="Hapus" onclick="return confirm('Hapus produk ini?')">🗑️</button>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px;">
                    <a href="products.php" class="btn btn-secondary">← Lanjut Belanja</a>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div>
                <div class="cart-summary">
                    <h3>Ringkasan Belanja</h3>
                    
                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span><?= formatRupiah($subtotal) ?></span>
                    </div>
                    
                    <div class="cart-summary-row">
                        <span>Ongkos Kirim</span>
                        <span><?= formatRupiah($shippingCost) ?></span>
                    </div>
                    
                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span><?= formatRupiah($total) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">
                        Lanjut ke Pembayaran →
                    </a>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--cream); text-align: center;">
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
                            🔒 Transaksi Aman & Terpercaya
                        </p>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div style="background: var(--cream-dark); padding: 20px; border-radius: var(--radius-md); margin-top: 20px; border-left: 4px solid var(--gold);">
                    <h4 style="color: var(--primary-dark); margin-bottom: 10px;">💡 Tips Berbelanja</h4>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">
                        Niatkan berbelanja untuk kebutuhan halal dan hindari berlebihan. Semoga Allah memberkahi setiap transaksi kita.
                    </p>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</section>

<!-- Order History Section -->
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div class="section-title">
            <h2>📋 Riwayat Pesanan</h2>
            <p>Lacak status pesanan Anda</p>
        </div>
        
        <?php
        // Get user's order history
        $orderHistory = [];
        if (isLoggedIn() && isset($_SESSION['user_email'])) {
            $stmt = db()->prepare("
                SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                WHERE o.customer_email = ? 
                ORDER BY o.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$_SESSION['user_email']]);
            $orderHistory = $stmt->fetchAll();
        }
        
        $statusLabels = [
            'pending' => ['icon' => '⏳', 'label' => 'Menunggu', 'color' => '#FFA500'],
            'processing' => ['icon' => '⚙️', 'label' => 'Diproses', 'color' => '#3498DB'],
            'shipped' => ['icon' => '🚚', 'label' => 'Dikirim', 'color' => '#9B59B6'],
            'delivered' => ['icon' => '📦', 'label' => 'Sampai', 'color' => '#27AE60'],
            'completed' => ['icon' => '✅', 'label' => 'Selesai', 'color' => '#00695C'],
            'cancelled' => ['icon' => '❌', 'label' => 'Dibatalkan', 'color' => '#E74C3C']
        ];
        ?>
        
        <?php if (!isLoggedIn()): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
            <span style="font-size: 3rem;">🔐</span>
            <h3 style="color: var(--primary-dark); margin: 15px 0;">Login untuk Melihat Riwayat</h3>
            <p style="color: #666; margin-bottom: 20px;">Silakan login untuk melihat riwayat pesanan Anda</p>
            <a href="<?= BASE_URL ?>/pages/auth/login.php" class="btn btn-primary">Login Sekarang</a>
        </div>
        
        <?php elseif (empty($orderHistory)): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
            <span style="font-size: 3rem;">📭</span>
            <h3 style="color: var(--primary-dark); margin: 15px 0;">Belum Ada Pesanan</h3>
            <p style="color: #666;">Anda belum memiliki riwayat pesanan</p>
        </div>
        
        <?php else: ?>
        <div class="order-history-grid">
            <?php foreach ($orderHistory as $order): ?>
            <div class="order-history-card">
                <div class="order-history-header">
                    <div>
                        <div class="order-number"><?= $order['order_number'] ?></div>
                        <div class="order-date">📅 <?= formatDate($order['created_at']) ?></div>
                    </div>
                    <span class="order-status" style="background: <?= $statusLabels[$order['status']]['color'] ?>;">
                        <?= $statusLabels[$order['status']]['icon'] ?> <?= $statusLabels[$order['status']]['label'] ?>
                    </span>
                </div>
                
                <div class="order-history-body">
                    <div class="order-info-row">
                        <span>🛒 Jumlah Item</span>
                        <strong><?= $order['item_count'] ?> produk</strong>
                    </div>
                    <div class="order-info-row">
                        <span>💰 Total</span>
                        <strong style="color: var(--primary);"><?= formatRupiah($order['total_amount']) ?></strong>
                    </div>
                    <?php if ($order['courier_name'] && $order['tracking_number']): ?>
                    <div class="order-info-row">
                        <span>🚚 Resi</span>
                        <strong><?= strtoupper($order['courier_name']) ?>: <?= $order['tracking_number'] ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="order-history-footer">
                    <a href="<?= BASE_URL ?>/pages/track-order.php?order=<?= $order['order_number'] ?>" class="btn btn-sm btn-primary">
                        🔍 Lacak Pesanan
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.order-history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.order-history-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.order-history-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.order-history-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px 20px;
    background: linear-gradient(135deg, #f8f9fa, #fff);
    border-bottom: 1px solid #eee;
}

.order-number {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: 0.95rem;
}

.order-date {
    font-size: 0.8rem;
    color: #666;
    margin-top: 3px;
}

.order-status {
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.order-history-body {
    padding: 15px 20px;
}

.order-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 0.9rem;
}

.order-info-row:last-child {
    border-bottom: none;
}

.order-info-row span {
    color: #666;
}

.order-history-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    text-align: center;
}

@media (max-width: 768px) {
    .order-history-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>

<?php
/**
 * Checkout Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Checkout';
require_once '../../includes/header.php';

// Check if cart is empty
$cart = getCart();
if (empty($cart)) {
    setFlash('warning', 'Keranjang belanja Anda kosong');
    header('Location: cart.php');
    exit;
}

// Get cart items
$ids = array_keys($cart);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartItems = [];
$subtotal = 0;

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

$shippingCost = (float)getSetting('shipping_cost', 15000);
$total = $subtotal + $shippingCost;

// Handle checkout form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $payment = sanitize($_POST['payment'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validation
    if (empty($name)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($email)) $errors[] = 'Email harus diisi';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid';
    if (empty($phone)) $errors[] = 'Nomor telepon harus diisi';
    if (empty($address)) $errors[] = 'Alamat pengiriman harus diisi';
    if (empty($payment)) $errors[] = 'Metode pembayaran harus dipilih';
    
    if (empty($errors)) {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            
            // Generate order number
            $orderNumber = generateOrderNumber();
            
            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, customer_address, subtotal, shipping_cost, total_amount, payment_method, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$orderNumber, $name, $email, $phone, $address, $subtotal, $shippingCost, $total, $payment, $notes]);
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['product']['name'],
                    $item['quantity'],
                    $item['product']['price'],
                    $item['total']
                ]);
                
                // Update stock
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([
                    $item['quantity'],
                    $item['product']['id']
                ]);
            }
            
            $pdo->commit();
            
            // Store pending payment info
            $_SESSION['pending_payment'] = [
                'order_number' => $orderNumber,
                'total' => $total,
                'payment_method' => $payment
            ];
            
            // Redirect based on payment method
            if ($payment === 'transfer') {
                header('Location: payment-bank.php');
                exit;
            } elseif ($payment === 'ewallet') {
                header('Location: payment-ewallet.php');
                exit;
            } else {
                // COD - Clear cart and show success
                clearCart();
                $_SESSION['order_success'] = [
                    'order_number' => $orderNumber,
                    'total' => $total
                ];
                unset($_SESSION['pending_payment']);
                header('Location: checkout.php?success=1');
                exit;
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

// Check for success
$orderSuccess = isset($_GET['success']) && isset($_SESSION['order_success']) ? $_SESSION['order_success'] : null;
if ($orderSuccess) {
    unset($_SESSION['order_success']);
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <a href="<?= BASE_URL ?>/cart.php" style="color: rgba(255,255,255,0.7);">Keranjang</a>
            <span>›</span>
            <span style="color: var(--gold);">Checkout</span>
        </div>
        <h1>📝 Checkout</h1>
    </div>
</section>

<!-- Checkout Content -->
<section class="section">
    <div class="container">
        
        <?php if ($orderSuccess): ?>
        <!-- Order Success -->
        <div style="max-width: 600px; margin: 0 auto; text-align: center; background: white; padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            <div style="font-size: 5rem; margin-bottom: 20px;">✅</div>
            <h2 style="color: var(--success); margin-bottom: 15px;">Pesanan Berhasil!</h2>
            <p style="font-size: 1.1rem; margin-bottom: 20px;">Terima kasih atas pesanan Anda. JazakAllahu Khairan.</p>
            
            <div style="background: var(--cream); padding: 20px; border-radius: var(--radius-md); margin-bottom: 30px;">
                <p style="margin-bottom: 5px;">Nomor Pesanan:</p>
                <h3 style="color: var(--primary-dark);"><?= $orderSuccess['order_number'] ?></h3>
                <p style="font-size: 1.25rem; color: var(--primary); margin-top: 10px;">Total: <?= formatRupiah($orderSuccess['total']) ?></p>
            </div>
            
            <p style="color: var(--text-secondary); margin-bottom: 30px;">
                Kami akan segera memproses pesanan Anda. Detail pesanan telah dikirim ke email Anda.
            </p>
            
            <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">Kembali ke Beranda</a>
        </div>
        
        <?php else: ?>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 40px;">
                
                <!-- Checkout Form -->
                <div style="background: white; padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                    <h3 style="margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid var(--cream);">
                        📋 Informasi Pengiriman
                    </h3>
                    
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon *</label>
                            <input type="tel" name="phone" class="form-control" placeholder="08xxxxxxxxxx" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Lengkap *</label>
                        <textarea name="address" class="form-control" rows="4" placeholder="Masukkan alamat lengkap (jalan, RT/RW, kelurahan, kecamatan, kota, kode pos)" required><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan untuk pesanan"><?= isset($notes) ? htmlspecialchars($notes) : '' ?></textarea>
                    </div>
                    
                    <h3 style="margin: 30px 0 20px; padding-top: 20px; border-top: 2px solid var(--cream);">
                        💳 Metode Pembayaran
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #E0E0E0; border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment" value="transfer" style="margin-right: 15px;" required>
                            <span>🏦 Transfer Bank</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #E0E0E0; border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment" value="cod" style="margin-right: 15px;">
                            <span>🚚 COD (Bayar di Tempat)</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #E0E0E0; border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment" value="ewallet" style="margin-right: 15px;">
                            <span>📱 E-Wallet (GoPay, OVO, Dana)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div>
                    <div class="cart-summary">
                        <h3>📦 Ringkasan Pesanan</h3>
                        
                        <div style="max-height: 300px; overflow-y: auto; margin: 20px 0;">
                            <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; gap: 15px; padding: 10px 0; border-bottom: 1px solid var(--cream);">
                                <img src="<?= getImageUrl($item['product']['image']) ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($item['product']['name']) ?></div>
                                    <div style="color: var(--text-secondary); font-size: 0.85rem;">
                                        <?= $item['quantity'] ?> x <?= formatRupiah($item['product']['price']) ?>
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: var(--primary-dark);">
                                    <?= formatRupiah($item['total']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
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
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">
                            ✓ Buat Pesanan
                        </button>
                        
                        <p style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; margin-top: 15px;">
                            Dengan melakukan pemesanan, Anda menyetujui syarat dan ketentuan yang berlaku.
                        </p>
                    </div>
                    
                    <div style="background: var(--cream-dark); padding: 20px; border-radius: var(--radius-md); margin-top: 20px; text-align: center;">
                        <span style="font-size: 1.5rem; color: var(--gold);">۞</span>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 10px 0 0;">
                            "Barakallahu fiikum" - Semoga Allah memberkahi transaksi ini.
                        </p>
                    </div>
                </div>
            </div>
        </form>
        
        <?php endif; ?>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

<?php
/**
 * Product Detail Page
 * Toko Islami - Online Shop & Artikel
 */

require_once '../../includes/header.php';

// Get product by slug
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: products.php');
    exit;
}

$stmt = db()->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Produk tidak ditemukan');
    header('Location: products.php');
    exit;
}

$pageTitle = $product['name'];

// Get related products
$relatedStmt = db()->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
    ORDER BY RAND() 
    LIMIT 4
");
$relatedStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();
?>

<!-- Breadcrumb -->
<section style="background: var(--cream-dark); padding: 20px 0;">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Beranda</a>
            <span>›</span>
            <a href="<?= BASE_URL ?>/products.php">Produk</a>
            <span>›</span>
            <?php if ($product['category_slug']): ?>
            <a href="<?= BASE_URL ?>/products.php?category=<?= $product['category_slug'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
            <span>›</span>
            <?php endif; ?>
            <span style="color: var(--primary);"><?= htmlspecialchars($product['name']) ?></span>
        </div>
    </div>
</section>

<!-- Product Detail -->
<section class="product-detail">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <img src="<?= getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php if ($product['is_featured']): ?>
                <div style="position: absolute; top: 20px; left: 20px; background: var(--gold); color: var(--primary-dark); padding: 8px 20px; border-radius: var(--radius-xl); font-weight: 600;">
                    ⭐ Produk Unggulan
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <div class="category"><?= htmlspecialchars($product['category_name'] ?? 'Produk') ?></div>
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="price"><?= formatRupiah($product['price']) ?></div>
                
                <div class="stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                    <?php if ($product['stock'] > 0): ?>
                        ✓ Stok tersedia (<?= $product['stock'] ?> item)
                    <?php else: ?>
                        ✗ Stok habis
                    <?php endif; ?>
                </div>
                
                <div class="description">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="quantity-selector">
                        <label>Jumlah:</label>
                        <div class="cart-item-quantity">
                            <button type="button" class="qty-btn qty-minus">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                            <button type="button" class="qty-btn qty-plus">+</button>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" class="btn btn-primary btn-lg">🛒 Tambahkan ke Keranjang</button>
                    </div>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>Stok Habis</button>
                <?php endif; ?>
                
                <!-- Product Features -->
                <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid var(--cream);">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.5rem;">✓</span>
                            <span>100% Produk Halal</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.5rem;">📦</span>
                            <span>Pengiriman Aman</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.5rem;">💯</span>
                            <span>Kualitas Terjamin</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.5rem;">🔄</span>
                            <span>Garansi Pengembalian</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div class="section-title">
            <h2>Produk Terkait</h2>
            <p>Mungkin Anda juga tertarik</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
            <div class="card">
                <div class="card-image">
                    <img src="<?= getImageUrl($related['image']) ?>" alt="<?= htmlspecialchars($related['name']) ?>">
                </div>
                <div class="card-body">
                    <div class="card-category"><?= htmlspecialchars($related['category_name'] ?? 'Produk') ?></div>
                    <h3 class="card-title">
                        <a href="product-detail.php?slug=<?= $related['slug'] ?>"><?= htmlspecialchars($related['name']) ?></a>
                    </h3>
                    <div class="card-price"><?= formatRupiah($related['price']) ?></div>
                    <div class="card-actions">
                        <a href="product-detail.php?slug=<?= $related['slug'] ?>" class="btn btn-secondary btn-sm btn-block">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>

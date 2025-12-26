<?php
/**
 * Homepage
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Beranda';
require_once 'includes/header.php';

// Get featured products
$featuredProducts = db()->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 AND p.is_active = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
")->fetchAll();

// Get categories
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get latest articles
$latestArticles = db()->query("
    SELECT * FROM articles 
    WHERE is_published = 1 
    ORDER BY created_at DESC 
    LIMIT 3
")->fetchAll();

// Get hero settings
$heroTitle = getSiteSettingValue('hero_title', 'Selamat Datang di Toko Islami');
$heroSubtitle = getSiteSettingValue('hero_subtitle', 'Temukan berbagai produk Islami berkualitas untuk kebutuhan ibadah dan sehari-hari Anda. Belanja mudah, berkah berlimpah.');
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="ornament">۞</span>
            <h1><?= html_entity_decode($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= html_entity_decode($heroSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="pages/shop/products.php" class="btn btn-primary btn-lg">🛒 Belanja Sekarang</a>
                <a href="pages/articles/" class="btn btn-outline btn-lg">📖 Baca Artikel</a>
            </div>
        </div>
        <div class="hero-image">
            <div style="width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="font-size: 8rem;">🕌</span>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section categories-section">
    <div class="container">
        <div class="section-title">
            <h2>Kategori Produk</h2>
            <p>Temukan produk sesuai kebutuhan Anda</p>
        </div>
        
        <div class="categories-grid">
            <?php 
            $icons = ['👔', '👗', '🕌', '📖', '📿', '🥗'];
            foreach ($categories as $index => $category): 
            ?>
            <a href="pages/shop/products.php?category=<?= $category['slug'] ?>" class="category-card">
                <div style="font-size: 2.5rem; margin-bottom: 10px;"><?= $icons[$index % count($icons)] ?></div>
                <h4><?= htmlspecialchars($category['name']) ?></h4>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Produk Unggulan</h2>
            <p>Pilihan terbaik untuk Anda</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="card">
                <div class="card-image">
                    <img src="<?= getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <span class="card-badge">Unggulan</span>
                </div>
                <div class="card-body">
                    <div class="card-category"><?= htmlspecialchars($product['category_name'] ?? 'Produk') ?></div>
                    <h3 class="card-title">
                        <a href="pages/shop/product-detail.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                    </h3>
                    <div class="card-price"><?= formatRupiah($product['price']) ?></div>
                    <div class="card-actions">
                        <form action="pages/shop/cart.php" method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm btn-block">🛒 Tambah</button>
                        </form>
                        <a href="pages/shop/product-detail.php?slug=<?= $product['slug'] ?>" class="btn btn-secondary btn-sm">Detail</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="pages/shop/products.php" class="btn btn-primary btn-lg" style="color: white;">Lihat Semua Produk →</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white;">
    <div class="container">
        <div class="section-title">
            <h2 style="color: white;">Mengapa Memilih Kami?</h2>
            <p style="color: rgba(255,255,255,0.8);">Komitmen kami untuk memberikan yang terbaik</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 3rem; margin-bottom: 15px;">✓</div>
                <h4 style="color: var(--gold); margin-bottom: 10px;">100% Produk Halal</h4>
                <p style="color: rgba(255,255,255,0.8);">Semua produk telah tersertifikasi halal dan aman digunakan</p>
            </div>
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 3rem; margin-bottom: 15px;">📦</div>
                <h4 style="color: var(--gold); margin-bottom: 10px;">Pengiriman Cepat</h4>
                <p style="color: rgba(255,255,255,0.8);">Pesanan diproses dengan cepat dan dikirim ke seluruh Indonesia</p>
            </div>
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 3rem; margin-bottom: 15px;">💯</div>
                <h4 style="color: var(--gold); margin-bottom: 10px;">Kualitas Terjamin</h4>
                <p style="color: rgba(255,255,255,0.8);">Produk berkualitas tinggi dengan harga yang terjangkau</p>
            </div>
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 3rem; margin-bottom: 15px;">🤝</div>
                <h4 style="color: var(--gold); margin-bottom: 10px;">Pelayanan Amanah</h4>
                <p style="color: rgba(255,255,255,0.8);">Kami melayani dengan jujur dan amanah sesuai syariat</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest Articles -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Artikel Terbaru</h2>
            <p>Inspirasi dan pengetahuan Islami</p>
        </div>
        
        <div class="articles-grid">
            <?php foreach ($latestArticles as $article): ?>
            <div class="card">
                <div class="card-image" style="aspect-ratio: 16/9;">
                    <img src="<?= getImageUrl($article['image'], 'assets/images/article-default.png') ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
                <div class="card-body">
                    <div class="card-meta">
                        <span>📅 <?= formatDate($article['created_at']) ?></span>
                        <span>👤 <?= htmlspecialchars($article['author']) ?></span>
                    </div>
                    <h3 class="card-title">
                        <a href="pages/articles/detail.php?slug=<?= $article['slug'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">
                        <?= truncateText(strip_tags($article['excerpt'] ?? $article['content']), 120) ?>
                    </p>
                    <a href="pages/articles/detail.php?slug=<?= $article['slug'] ?>" class="btn btn-secondary btn-sm" style="margin-top: 10px;">Baca Selengkapnya →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="pages/articles/" class="btn btn-primary btn-lg" style="color: white;">Lihat Semua Artikel →</a>
        </div>
    </div>
</section>

<!-- Newsletter / CTA -->
<section class="section islamic-pattern" style="background-color: var(--cream-dark);">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <span style="font-size: 3rem; color: var(--gold);">۞</span>
            <h2>Mari Berbelanja dengan Berkah</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">
                Dengan niat yang baik dan produk yang halal, semoga setiap transaksi membawa keberkahan bagi kita semua.
            </p>
            <a href="pages/shop/products.php" class="btn btn-primary btn-lg" style="color: white;">Mulai Berbelanja</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

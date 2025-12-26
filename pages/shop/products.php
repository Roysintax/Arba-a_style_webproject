<?php
/**
 * Products Listing Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Produk';
require_once '../../includes/header.php';

// Get filter parameters
$categorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;

// Build query
$where = ['p.is_active = 1'];
$params = [];

if ($categorySlug) {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($search) {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Get sort order
switch ($sort) {
    case 'price_low':
        $orderBy = 'p.price ASC';
        break;
    case 'price_high':
        $orderBy = 'p.price DESC';
        break;
    case 'name':
        $orderBy = 'p.name ASC';
        break;
    default:
        $orderBy = 'p.created_at DESC';
}

// Count total products
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause";
$stmt = db()->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Pagination
$pagination = paginate($totalProducts, $page, $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereClause 
        ORDER BY $orderBy 
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get current category name
$currentCategory = null;
if ($categorySlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $currentCategory = $cat;
            break;
        }
    }
}
?>

<!-- Page Header -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 40px 0;">
    <div class="container">
        <div class="breadcrumb" style="color: rgba(255,255,255,0.7);">
            <a href="<?= BASE_URL ?>" style="color: rgba(255,255,255,0.7);">Beranda</a>
            <span>›</span>
            <span style="color: var(--gold);">Produk</span>
            <?php if ($currentCategory): ?>
            <span>›</span>
            <span style="color: var(--gold);"><?= htmlspecialchars($currentCategory['name']) ?></span>
            <?php endif; ?>
        </div>
        <h1 style="color: white;">
            <?php if ($currentCategory): ?>
                <?= htmlspecialchars($currentCategory['name']) ?>
            <?php elseif ($search): ?>
                Hasil Pencarian: "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                Semua Produk
            <?php endif; ?>
        </h1>
        <p style="color: rgba(255,255,255,0.8);">Ditemukan <?= $totalProducts ?> produk</p>
    </div>
</section>

<!-- Products Section -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 40px;">
            
            <!-- Sidebar -->
            <aside>
                <!-- Search -->
                <div style="background: white; padding: 20px; border-radius: var(--radius-md); margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                    <h4 style="margin-bottom: 15px;">🔍 Cari Produk</h4>
                    <form action="" method="GET">
                        <input type="text" name="search" class="form-control" placeholder="Nama produk..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Cari</button>
                    </form>
                </div>
                
                <!-- Categories -->
                <div style="background: white; padding: 20px; border-radius: var(--radius-md); margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                    <h4 style="margin-bottom: 15px;">📂 Kategori</h4>
                    <ul style="display: flex; flex-direction: column; gap: 8px;">
                        <li>
                            <a href="products.php" style="display: block; padding: 10px; border-radius: var(--radius-sm); <?= !$categorySlug ? 'background: var(--primary); color: white;' : 'background: var(--cream);' ?>">
                                Semua Kategori
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="products.php?category=<?= $cat['slug'] ?>" style="display: block; padding: 10px; border-radius: var(--radius-sm); <?= $categorySlug === $cat['slug'] ? 'background: var(--primary); color: white;' : 'background: var(--cream);' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Sort -->
                <div style="background: white; padding: 20px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                    <h4 style="margin-bottom: 15px;">⇅ Urutkan</h4>
                    <form action="" method="GET">
                        <?php if ($categorySlug): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>">
                        <?php endif; ?>
                        <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                        <select name="sort" class="form-control" onchange="this.form.submit()">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A-Z</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                        </select>
                    </form>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div>
                <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div style="font-size: 4rem;">📦</div>
                    <h3>Produk Tidak Ditemukan</h3>
                    <p>Maaf, tidak ada produk yang sesuai dengan pencarian Anda.</p>
                    <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
                </div>
                <?php else: ?>
                
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="card">
                        <div class="card-image">
                            <img src="<?= getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php if ($product['is_featured']): ?>
                            <span class="card-badge">Unggulan</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-category"><?= htmlspecialchars($product['category_name'] ?? 'Produk') ?></div>
                            <h3 class="card-title">
                                <a href="product-detail.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                            </h3>
                            <div class="card-price"><?= formatRupiah($product['price']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 5px;">
                                <?= $product['stock'] > 0 ? "Stok: {$product['stock']}" : '<span style="color:var(--danger)">Habis</span>' ?>
                            </div>
                            <div class="card-actions">
                                <?php if ($product['stock'] > 0): ?>
                                <form action="cart.php" method="POST" style="flex: 1;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">🛒 Tambah</button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-secondary btn-sm btn-block" disabled>Stok Habis</button>
                                <?php endif; ?>
                                <a href="product-detail.php?slug=<?= $product['slug'] ?>" class="btn btn-secondary btn-sm">Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?= renderPagination($pagination, "products.php" . ($categorySlug ? "?category=$categorySlug" : "") . ($search ? ($categorySlug ? "&" : "?") . "search=$search" : "")) ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

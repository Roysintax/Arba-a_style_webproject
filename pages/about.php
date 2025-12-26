<?php
/**
 * About Page - Tentang Kami
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Tentang Kami';
require_once '../includes/header.php';

// Get about page content
$about = getAboutPage();
?>

<!-- Hero Section -->
<section style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 60px 0; text-align: center;">
    <div class="container">
        <span style="font-size: 3rem; color: var(--gold);">۞</span>
        <h1 style="margin: 20px 0 10px; color: white;"><?= htmlspecialchars($about['title'] ?? 'Tentang Kami') ?></h1>
        <p style="color: rgba(255,255,255,0.8); font-size: 1.2rem;"><?= htmlspecialchars($about['subtitle'] ?? 'Mengenal Lebih Dekat') ?></p>
    </div>
</section>

<!-- Main Content -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
            
            <!-- Photo Section -->
            <div style="text-align: center;">
                <div style="position: relative; display: inline-block;">
                    <?php if (!empty($about['photo'])): ?>
                    <img src="<?= UPLOAD_URL . $about['photo'] ?>" alt="<?= htmlspecialchars($about['title'] ?? 'Tentang Kami') ?>" 
                         style="width: 100%; max-width: 400px; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
                    <?php else: ?>
                    <div style="width: 100%; max-width: 400px; aspect-ratio: 1; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <span style="font-size: 8rem; color: white; opacity: 0.8;">🕌</span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Decorative elements -->
                    <div style="position: absolute; top: -15px; right: -15px; width: 60px; height: 60px; background: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                        ۞
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div>
                <h2 style="color: var(--primary-dark); margin-bottom: 25px; font-size: 2rem;">
                    Selamat Datang di <?= getSetting('site_name', 'Toko Islami') ?>
                </h2>
                
                <div style="color: var(--text-secondary); line-height: 1.8; font-size: 1.05rem;">
                    <?= $about['content'] ?? '<p>Informasi tentang kami akan segera hadir.</p>' ?>
                </div>
                
                <!-- Stats -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px;">
                    <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: var(--radius-md);">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-dark);">100+</div>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">Produk Halal</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: var(--radius-md);">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-dark);">1000+</div>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">Pelanggan</div>
                    </div>
                    <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: var(--radius-md);">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-dark);">5+</div>
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">Tahun Pengalaman</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="section" style="background: var(--cream-dark);">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            
            <!-- Vision -->
            <div style="background: white; padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 1.5rem; color: white;">👁️</span>
                    </div>
                    <h3 style="margin: 0; color: var(--primary-dark);">Visi Kami</h3>
                </div>
                <p style="color: var(--text-secondary); line-height: 1.8; margin: 0;">
                    <?= htmlspecialchars($about['vision'] ?? 'Menjadi platform belanja online Islami terpercaya yang membawa keberkahan bagi seluruh umat.') ?>
                </p>
            </div>
            
            <!-- Mission -->
            <div style="background: white; padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--gold), #c9a227); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 1.5rem; color: white;">🎯</span>
                    </div>
                    <h3 style="margin: 0; color: var(--primary-dark);">Misi Kami</h3>
                </div>
                <p style="color: var(--text-secondary); line-height: 1.8; margin: 0;">
                    <?= htmlspecialchars($about['mission'] ?? 'Menyediakan produk-produk Islami berkualitas dengan harga terjangkau. Melayani dengan amanah dan kejujuran.') ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Nilai-Nilai Kami</h2>
            <p>Prinsip yang kami pegang teguh</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px;">
            <div style="text-align: center; padding: 30px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); transition: transform 0.3s;">
                <div style="font-size: 3rem; margin-bottom: 15px;">🤲</div>
                <h4 style="color: var(--primary-dark); margin-bottom: 10px;">Amanah</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Menjaga kepercayaan pelanggan dengan kejujuran</p>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); transition: transform 0.3s;">
                <div style="font-size: 3rem; margin-bottom: 15px;">✅</div>
                <h4 style="color: var(--primary-dark); margin-bottom: 10px;">Halal</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Semua produk terjamin kehalalannya</p>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); transition: transform 0.3s;">
                <div style="font-size: 3rem; margin-bottom: 15px;">💎</div>
                <h4 style="color: var(--primary-dark); margin-bottom: 10px;">Berkualitas</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Seleksi ketat untuk produk terbaik</p>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); transition: transform 0.3s;">
                <div style="font-size: 3rem; margin-bottom: 15px;">❤️</div>
                <h4 style="color: var(--primary-dark); margin-bottom: 10px;">Pelayanan</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Melayani dengan sepenuh hati</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section islamic-pattern" style="background-color: var(--primary); color: white; text-align: center;">
    <div class="container">
        <span style="font-size: 3rem; color: var(--gold);">۞</span>
        <h2 style="color: white; margin: 20px 0;">Mari Berbelanja dengan Berkah</h2>
        <p style="color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto 30px;">
            Dengan niat yang baik dan produk yang halal, semoga setiap transaksi membawa keberkahan bagi kita semua.
        </p>
        <a href="<?= BASE_URL ?>/pages/shop/products.php" class="btn btn-lg" style="background: var(--gold); color: var(--primary-dark);">
            🛒 Mulai Berbelanja
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

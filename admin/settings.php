<?php
/**
 * Admin - Site Settings
 * Pengaturan Logo, Navbar, Header, Hero
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check admin auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle save settings BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Navbar Title
    $navbarLarge = trim($_POST['navbar_title_large']);
    $navbarSmall = trim($_POST['navbar_title_small']);
    $footerText = trim($_POST['footer_text']);
    // Hero text - allow special characters like apostrophe
    $heroTitle = trim($_POST['hero_title']);
    $heroSubtitle = trim($_POST['hero_subtitle']);
    
    // Update text settings
    $updates = [
        'navbar_title_large' => $navbarLarge,
        'navbar_title_small' => $navbarSmall,
        'footer_text' => $footerText,
        'hero_title' => $heroTitle,
        'hero_subtitle' => $heroSubtitle
    ];
    
    foreach ($updates as $key => $value) {
        $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    
    // Handle logo upload
    if (isset($_FILES['navbar_logo']) && $_FILES['navbar_logo']['error'] === UPLOAD_ERR_OK) {
        $logoName = uploadImage($_FILES['navbar_logo'], 'logo');
        if ($logoName) {
            $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('navbar_logo', ?, 'image') 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$logoName, $logoName]);
        }
    }
    
    // Handle header banner upload
    if (isset($_FILES['header_banner']) && $_FILES['header_banner']['error'] === UPLOAD_ERR_OK) {
        $bannerName = uploadImage($_FILES['header_banner'], 'banner');
        if ($bannerName) {
            $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('header_banner', ?, 'image') 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$bannerName, $bannerName]);
        }
    }
    
    setFlash('success', 'Pengaturan berhasil disimpan');
    header('Location: settings.php');
    exit;
}

// Get current settings
function getSiteSetting($key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        try {
            $stmt = db()->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

$navbarLogo = getSiteSetting('navbar_logo', '');
$navbarLarge = getSiteSetting('navbar_title_large', 'Toko');
$navbarSmall = getSiteSetting('navbar_title_small', 'Islami');
$headerBanner = getSiteSetting('header_banner', '');
$footerText = getSiteSetting('footer_text', 'Toko Islami - Belanja Berkah, Hidup Barokah');
$heroTitle = getSiteSetting('hero_title', 'Selamat Datang di Toko Islami');
$heroSubtitle = getSiteSetting('hero_subtitle', 'Temukan berbagai produk Islami berkualitas untuk kebutuhan ibadah dan sehari-hari Anda. Belanja mudah, berkah berlimpah.');

// Now include admin header (after all POST processing)
$pageTitle = 'Pengaturan Website';
require_once 'includes/admin-header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>⚙️ Pengaturan Website</h2>
    </div>
    <div class="panel-body">
        <form action="" method="POST" enctype="multipart/form-data">
            
            <!-- Logo Navbar -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: var(--primary-dark);">🖼️ Logo Navbar</h3>
                
                <div class="form-group">
                    <label>Upload Logo (PNG/JPG, maks 2MB)</label>
                    <input type="file" name="navbar_logo" class="form-control" accept="image/*">
                    <?php if ($navbarLogo): ?>
                    <div style="margin-top: 10px;">
                        <p style="margin-bottom: 5px; color: #666;">Logo saat ini:</p>
                        <img src="<?= UPLOAD_URL . $navbarLogo ?>" alt="Logo" style="max-height: 60px; border-radius: 5px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div class="form-group">
                        <label>Teks Logo (Besar)</label>
                        <input type="text" name="navbar_title_large" class="form-control" 
                               value="<?= htmlspecialchars(html_entity_decode($navbarLarge, ENT_QUOTES, 'UTF-8')) ?>" placeholder="Contoh: Toko">
                        <small style="color: #666;">Teks yang tampil besar di navbar</small>
                    </div>
                    <div class="form-group">
                        <label>Teks Logo (Kecil)</label>
                        <input type="text" name="navbar_title_small" class="form-control" 
                               value="<?= htmlspecialchars(html_entity_decode($navbarSmall, ENT_QUOTES, 'UTF-8')) ?>" placeholder="Contoh: Islami">
                        <small style="color: #666;">Teks yang tampil kecil di bawah</small>
                    </div>
                </div>
                
                <!-- Preview -->
                <div style="margin-top: 15px; padding: 15px; background: var(--primary-dark); border-radius: 8px; display: inline-block;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php if ($navbarLogo): ?>
                        <img src="<?= UPLOAD_URL . $navbarLogo ?>" alt="" style="height: 40px;">
                        <?php else: ?>
                        <div style="font-size: 1.5rem;">🕌</div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight: 700; color: white; font-size: 1.2rem;" id="preview-large"><?= html_entity_decode($navbarLarge, ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="font-size: 0.7rem; color: #D4AF37;" id="preview-small"><?= html_entity_decode($navbarSmall, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Header Banner -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: var(--primary-dark);">🎨 Banner Header</h3>
                
                <div class="form-group">
                    <label>Upload Banner Header (PNG/JPG, maks 5MB)</label>
                    <input type="file" name="header_banner" class="form-control" accept="image/*">
                    <?php if ($headerBanner): ?>
                    <div style="margin-top: 10px;">
                        <p style="margin-bottom: 5px; color: #666;">Banner saat ini:</p>
                        <img src="<?= UPLOAD_URL . $headerBanner ?>" alt="Banner" style="max-width: 100%; max-height: 150px; border-radius: 5px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Hero Section (Beranda) -->
            <div style="background: #e8f4fd; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid var(--primary);">
                <h3 style="margin-bottom: 15px; color: var(--primary-dark);">🏠 Hero Section (Beranda)</h3>
                <p style="color: #666; margin-bottom: 15px; font-size: 0.9rem;">Teks yang tampil di bagian atas halaman beranda</p>
                
                <div class="form-group">
                    <label>Judul Hero</label>
                    <input type="text" name="hero_title" class="form-control" 
                           value="<?= htmlspecialchars(html_entity_decode($heroTitle, ENT_QUOTES, 'UTF-8')) ?>" 
                           placeholder="Contoh: Selamat Datang di Toko Islami">
                    <small style="color: #666;">Teks utama yang tampil besar di hero section</small>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Hero</label>
                    <textarea name="hero_subtitle" class="form-control" rows="3" 
                              placeholder="Deskripsi singkat tentang toko Anda"><?= htmlspecialchars(html_entity_decode($heroSubtitle, ENT_QUOTES, 'UTF-8')) ?></textarea>
                    <small style="color: #666;">Teks pendukung di bawah judul utama</small>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: var(--primary-dark);">📝 Footer</h3>
                
                <div class="form-group">
                    <label>Teks Footer</label>
                    <input type="text" name="footer_text" class="form-control" 
                           value="<?= htmlspecialchars($footerText) ?>" placeholder="Teks yang tampil di footer">
                </div>
            </div>
            
            <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                💾 Simpan Pengaturan
            </button>
        </form>
    </div>
</div>

<script>
// Live preview for navbar text
document.querySelector('input[name="navbar_title_large"]').addEventListener('input', function() {
    document.getElementById('preview-large').textContent = this.value || 'Toko';
});
document.querySelector('input[name="navbar_title_small"]').addEventListener('input', function() {
    document.getElementById('preview-small').textContent = this.value || 'Islami';
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>

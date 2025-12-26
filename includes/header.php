<?php
/**
 * Header Component
 * Toko Islami - Online Shop & Artikel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$siteName = getSetting('site_name', 'Toko Islami');
$siteTagline = getSetting('site_tagline', 'Belanja Berkah, Hidup Barokah');
$sitePhone = getSetting('site_phone', '08123456789');
$siteEmail = getSetting('site_email', 'info@tokoislami.com');

// Get site settings for logo/navbar
function getSiteSettingValue($key, $default = '') {
    static $settingsCache = null;
    if ($settingsCache === null) {
        try {
            $stmt = db()->query("SELECT setting_key, setting_value FROM site_settings");
            $settingsCache = [];
            while ($row = $stmt->fetch()) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}

$navbarLogo = getSiteSettingValue('navbar_logo', '');
$navbarTitleLarge = getSiteSettingValue('navbar_title_large', 'Toko');
$navbarTitleSmall = getSiteSettingValue('navbar_title_small', 'Islami');

// Current page for active nav
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $siteTagline ?>">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container">
                <div class="header-top-left">
                    <span>📞 <?= $sitePhone ?></span>
                    <span style="margin-left: 20px;">✉️ <?= $siteEmail ?></span>
                </div>
                <div class="header-top-right">
                    <span style="color: var(--gold);">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>" class="logo">
                    <?php if ($navbarLogo): ?>
                    <img src="<?= UPLOAD_URL . $navbarLogo ?>" alt="Logo" style="height: 45px;">
                    <?php else: ?>
                    <div class="logo-icon">🕌</div>
                    <?php endif; ?>
                    <div class="logo-text">
                        <h1><?= html_entity_decode($navbarTitleLarge, ENT_QUOTES, 'UTF-8') ?></h1>
                        <span><?= html_entity_decode($navbarTitleSmall, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </a>
                
                <!-- Navigation -->
                <nav class="nav-menu">
                    <a href="<?= BASE_URL ?>" class="<?= $currentPage == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/pages') === false ? 'active' : '' ?>">Beranda</a>
                    <a href="<?= BASE_URL ?>/pages/shop/products.php" class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">Produk</a>
                    <a href="<?= BASE_URL ?>/pages/articles/" class="<?= $currentPage == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/articles') !== false ? 'active' : '' ?>">Artikel</a>
                    <a href="<?= BASE_URL ?>/pages/aktivitas.php" class="<?= $currentPage == 'aktivitas.php' ? 'active' : '' ?>">Aktivitas</a>
                    <a href="<?= BASE_URL ?>/pages/quiz.php" class="<?= $currentPage == 'quiz.php' || $currentPage == 'quiz-play.php' ? 'active' : '' ?>">Kuis</a>
                    <a href="<?= BASE_URL ?>/pages/about.php" class="<?= $currentPage == 'about.php' ? 'active' : '' ?>">Tentang Kami</a>
                    <a href="<?= BASE_URL ?>/pages/shop/cart.php" class="<?= $currentPage == 'cart.php' ? 'active' : '' ?>">Keranjang</a>
                </nav>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <a href="<?= BASE_URL ?>/pages/shop/cart.php" class="cart-icon">
                        🛒
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                    <!-- User Menu -->
                    <div class="user-dropdown" style="position: relative;">
                        <a href="#" style="color: white; display: flex; align-items: center; gap: 8px;" onclick="document.getElementById('userMenu').classList.toggle('show'); return false;">
                            👤 <?= htmlspecialchars($_SESSION['user_name']) ?> ▾
                        </a>
                        <div id="userMenu" class="dropdown-menu" style="position: absolute; right: 0; top: 100%; background: white; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); min-width: 180px; display: none; z-index: 1000;">
                            <a href="<?= BASE_URL ?>/pages/account.php" style="display: block; padding: 12px 20px; color: var(--text-primary);">📋 Akun Saya</a>
                            <?php if (isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/admin/" style="display: block; padding: 12px 20px; color: var(--text-primary);">⚙️ Admin Panel</a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/pages/auth/logout.php" style="display: block; padding: 12px 20px; color: var(--danger); border-top: 1px solid #eee;">🚪 Keluar</a>
                        </div>
                    </div>
                    <style>
                        .dropdown-menu.show { display: block !important; }
                        .dropdown-menu a:hover { background: var(--cream); }
                    </style>
                    <?php else: ?>
                    <!-- Login/Register -->
                    <a href="<?= BASE_URL ?>/pages/auth/login.php" style="color: white; padding: 8px 15px;">Masuk</a>
                    <a href="<?= BASE_URL ?>/pages/auth/register.php" class="btn btn-primary btn-sm">Daftar</a>
                    <?php endif; ?>
                    
                    <button class="mobile-menu-btn">☰</button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <div class="container" style="margin-top: 20px;">
        <?php displayFlash(); ?>
    </div>
    
    <!-- Main Content -->
    <main>

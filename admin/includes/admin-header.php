<?php
/**
 * Admin Header Component
 * Toko Islami - Admin Panel
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check admin login
requireAdmin();

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$siteName = getSetting('site_name', 'Toko Islami');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Panel' ?> | <?= $siteName ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #00695C;
            --primary-dark: #004D40;
            --gold: #D4AF37;
            --sidebar-width: 260px;
            --header-height: 60px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: #F5F6FA;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark), var(--primary));
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            color: var(--gold);
            font-size: 1.3rem;
        }
        
        .sidebar-header small {
            color: rgba(255,255,255,0.7);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            padding: 10px 20px;
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--gold);
        }
        
        .sidebar-menu a span.icon {
            font-size: 1.2rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-header h1 {
            font-size: 1.3rem;
            color: #333;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-menu .user-name {
            font-weight: 500;
        }
        
        .user-menu a {
            color: #666;
            text-decoration: none;
        }
        
        .user-menu a:hover {
            color: var(--primary);
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
        }
        
        /* Cards */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
        }
        
        /* Panel */
        .panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-header h2 {
            font-size: 1.1rem;
            color: #333;
            margin: 0;
        }
        
        .panel-body {
            padding: 20px;
        }
        
        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: #27AE60;
            color: white;
        }
        
        .btn-warning {
            background: #F39C12;
            color: white;
        }
        
        .btn-danger {
            background: #E74C3C;
            color: white;
        }
        
        .btn-secondary {
            background: #95A5A6;
            color: white;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #D4EDDA;
            color: #155724;
        }
        
        .alert-danger {
            background: #F8D7DA;
            color: #721C24;
        }
        
        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-pending { background: #FFF3CD; color: #856404; }
        .badge-processing { background: #D1ECF1; color: #0C5460; }
        .badge-shipped { background: #CCE5FF; color: #004085; }
        .badge-completed { background: #D4EDDA; color: #155724; }
        .badge-cancelled { background: #F8D7DA; color: #721C24; }
        
        /* Image Preview */
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🕌 <?= $siteName ?></h2>
            <small>Admin Panel</small>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-section">Menu Utama</div>
            <a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
                <span class="icon">📊</span> Dashboard
            </a>
            
            <div class="menu-section">Toko</div>
            <a href="products.php" class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">
                <span class="icon">📦</span> Produk
            </a>
            <a href="categories.php" class="<?= $currentPage == 'categories.php' ? 'active' : '' ?>">
                <span class="icon">📂</span> Kategori
            </a>
            <a href="orders.php" class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>">
                <span class="icon">🧾</span> Pesanan
            </a>
            
            <div class="menu-section">Konten</div>
            <a href="articles.php" class="<?= $currentPage == 'articles.php' ? 'active' : '' ?>">
                <span class="icon">📝</span> Artikel
            </a>
            <a href="about.php" class="<?= $currentPage == 'about.php' ? 'active' : '' ?>">
                <span class="icon">📖</span> Tentang Kami
            </a>
            <a href="chats.php" class="<?= $currentPage == 'chats.php' ? 'active' : '' ?>">
                <span class="icon">💬</span> Chat
                <?php 
                $unreadChats = db()->query("SELECT SUM(unread_admin) FROM chats")->fetchColumn();
                if ($unreadChats > 0): 
                ?>
                <span style="background: #E74C3C; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; margin-left: auto;"><?= $unreadChats ?></span>
                <?php endif; ?>
            </a>
            
            <div class="menu-section">Aktivitas</div>
            <a href="dakwah-videos.php" class="<?= $currentPage == 'dakwah-videos.php' ? 'active' : '' ?>">
                <span class="icon">🎬</span> Video Dakwah
            </a>
            <a href="social-activities.php" class="<?= $currentPage == 'social-activities.php' ? 'active' : '' ?>">
                <span class="icon">🤝</span> Kegiatan Sosial
            </a>
            <a href="quizzes.php" class="<?= $currentPage == 'quizzes.php' || $currentPage == 'quiz-questions.php' ? 'active' : '' ?>">
                <span class="icon">📝</span> Kuis Islami
            </a>
            
            <div class="menu-section">Lainnya</div>
            <a href="users.php" class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
                <span class="icon">👥</span> Kelola User
            </a>
            <a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                <span class="icon">⚙️</span> Pengaturan
            </a>
            <a href="<?= BASE_URL ?>" target="_blank">
                <span class="icon">🌐</span> Lihat Website
            </a>
            <a href="logout.php">
                <span class="icon">🚪</span> Logout
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="user-menu">
                <span class="user-name">👤 <?= htmlspecialchars($currentUser['name']) ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </header>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php displayFlash(); ?>

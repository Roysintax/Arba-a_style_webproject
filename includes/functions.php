<?php
/**
 * Helper Functions
 * Toko Islami - Online Shop & Artikel
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout - 10 minutes (600 seconds)
define('SESSION_TIMEOUT', 600);

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive >= SESSION_TIMEOUT) {
        // Session expired, destroy and redirect
        session_unset();
        session_destroy();
        
        // Redirect to login if on a protected page
        if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/pages/auth/login.php?expired=1');
            exit;
        }
    }
}
// Update last activity time
$_SESSION['last_activity'] = time();

/**
 * Format number to Rupiah currency
 */
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Generate URL-friendly slug
 */
function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Upload image file
 */
function uploadImage($file, $folder = 'products') {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File terlalu besar (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan'];
    }
    
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $folder . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal mengupload file'];
}

/**
 * Delete uploaded file
 */
function deleteImage($filename) {
    $filepath = UPLOAD_PATH . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get image URL
 */
function getImageUrl($filename, $default = 'assets/images/no-image.png') {
    if (empty($filename)) {
        return BASE_URL . '/' . $default;
    }
    return UPLOAD_URL . $filename;
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $class . '">' . $flash['message'] . '</div>';
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin (from admins table)
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['user_name'],
            'is_admin' => false
        ];
    }
    if (isAdmin()) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'name' => $_SESSION['admin_name'],
            'is_admin' => true
        ];
    }
    return null;
}

/**
 * Get about page content from database
 */
function getAboutPage() {
    try {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM about_page LIMIT 1");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Cart functions
 */
function getCart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function updateCartItem($productId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartCount() {
    $cart = getCart();
    return array_sum($cart);
}

function getCartTotal() {
    $cart = getCart();
    $total = 0;
    
    if (!empty($cart)) {
        $pdo = db();
        $ids = array_keys($cart);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $total += $products[$productId] * $quantity;
            }
        }
    }
    
    return $total;
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Format date to Indonesian
 */
function formatDate($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
 * Get setting value
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $pdo = db();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $settings = [];
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Pagination helper
 */
function paginate($totalItems, $currentPage = 1, $perPage = 12) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Render pagination links
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<div class="pagination">';
    
    if ($pagination['has_prev']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '" class="page-link">&laquo; Sebelumnya</a>';
    }
    
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = $i === $pagination['current_page'] ? 'active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="page-link ' . $active . '">' . $i . '</a>';
    }
    
    if ($pagination['has_next']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '" class="page-link">Selanjutnya &raquo;</a>';
    }
    
    $html .= '</div>';
    return $html;
}

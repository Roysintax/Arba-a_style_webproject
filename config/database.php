<?php
/**
 * Database Configuration
 * Toko Islami - Online Shop & Artikel
 */

// Check environment (Local vs Live)
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    // Localhost (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'shop_islami');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://localhost/shopping');
} else {
    // Live Server (InfinityFree)
    define('DB_HOST', 'sql304.infinityfree.com');
    define('DB_NAME', 'if0_40767293_shop_islami');
    define('DB_USER', 'if0_40767293');
    define('DB_PASS', 'sp7ycxfM0qicZW');
    
    // Auto-detect protocol (HTTPS or HTTP) for CSS/JS compatibility
    // NOTE: Files uploaded to htdocs root, so no /shopping subdirectory
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                ? 'https' : 'http';
                
    define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST']); 
}

define('DB_CHARSET', 'utf8mb4');

// URLs
define('ADMIN_URL', BASE_URL . '/admin');

// Upload paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection
 */
function db() {
    return Database::getInstance()->getConnection();
}

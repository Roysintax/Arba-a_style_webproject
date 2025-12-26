<?php
/**
 * User Login Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Masuk';
require_once '../../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        // Query from users table only (not admins)
        $stmt = db()->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['last_activity'] = time();
            
            // Update last login
            db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            
            // Redirect to previous page or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../../index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Username atau password salah';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div style="max-width: 450px; margin: 0 auto;">
            <div style="background: white; padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <span style="font-size: 3rem;">🕌</span>
                    <h1 style="font-size: 1.8rem; margin: 15px 0 5px;">Masuk</h1>
                    <p style="color: var(--text-secondary);">Selamat datang kembali</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Username atau Email</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">🔐 Masuk</button>
                </form>
                
                <div style="text-align: center; margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--cream);">
                    <p style="color: var(--text-secondary);">
                        Belum punya akun? <a href="register.php" style="color: var(--primary); font-weight: 600;">Daftar sekarang</a>
                    </p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <span style="color: var(--gold); font-size: 1.2rem;">بِسْمِ اللَّهِ</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

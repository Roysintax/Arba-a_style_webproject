<?php
/**
 * User Registration Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Daftar Akun';
require_once '../../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($username)) $errors[] = 'Username harus diisi';
    if (strlen($username) < 4) $errors[] = 'Username minimal 4 karakter';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
    if (empty($email)) $errors[] = 'Email harus diisi';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid';
    if (empty($password)) $errors[] = 'Password harus diisi';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
    if ($password !== $confirm_password) $errors[] = 'Konfirmasi password tidak cocok';
    
    // Check if username exists
    if (empty($errors)) {
        $stmt = db()->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username sudah digunakan';
        }
    }
    
    // Check if email exists
    if (empty($errors)) {
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar';
        }
    }
    
    // Register user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = db()->prepare("
            INSERT INTO users (username, password, name, email, is_active) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$username, $hashedPassword, $name, $email]);
        
        $success = true;
    }
}
?>

<section class="section">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            
            <?php if ($success): ?>
            <!-- Success Message -->
            <div style="background: white; padding: 50px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 20px;">✅</div>
                <h2 style="color: var(--success); margin-bottom: 15px;">Pendaftaran Berhasil!</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">
                    Akun Anda telah berhasil dibuat. Silakan login untuk mulai berbelanja.
                </p>
                <a href="login.php" class="btn btn-primary btn-lg">🔐 Masuk Sekarang</a>
            </div>
            
            <?php else: ?>
            <!-- Registration Form -->
            <div style="background: white; padding: 40px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <span style="font-size: 3rem;">🕌</span>
                    <h1 style="font-size: 1.8rem; margin: 15px 0 5px;">Daftar Akun</h1>
                    <p style="color: var(--text-secondary);">Bergabunglah dengan Toko Islami</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" placeholder="Pilih username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
                        <small style="color: var(--text-light);">Minimal 4 karakter, hanya huruf, angka, dan underscore</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password *</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">📝 Daftar Sekarang</button>
                </form>
                
                <div style="text-align: center; margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--cream);">
                    <p style="color: var(--text-secondary);">
                        Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight: 600;">Masuk di sini</a>
                    </p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <span style="color: var(--gold); font-size: 1.2rem;">بِسْمِ اللَّهِ</span>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

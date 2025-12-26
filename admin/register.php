<?php
/**
 * Admin Register Page
 * Toko Islami - Admin Panel
 * Halaman untuk membuat akun admin baru
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($name) || empty($password)) {
        $error = 'Username, nama, dan password harus diisi';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        // Check if username exists
        $stmt = db()->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan';
        } else {
            // Create admin
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO admins (username, password, name, email) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $hashedPassword, $name, $email])) {
                $success = 'Akun admin berhasil dibuat! Silakan login.';
            } else {
                $error = 'Gagal membuat akun admin';
            }
        }
    }
}

$siteName = getSetting('site_name', 'Toko Islami');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - <?= $siteName ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00695C;
            --primary-dark: #004D40;
            --gold: #D4AF37;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header .icon {
            font-size: 3.5rem;
            margin-bottom: 10px;
        }
        
        .register-header h1 {
            color: var(--primary-dark);
            font-size: 1.6rem;
            margin-bottom: 5px;
        }
        
        .register-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
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
            padding: 12px 15px;
            border: 2px solid #E0E0E0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: var(--primary-dark);
        }
        
        .error {
            background: #F8D7DA;
            color: #721C24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .success {
            background: #D4EDDA;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .bismillah {
            text-align: center;
            color: var(--gold);
            font-size: 1.1rem;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="icon">🕌</div>
            <h1><?= $siteName ?></h1>
            <p>Daftar Akun Admin Baru</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email (opsional)">
            </div>
            
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>
            
            <div class="form-group">
                <label>Konfirmasi Password *</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
            </div>
            
            <button type="submit" class="btn">📝 Daftar Admin</button>
        </form>
        
        <div class="links">
            <a href="login.php">← Sudah punya akun? Login</a>
        </div>
        
        <div class="bismillah">بِسْمِ اللَّهِ</div>
    </div>
</body>
</html>

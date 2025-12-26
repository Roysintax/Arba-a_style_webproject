<?php
/**
 * User Account Page
 * Toko Islami - Online Shop & Artikel
 */

$pageTitle = 'Akun Saya';
require_once '../includes/header.php';

// Require login
if (!isLoggedIn()) {
    header('Location: auth/login.php?redirect=account.php');
    exit;
}

// Get user data
$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user orders
$ordersStmt = db()->prepare("
    SELECT * FROM orders 
    WHERE customer_email = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$ordersStmt->execute([$user['email']]);
$orders = $ordersStmt->fetchAll();

// Handle profile update
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($name)) $errors[] = 'Nama harus diisi';
        if (empty($email)) $errors[] = 'Email harus diisi';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid';
        
        // Check email uniqueness
        if (empty($errors) && $email !== $user['email']) {
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah digunakan';
            }
        }
        
        if (empty($errors)) {
            db()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")->execute([$name, $email, $user['id']]);
            $_SESSION['user_name'] = $name;
            $success = 'Profil berhasil diperbarui';
            
            // Refresh user data
            $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password)) $errors[] = 'Password saat ini harus diisi';
        if (empty($new_password)) $errors[] = 'Password baru harus diisi';
        if (strlen($new_password) < 6) $errors[] = 'Password baru minimal 6 karakter';
        if ($new_password !== $confirm_password) $errors[] = 'Konfirmasi password tidak cocok';
        
        if (empty($errors) && !password_verify($current_password, $user['password'])) {
            $errors[] = 'Password saat ini salah';
        }
        
        if (empty($errors)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            db()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashedPassword, $user['id']]);
            $success = 'Password berhasil diubah';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Beranda</a>
            <span>›</span>
            <span style="color: var(--primary);">Akun Saya</span>
        </div>
        
        <h1 style="margin-bottom: 30px;">👤 Akun Saya</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Profile Info -->
            <div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--cream);">
                    📋 Informasi Profil
                </h3>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background: #f5f5f5;">
                        <small style="color: var(--text-light);">Username tidak dapat diubah</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--cream);">
                    🔐 Ubah Password
                </h3>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label>Password Saat Ini *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password Baru *</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small style="color: var(--text-light);">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password Baru *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">🔄 Ubah Password</button>
                </form>
            </div>
        </div>
        
        <!-- Order History -->
        <div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-top: 30px;">
            <h3 style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--cream);">
                🧾 Riwayat Pesanan
            </h3>
            
            <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 3rem; margin-bottom: 15px;">📦</div>
                <p style="color: var(--text-secondary);">Belum ada riwayat pesanan</p>
                <a href="shop/products.php" class="btn btn-primary" style="margin-top: 10px;">Mulai Belanja</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?= $order['order_number'] ?></strong></td>
                            <td><?= formatDate($order['created_at']) ?></td>
                            <td><?= formatRupiah($order['total_amount']) ?></td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'pending' => '⏳ Menunggu',
                                    'processing' => '🔄 Diproses',
                                    'shipped' => '🚚 Dikirim',
                                    'completed' => '✅ Selesai',
                                    'cancelled' => '❌ Dibatalkan'
                                ];
                                echo $statusLabels[$order['status']] ?? $order['status'];
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Account Info -->
        <div style="background: var(--cream-dark); padding: 20px; border-radius: var(--radius-md); margin-top: 30px; text-align: center;">
            <p style="color: var(--text-secondary); margin: 0;">
                Terdaftar sejak: <?= formatDate($user['created_at']) ?>
                <?php if ($user['last_login']): ?>
                | Login terakhir: <?= formatDate($user['last_login']) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

<?php
/**
 * Admin - User Management
 * Kelola pengguna yang terdaftar
 */

$pageTitle = 'Kelola User';
require_once 'includes/admin-header.php';

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (isAdmin()) {
        db()->prepare("DELETE FROM users WHERE id = ?")->execute([$_GET['delete']]);
        setFlash('success', 'User berhasil dihapus');
    }
    header('Location: users.php');
    exit;
}

// Handle update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $userId = (int)$_POST['user_id'];
    $newPassword = $_POST['new_password'];
    
    if (strlen($newPassword) < 6) {
        setFlash('error', 'Password minimal 6 karakter');
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        db()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashedPassword, $userId]);
        setFlash('success', 'Password berhasil diubah');
    }
    header('Location: users.php');
    exit;
}

// Handle toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $user = db()->prepare("SELECT is_active FROM users WHERE id = ?");
    $user->execute([$_GET['toggle']]);
    $current = $user->fetch();
    
    if ($current) {
        $newStatus = $current['is_active'] ? 0 : 1;
        db()->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $_GET['toggle']]);
        setFlash('success', 'Status user berhasil diubah');
    }
    header('Location: users.php');
    exit;
}

// Get all users
$users = db()->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Get statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => $u['is_active']));
$newUsersToday = db()->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>

<div class="panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>👥 Kelola User</h2>
        <span style="color: #666;">Total: <?= $totalUsers ?> user</span>
    </div>
    <div class="panel-body" style="padding: 0;">
        
        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #eee;">
            <div style="text-align: center; padding: 15px; background: white; border-radius: 10px;">
                <div style="font-size: 2rem; color: var(--primary);">👥</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-dark);"><?= $totalUsers ?></div>
                <div style="color: #666; font-size: 0.9rem;">Total User</div>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 10px;">
                <div style="font-size: 2rem; color: #27AE60;">✅</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #27AE60;"><?= $activeUsers ?></div>
                <div style="color: #666; font-size: 0.9rem;">User Aktif</div>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 10px;">
                <div style="font-size: 2rem; color: #3498DB;">🆕</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #3498DB;"><?= $newUsersToday ?></div>
                <div style="color: #666; font-size: 0.9rem;">User Baru Hari Ini</div>
            </div>
        </div>
        
        <!-- User Table -->
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th>Terdaftar</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 10px;">👤</div>
                            Belum ada user terdaftar
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $i => $user): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($user['name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?: '-') ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $user['last_login'] ? formatDate($user['last_login'], 'd M Y H:i') : '-' ?>
                        </td>
                        <td><?= formatDate($user['created_at'], 'd M Y') ?></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <button onclick="openPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-sm" style="background: #3498DB; color: white;">
                                    🔑 Password
                                </button>
                                <a href="?toggle=<?= $user['id'] ?>" 
                                   class="btn btn-sm" style="background: <?= $user['is_active'] ? '#F39C12' : '#27AE60' ?>; color: white;">
                                    <?= $user['is_active'] ? '⏸️' : '▶️' ?>
                                </a>
                                <a href="?delete=<?= $user['id'] ?>" 
                                   onclick="return confirm('Yakin hapus user ini?')"
                                   class="btn btn-sm" style="background: #E74C3C; color: white;">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Change Password -->
<div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 400px; width: 90%;">
        <h3 style="margin-bottom: 20px; color: var(--primary-dark);">🔑 Ubah Password</h3>
        <p style="margin-bottom: 15px; color: #666;">User: <strong id="modalUsername"></strong></p>
        
        <form action="" method="POST">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="update_password" class="btn btn-primary" style="flex: 1;">
                    💾 Simpan
                </button>
                <button type="button" onclick="closePasswordModal()" class="btn" style="flex: 1; background: #ddd;">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-success {
    background: #d4edda;
    color: #155724;
}
.badge-danger {
    background: #f8d7da;
    color: #721c24;
}
.btn-sm {
    padding: 5px 10px;
    font-size: 0.8rem;
    border-radius: 5px;
}
</style>

<script>
function openPasswordModal(userId, username) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('passwordModal').style.display = 'flex';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>

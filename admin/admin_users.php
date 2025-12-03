<?php
include 'auth_admin.php';

// HAPUS USER
if (isset($_POST['hapus_user'])) {
    $id = intval($_POST['id']);
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_users.php?status=deleted");
        exit();
    } else {
        header("Location: admin_users.php?error=cant_delete_self");
        exit();
    }
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');
    
    if ($username && $role) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_users.php?status=updated");
        exit();
    } else {
        header("Location: admin_users.php?error=invalid_data");
        exit();
    }
}

// LOAD USER
$users = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_users.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="header-left">
            <h1><span>👥</span> Kelola User</h1>
            <p class="header-subtitle">Atur akun, peran, dan akses pengguna aplikasi Anda.</p>
        </div>
        <div class="nav">
            <a href="admin_products.php">
                <span>📦 Kelola Produk</span>
            </a>
            <a href="admin_transactions.php">
                <span>💳 Kelola Transaksi</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span>🚪 Logout</span>
            </a>
        </div>
    </div>

    <?php if (isset($_GET['status']) || isset($_GET['error'])): ?>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="alert alert-success" id="alertBox">
                <span class="icon">✅</span>
                <span>Data user berhasil diperbarui.</span>
                <button type="button" class="close-alert" onclick="document.getElementById('alertBox').style.display='none'">×</button>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
            <div class="alert alert-success" id="alertBox">
                <span class="icon">🗑️</span>
                <span>User berhasil dihapus.</span>
                <button type="button" class="close-alert" onclick="document.getElementById('alertBox').style.display='none'">×</button>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'cant_delete_self'): ?>
            <div class="alert alert-error" id="alertBox">
                <span class="icon">⚠️</span>
                <span>Anda tidak dapat menghapus akun yang sedang digunakan.</span>
                <button type="button" class="close-alert" onclick="document.getElementById('alertBox').style.display='none'">×</button>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_data'): ?>
            <div class="alert alert-error" id="alertBox">
                <span class="icon">⚠️</span>
                <span>Data tidak valid, silakan cek kembali.</span>
                <button type="button" class="close-alert" onclick="document.getElementById('alertBox').style.display='none'">×</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="section-header">
        <div class="section-title">
            <span>📋</span>
            <span>Daftar User</span>
        </div>
        <div class="user-count-pill">
            <?php echo $users->num_rows; ?> user terdaftar
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Cari berdasarkan username atau email...">
            <span class="clear-search" id="clearSearch">✕</span>
        </div>
        <div class="role-legend">
            <span class="label">Legenda peran:</span>
            <span class="role-badge-mini admin">Admin</span>
            <span class="role-badge-mini user">User</span>
        </div>
    </div>
    
    <div class="table-wrapper">
        <div class="table-scroll">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username & Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = $users->fetch_assoc()) { 
                    $isCurrentUser = ($u['id'] == $_SESSION['user_id']);
                ?>
                    <tr class="user-row <?php echo $isCurrentUser ? 'current-user-row' : ''; ?>">
                        <td class="cell-id" data-label="ID">
                            <span class="user-id">#<?php echo $u['id']; ?></span>
                            <?php if ($isCurrentUser): ?>
                                <span class="current-user-chip">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Username & Email">
                            <div style="font-weight:600; font-size:13px;">
                                <?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="email-muted">
                                <?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </td>
                        <td data-label="Role">
                            <span class="role-badge <?php echo $u['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                <?php echo htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td data-label="Aksi">
                            <div class="action-cell">
                                <!-- UPDATE -->
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                    <input 
                                        type="text" 
                                        name="username" 
                                        class="action-input"
                                        value="<?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>" 
                                        required
                                    >
                                    <select name="role" class="action-select" required>
                                        <option value="user" <?php echo $u['role'] === "user" ? "selected" : ""; ?>>user</option>
                                        <option value="admin" <?php echo $u['role'] === "admin" ? "selected" : ""; ?>>admin</option>
                                    </select>
                                    <button class="btn-update" name="update_user">
                                        <span>Simpan</span>
                                    </button>
                                </form>

                                <!-- HAPUS -->
                                <?php if (!$isCurrentUser) { ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus user ini? Tindakan ini tidak dapat dibatalkan.')">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <button class="btn-delete" name="hapus_user">
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
    <script src="js/admin_users.js"></script>
</body>
</html>

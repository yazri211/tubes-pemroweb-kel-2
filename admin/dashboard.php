<?php 
include 'auth_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Dashboard Admin</h1>
                <p class="header-subtitle">Kelola sistem toko online Anda dari satu tempat</p>
            </div>
            <div class="user-info">
                <p>👤 <?= htmlspecialchars($_SESSION['username']) ?></p>
                <a href="../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Hanya MENU UTAMA -->
        <h2 class="section-title">Menu Utama</h2>
        <div class="menu">
            <a href="admin_users.php" class="menu-card">
                <span class="menu-icon">👥</span>
                <h2>Kelola Users</h2>
                <p>Tambah, edit, atau hapus akun pengguna.</p>
            </a>
            
            <a href="admin_products.php" class="menu-card">
                <span class="menu-icon">🛒</span>
                <h2>Kelola Produk</h2>
                <p>Kelola daftar produk, stok, dan harga.</p>
            </a>
            
            <a href="admin_transactions.php" class="menu-card">
                <span class="menu-icon">💳</span>
                <h2>Kelola Transaksi</h2>
                <p>Lihat dan ubah status transaksi pelanggan.</p>
            </a>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Admin System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

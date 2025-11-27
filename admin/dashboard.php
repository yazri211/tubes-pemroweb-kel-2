<?php 
include 'auth_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #fff7fb, #ffe6f4);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3f0f23ff;
            padding-bottom: 18px;
        }
        
        .header h1 {
            color: #2d0120;
            font-size: 28px;
        }
        
        .header-subtitle {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info p {
            color: #666;
            font-weight: bold;
            font-size: 14px;
            background: #fff0f6;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #ffb3d9;
        }
        
        .logout-btn {
            background: #ff4d94;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 999px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.25s, transform 0.15s, box-shadow 0.25s;
            box-shadow: 0 6px 15px rgba(255, 77, 148, 0.5);
        }
        
        .logout-btn:hover {
            background: #e60073;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(255, 77, 148, 0.6);
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #3f0f23ff;
            margin: 10px 0 12px;
        }
        
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .menu-card {
            background: linear-gradient(135deg, #ffb3d9 0%, #ffc2e1 100%);
            color: #660033;
            padding: 24px 22px;
            border-radius: 14px;
            text-decoration: none;
            transition: transform 0.25s, box-shadow 0.25s, background 0.25s;
            box-shadow: 0 5px 15px rgba(236, 49, 130, 0.35);
            position: relative;
            overflow: hidden;
        }

        .menu-icon {
            font-size: 26px;
            margin-bottom: 8px;
            display: block;
        }
        
        .menu-card h2 {
            font-size: 18px;
            margin-bottom: 6px;
        }
        
        .menu-card p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(236, 49, 130, 0.55);
            background: linear-gradient(135deg, #ff99cc 0%, #ffb3d9 100%);
        }
        
        .footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 16px;
            border-top: 2px solid #eee;
            color: #666;
            font-size: 13px;
        }
    </style>
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

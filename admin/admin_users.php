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
        header("Location: admin_users.php");
        exit();
    }
}

// UPDATE USER
if (isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $username = htmlspecialchars($_POST['username']);
    $role = htmlspecialchars($_POST['role']);
    
    if ($username && $role) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_users.php");
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #fff7fb;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #ff6bb5;
            padding-bottom: 25px;
            background: #ff6bb5;
            padding: 20px;
            border-radius: 15px;
            color: white;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .nav {
            display: flex;
            gap: 20px;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .nav a:last-child {
            background: #b43074ff;
        }
       
        .section-title {
            font-size: 22px;
            margin: 40px 0 20px 0;
            color: #ff6bb5;
            border-bottom: 2px solid #ffb3d9;
            padding-bottom: 15px;
            font-weight: 600;
        }
        
        .form-group {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #fff5f9 0%, #ffebf2 100%);
            border-radius: 15px;
            border: 1px solid #ffb3d9;
            box-shadow: 0 5px 15px rgba(255, 179, 217, 0.3);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        input, textarea, select {
            padding: 15px;
            border: 2px solid #ffb3d9;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Poppins', Arial;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #ff6bb5;
            box-shadow: 0 0 10px rgba(255, 107, 181, 0.3);
            transform: scale(1.02);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        button {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: #ff6bb5;
            color: white;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%);
            color: white;
            padding: 10px 18px;
            font-size: 14px;
        }
    
        .btn-delete {
            background: linear-gradient(135deg, #d10000ff 0%, #c9302c 100%);
            color: white;
            padding: 10px 18px;
            font-size: 14px;
        }
    
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        th {
            background: #ff4d94;
            color: #561d39ff;
            padding: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:nth-child(even) {
            background: #fff9fb;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ffb3d9;
            transition: transform 0.3s ease;
        }
        
        .action-cell {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .action-cell input, .action-cell textarea, .action-cell select {
            font-size: 14px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ffb3d9;
        }
        
        .action-cell textarea {
            min-height: 50px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 14px;
            }
            
            .action-cell {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>👥 Kelola User</h1>
        <div class="nav">
            <a href="admin_products.php">Kelola Produk</a>
            <a href="admin_transactions.php">Kelola Transaksi</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <h2 class="section-title">📋 Daftar User</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>

        <?php while($u = $users->fetch_assoc()) { ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td>
                <div class="action-cell">
                    <!-- UPDATE -->
                    <form method="post" style="display:contents;">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="text" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
                        <select name="role" required>
                            <option value="user" <?= $u['role']=="user"?"selected":"" ?>>user</option>
                            <option value="admin" <?= $u['role']=="admin"?"selected":"" ?>>admin</option>
                        </select>
                        <button class="btn-update" name="update_user">Update</button>
                    </form>

                    <!-- HAPUS -->
                    <?php if ($u['id'] != $_SESSION['user_id']) { ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus user ini?')">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button class="btn-delete" name="hapus_user">Hapus</button>
                    </form>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
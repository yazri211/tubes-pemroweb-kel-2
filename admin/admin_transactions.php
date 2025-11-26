<?php
include 'auth_admin.php';

// UPDATE STATUS
if (isset($_POST['set_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    
    $valid_status = ['pending', 'completed', 'canceled'];
    if (in_array($status, $valid_status)) {
        mysqli_query($conn, "UPDATE transactions SET status = '$status' WHERE id = $id");
    }
    header("Location: admin_transactions.php");
    exit();
}

// LOAD TRANSAKSI
$tx = mysqli_query($conn, "
    SELECT t.id, t.user_id, t.total, t.status, t.created_at, u.username, u.email
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi</title>
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
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-canceled {
            background: #f8d7da;
            color: #721c24;
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
        <h1>💰 Kelola Transaksi</h1>
        <div class="nav">
            <a href="admin_users.php">Kelola User</a>
            <a href="admin_products.php">Kelola Produk</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <h2 class="section-title">📋 Daftar Transaksi</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        <?php while($t = mysqli_fetch_assoc($tx)) { ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><?= isset($t['username']) ? htmlspecialchars($t['username']) : '-' ?></td>
            <td><?= isset($t['email']) ? htmlspecialchars($t['email']) : '-' ?></td>
            <td>Rp <?= number_format($t['total'], 0, ',', '.') ?></td>
            <td>
                <span class="status-badge status-<?= $t['status'] ?>">
                    <?= ucfirst($t['status']) ?>
                </span>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
            <td>
                <div class="action-cell">
                    <form method="post" style="display:contents;">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $t['status']=="pending"?"selected":"" ?>>Pending</option>
                            <option value="completed" <?= $t['status']=="completed"?"selected":"" ?>>Completed</option>
                            <option value="canceled" <?= $t['status']=="canceled"?"selected":"" ?>>Canceled</option>
                        </select>
                        <button class="btn-update" name="set_status">Set</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
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
    <link rel="stylesheet" href="css/admin_transactions.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-left">
            <h1><span>💰</span> Kelola Transaksi</h1>
            <p class="header-subtitle">Pantau dan kelola seluruh transaksi user dengan cepat dan rapi.</p>
        </div>
        <div class="nav">
            <a href="admin_users.php">Kelola User</a>
            <a href="admin_products.php">Kelola Produk</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <h2 class="section-title">
        <span class="icon">📋</span>
        Daftar Transaksi
    </h2>
    <p class="section-subtitle">Klik status untuk mengubah transaksi menjadi <b>Pending</b>, <b>Completed</b>, atau <b>Canceled</b>.</p>

    <div class="summary-bar">
        <div class="summary-chip">
            <span class="summary-dot"></span>
            Total transaksi: <span class="value">
                <?= mysqli_num_rows($tx) ?>
            </span>
        </div>
        <div class="summary-chip">
            Status tersedia: <span class="value">Pending, Completed, Canceled</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th class="td-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while($t = mysqli_fetch_assoc($tx)) { ?>
                <tr>
                    <td data-label="ID">
                        <span class="badge-id">#<?= $t['id'] ?></span>
                    </td>
                    <td data-label="Username">
                        <?= isset($t['username']) ? htmlspecialchars($t['username']) : '<span class="text-muted">-</span>' ?>
                    </td>
                    <td data-label="Email">
                        <?= isset($t['email']) ? htmlspecialchars($t['email']) : '<span class="text-muted">-</span>' ?>
                    </td>
                    <td data-label="Total">
                        Rp <?= number_format($t['total'], 0, ',', '.') ?>
                    </td>
                    <td data-label="Status">
                        <span class="status-badge status-<?= $t['status'] ?>">
                            <?= ucfirst($t['status']) ?>
                        </span>
                    </td>
                    <td data-label="Tanggal">
                        <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                    </td>
                    <td data-label="Aksi">
                        <div class="action-cell">
                            <form method="post" style="display:contents;">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <select name="status">
                                    <option value="pending"   <?= $t['status']=="pending"?"selected":"" ?>>Pending</option>
                                    <option value="completed" <?= $t['status']=="completed"?"selected":"" ?>>Completed</option>
                                    <option value="canceled"  <?= $t['status']=="canceled"?"selected":"" ?>>Canceled</option>
                                </select>
                                <button class="btn-update" name="set_status">Set</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

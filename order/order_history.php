<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

$orders = $conn->query("
    SELECT id, total, metode_pembayaran, status, created_at 
    FROM transactions
    WHERE user_id = $user_id 
    ORDER BY created_at DESC
");

$orders_count = $orders ? $orders->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="order_history.css">
</head>

<body>
<div class="container">

    <div class="header">
        <div class="header-left">
            <h1><span class="icon">📋</span>Riwayat Transaksi</h1>
            <small>Lihat daftar pesanan yang pernah kamu buat.</small>
            <div class="summary-pill">
                🔁 <span><?= $orders_count ?> transaksi</span>
            </div>
        </div>
        <div class="nav">
            <a href="../home.php">
                <span>🏠</span>
                <span>Beranda</span>
            </a>
        </div>
    </div>

    <?php if ($orders_count > 0): ?>
    <div class="table-wrapper">
        <div class="table-scroll">
            <table aria-label="Riwayat transaksi">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Total Pembayaran</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID Transaksi">#<?= (int)$row['id'] ?></td>
                        <td data-label="Total Pembayaran">
                            Rp <?= number_format((float)$row['total'], 0, ',', '.') ?>
                        </td>
                        <td data-label="Metode">
                            <?= htmlspecialchars($row['metode_pembayaran'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td data-label="Status">
                            <?php
                                $status_raw = $row['status'];
                                $status_class = 'status-' . preg_replace('/[^a-z]/', '', strtolower($status_raw));
                            ?>
                            <span class="status-badge <?= $status_class ?>">
                                <?= ucfirst(htmlspecialchars($status_raw, ENT_QUOTES, 'UTF-8')) ?>
                            </span>
                        </td>
                        <td data-label="Tanggal">
                            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['created_at']))) ?>
                        </td>
                        <td data-label="Aksi">
                            <a class="btn" href="order_detail.php?id=<?= (int)$row['id'] ?>">
                                <span class="icon">🔍</span>
                                <span>Lihat Detail</span>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
        <div class="empty">
            <strong>Belum ada transaksi.</strong>
            <span>Mulai belanja dan lihat pesananmu muncul di sini.</span>
            <br>
            <a href="../home.php" class="btn" style="margin-top:15px;">
                🛒 Mulai Belanja
            </a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>

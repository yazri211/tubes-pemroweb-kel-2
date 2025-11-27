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

    <style>
        :root {
            --bg: #fdf2f8;
            --card-bg: #ffffff;
            --pink-light: #fed7e2;
            --pink-mid: #ec4899;
            --pink-dark: #be185d;
            --text-dark: #431021;
            --muted: #6b7280;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
            --radius: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background: var(--bg);
            padding: 18px;
            color: #111827;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 26px 24px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--pink-light);
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--pink-light);
        }

        .header-left h1 {
            font-size: 22px;
            color: var(--pink-dark);
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .header-left small {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            color: var(--muted);
        }

        .header-left h1 span.icon {
            font-size: 22px;
        }

        .nav a {
            background: #ffffff;
            border: 1px solid var(--pink-dark);
            padding: 8px 14px;
            border-radius: 999px;
            color: var(--pink-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s ease;
        }

        .nav a:hover {
            background: var(--pink-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        .summary-pill {
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #fef2f7;
            border: 1px solid var(--pink-light);
            font-size: 12px;
            color: var(--pink-dark);
            font-weight: 600;
        }

        /* TABLE WRAPPER */
        .table-wrapper {
            margin-top: 8px;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        thead {
            background: #fee2f2;
        }

        th {
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
            border-bottom: 1px solid var(--pink-light);
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3e8ff;
            font-size: 13px;
            color: #111827;
            vertical-align: middle;
        }

        tbody tr {
            transition: background 0.12s ease, transform 0.08s ease;
        }

        tbody tr:hover {
            background: #fdf2f8;
            transform: translateY(-1px);
        }

        /* BADGE STATUS */
        .status-badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .status-pending,
        .status-tertunda {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed,
        .status-selesai {
            background: #d4edda;
            color: #155724;
        }

        .status-canceled,
        .status-batal {
            background: #f8d7da;
            color: #721c24;
        }

        /* BUTTON */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            background: var(--pink-mid);
            color: #fff;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: 0.18s ease;
        }

        .btn span.icon {
            margin-right: 4px;
        }

        .btn:hover {
            background: var(--pink-dark);
            transform: translateY(-1px);
        }

        .empty {
            text-align: center;
            padding: 40px 10px 10px;
            color: #6b7280;
            font-size: 14px;
        }

        .empty strong {
            display: block;
            margin-bottom: 6px;
            font-size: 16px;
            color: var(--pink-dark);
        }

        .empty .btn {
            margin-top: 14px;
        }

        /* RESPONSIVE: Tablet & HP */
        @media (max-width: 1024px) {
            body {
                padding: 12px;
            }

            .container {
                padding: 20px 16px 24px;
                border-radius: 16px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-wrapper {
                border-radius: 14px;
            }
        }

        /* RESPONSIVE: HP (card per transaksi) */
        @media (max-width: 768px) {
            .table-wrapper {
                background: transparent;
                box-shadow: none;
                border-radius: 0;
            }

            .table-scroll {
                overflow-x: visible;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            tbody {
                margin-top: 8px;
            }

            tbody tr {
                background: #ffffff;
                border-radius: 14px;
                box-shadow: var(--shadow);
                margin-bottom: 12px;
                padding: 10px 12px;
                border: 1px solid #ffe4f1;
            }

            td {
                border: none;
                padding: 6px 0;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            td:before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--pink-dark);
                font-size: 12px;
                margin-right: 8px;
                min-width: 110px;
                max-width: 50%;
                text-align: left;
            }

            td[data-label="Aksi"] {
                margin-top: 4px;
            }

            td[data-label="Aksi"] .btn {
                width: 100%;
                justify-content: center;
            }

            .summary-pill {
                margin-top: 8px;
            }
        }
    </style>
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

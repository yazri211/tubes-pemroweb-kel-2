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
        
        :root {
            --primary: #ec4899;      /* pink utama */
            --primary-dark: #db2777; /* pink lebih gelap */
            --bg: #f3f4f6;           /* abu lembut */
            --card-bg: #ffffff;
            --accent-soft: #fee2e2;
            --border-soft: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .container {
            width: 100%;
            max-width: 1400px;
            background: var(--card-bg);
            padding: 32px 28px 36px;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding: 18px 22px;
            border-radius: 18px;
            background: var(--primary);
            box-shadow: 0 12px 30px rgba(236, 72, 153, 0.45);
            color: #fdf2f8;
            position: relative;
            overflow: hidden;
        }

        /* dekorasi animasi di header */
        .header::before,
        .header::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            filter: blur(2px);
            animation: floatBubble 7s infinite linear;
        }

        .header::before {
            width: 160px;
            height: 160px;
            top: -60px;
            right: -30px;
        }

        .header::after {
            width: 110px;
            height: 110px;
            bottom: -40px;
            left: 10px;
            animation-duration: 9s;
        }

        @keyframes floatBubble {
            0% { transform: translateY(0) translateX(0); opacity: 0.9; }
            50% { transform: translateY(-8px) translateX(-6px); opacity: 0.6; }
            100% { transform: translateY(0) translateX(0); opacity: 0.9; }
        }
        
        .header-left {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header h1 span {
            font-size: 26px;
        }

        .header-subtitle {
            font-size: 13px;
            color: #fce7f3;
        }
        
        .nav {
            display: flex;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        
        .nav a {
            position: relative;
            color: #fdf2f8;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.1);
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 13px;
            border: 1px solid rgba(248, 250, 252, 0.3);
            backdrop-filter: blur(3px);
        }

        .nav a:last-child {
            background: #111827;
            border-color: #020617;
        }

        .nav a::after {
            content: "";
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 6px;
            height: 2px;
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.9);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.25s ease;
        }

        .nav a:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.26);
            background: rgba(15, 23, 42, 0.18);
        }

        .nav a:hover::after {
            transform: scaleX(1);
        }
      
        .section-title {
            font-size: 18px;
            margin: 12px 0 12px 0;
            color: var(--primary-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title span.icon {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--accent-soft);
            font-size: 15px;
        }

        .section-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        .summary-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f9fafb;
            border: 1px solid var(--border-soft);
            font-size: 12px;
            color: var(--text-muted);
        }

        .summary-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--primary);
        }

        .summary-chip span.value {
            color: var(--text-main);
            font-weight: 600;
        }

        .table-wrapper {
            width: 100%;
            margin-top: 10px;
            border-radius: 18px;
            background: #f9fafb;
            padding: 14px 14px 10px;
            border: 1px solid var(--border-soft);
        }
       
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        
        thead {
            background: #fee2e2;
        }

        th {
            color: #9f1239;
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid #fecaca;
            letter-spacing: 0.01em;
        }
        
        td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            color: var(--text-main);
            vertical-align: middle;
        }
        
        tbody tr {
            transition: background 0.2s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        tbody tr:hover {
            background: #fef2f2;
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(15, 23, 42, 0.08);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }
     
        .action-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
        }
        
        input, textarea, select {
            padding: 10px;
            border-radius: 9px;
            border: 1px solid var(--border-soft);
            font-size: 13px;
            font-family: 'Poppins', Arial, sans-serif;
            transition: all 0.2s ease;
            background: #f9fafb;
            color: var(--text-main);
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 1px rgba(236, 72, 153, 0.3);
            background: #ffffff;
        }
        
        select {
            padding-right: 26px;
        }

        button {
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 10px 18px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(236, 72, 153, 0.35);
        }

        .btn-update {
            background: #16a34a;
            color: white;
            padding: 9px 16px;
        }

        .btn-update:hover {
            background: #15803d;
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 10px 22px rgba(22, 163, 74, 0.35);
        }
 
        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 9px 16px;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 10px 22px rgba(220, 38, 38, 0.35);
        }
       
        .status-badge {
            display: inline-flex;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 11px;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
        }

        .status-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pending::before {
            background: #facc15;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-completed::before {
            background: #22c55e;
        }
        
        .status-canceled {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-canceled::before {
            background: #ef4444;
        }

        .status-badge:hover {
            animation: pulseBadge 0.9s ease-out;
        }

        @keyframes pulseBadge {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(236, 72, 153, 0.0); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 6px rgba(236, 72, 153, 0.15); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(236, 72, 153, 0.0); }
        }

        .td-right {
            text-align: right;
        }

        .text-muted {
            color: var(--text-muted);
        }

        .badge-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            background: #e5e7eb;
            color: #111827;
            font-weight: 600;
        }

        /* RESPONSIVE / MOBILE */
        @media (max-width: 900px) {
            body {
                padding: 12px;
            }

            .container {
                padding: 22px 16px 28px;
                border-radius: 18px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .nav {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .nav a {
                font-size: 12px;
                padding: 8px 14px;
            }

            .section-title {
                font-size: 16px;
            }

            .table-wrapper {
                padding: 0;
                background: transparent;
                border: none;
                box-shadow: none;
            }

            table {
                border-radius: 0;
                box-shadow: none;
                background: transparent;
            }

            thead {
                display: none;
            }

            tbody, tr, td {
                display: block;
                width: 100%;
            }

            tbody tr {
                background: #ffffff;
                border-radius: 16px;
                margin-bottom: 12px;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.11);
                padding: 6px 2px 10px;
            }

            tbody tr:hover {
                transform: translateY(-1px);
            }

            td {
                border-bottom: 1px solid #e5e7eb;
                padding: 9px 14px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            td:last-child {
                border-bottom: none;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-muted);
                font-size: 12px;
                flex: 0 0 40%;
                max-width: 130px;
            }

            td > *:not(span[data-label-content]) {
                flex: 1;
                text-align: right;
            }

            .badge-id {
                font-size: 11px;
            }

            .action-cell {
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            .action-cell select {
                width: 54%;
                min-width: 140px;
            }

            .action-cell button {
                padding-inline: 14px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 14px 14px 16px;
            }

            .header h1 {
                font-size: 20px;
            }

            .header h1 span {
                font-size: 22px;
            }

            .header-subtitle {
                font-size: 12px;
            }

            .summary-bar {
                flex-direction: column;
            }

            td {
                padding: 8px 12px;
            }

            td::before {
                flex-basis: 45%;
            }

            .action-cell {
                flex-direction: column;
                align-items: flex-end;
                gap: 6px;
            }

            .action-cell select {
                width: 100%;
            }

            .action-cell button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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

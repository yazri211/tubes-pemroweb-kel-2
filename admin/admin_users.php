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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff; /* SOLID */
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #ffffff; /* SOLID */
            padding: 32px 32px 40px;
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid #ffd1ec;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding: 18px 24px;
            border-radius: 18px;
            background: #ff6bb5; /* SOLID */
            color: white;
            box-shadow: 0 12px 30px rgba(255, 107, 181, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            top: -120px;
            right: -80px;
            filter: blur(2px);
        }
        
        .header-left {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(204, 38, 120, 0.5);
            margin-bottom: 4px;
        }

        .header small {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .nav {
            display: flex;
            gap: 10px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .nav a span.icon {
            font-size: 15px;
        }
        
        .nav a:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.24);
            box-shadow: 0 8px 16px rgba(146, 26, 89, 0.2);
        }

        .nav a.active {
            background: #ffffff;
            color: #ff4d94;
            box-shadow: 0 10px 20px rgba(145, 25, 85, 0.35);
        }
        
        .nav a.logout {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 2px 12px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-title {
            font-size: 20px;
            color: #ff4d94;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title span {
            font-size: 22px;
        }

        .user-count-pill {
            background: #fff0f7;
            color: #c0136b;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #ffd1ec;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin: 0 2px 10px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 38px 10px 38px;
            border-radius: 999px;
            border: 1px solid #ffd1ec;
            font-size: 14px;
            background: #fff7fb;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            border-color: #ff6bb5;
            box-shadow: 0 0 0 3px rgba(255, 107, 181, 0.18);
            background: #ffffff;
        }

        .search-box .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            opacity: 0.7;
        }

        .search-box .clear-search {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            cursor: pointer;
            opacity: 0.6;
            display: none;
        }

        .role-legend {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            flex-wrap: wrap;
        }

        .role-legend span.label {
            font-weight: 500;
            color: #555;
        }

        .role-badge-mini {
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .role-badge-mini.admin {
            background: #ffe6f4;
            color: #b10063;
            border: 1px solid #ffb3dd;
        }

        .role-badge-mini.user {
            background: #e6f4ff;
            color: #005fa3;
            border: 1px solid #b3ddff;
        }

        /* ALERT */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin: 8px 2px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
            animation: fadeIn 0.3s ease-out;
        }

        .alert-success {
            background: #e8fff5;
            border-color: #bff3d9;
            color: #146c43;
        }

        .alert-error {
            background: #fff0f0;
            border-color: #f5b3b3;
            color: #b02a37;
        }

        .alert span.icon {
            font-size: 18px;
        }

        .alert button.close-alert {
            margin-left: auto;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            opacity: 0.6;
        }

        .alert button.close-alert:hover {
            opacity: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-3px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        
        .table-wrapper {
            margin-top: 10px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #ffe0f0;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
            background: white;
            position: relative;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }
    
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 780px;
            background: white;
        }
        
        thead {
            background: #ff6bb5;
        }

        th {
            color: #fff5fb;
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }

        th:first-child {
            padding-left: 22px;
        }

        th:last-child {
            text-align: center;
        }
        
        tbody tr {
            transition: background 0.2s ease, transform 0.12s ease;
        }

        tbody tr:nth-child(even) {
            background: #fff9fd;
        }

        tbody tr:hover {
            background: #fff1f8;
            transform: translateY(-1px);
        }
        
        td {
            padding: 12px 18px;
            border-bottom: 1px solid #f4e5f0;
            font-size: 13px;
            vertical-align: middle;
        }

        td:first-child {
            padding-left: 22px;
        }

        td:last-child {
            text-align: center;
        }

        .user-id {
            font-weight: 600;
            color: #a2125e;
        }

        .email-muted {
            font-size: 12px;
            color: #777;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 600;
        }

        .role-admin {
            background: #ffe8f4;
            color: #b10063;
            border: 1px solid #ffb0dd;
        }

        .role-user {
            background: #eaf4ff;
            color: #084a94;
            border: 1px solid #b5d9ff;
        }

        .current-user-row {
            box-shadow: inset 3px 0 0 #ff6bb5;
        }

        .current-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            background: #fff2f8;
            color: #c2185b;
            margin-left: 6px;
        }

        .action-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
        
        .inline-form {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .action-input,
        .action-select {
            font-size: 12px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #ffd1ec;
            min-width: 130px;
            outline: none;
            background: #fffafd;
            transition: all 0.18s ease;
        }
        
        .action-input:focus,
        .action-select:focus {
            border-color: #ff6bb5;
            box-shadow: 0 0 0 2px rgba(255, 107, 181, 0.18);
            background: #ffffff;
        }
        
        button {
            padding: 8px 14px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(129, 28, 81, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-update {
            background: #21a821;
            color: white;
        }
    
        .btn-delete {
            background: #d62525;
            color: white;
        }

        .btn-update:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(56, 142, 60, 0.4);
        }
    
        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(211, 47, 47, 0.4);
        }

        .btn-update span.icon,
        .btn-delete span.icon {
            font-size: 13px;
        }
        
        /* Tablet / layar sedang */
        @media (max-width: 900px) {
            .container {
                padding: 20px 16px 28px;
                border-radius: 18px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .nav {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .role-legend {
                justify-content: flex-start;
            }
        }

        /* HP & iPad: ubah tabel jadi card */
        @media (max-width: 1024px) {
            body {
                padding: 12px;
            }

            .header h1 {
                font-size: 22px;
            }

            .header small {
                font-size: 12px;
            }

            .nav {
                width: 100%;
            }

            .nav a {
                flex: 1 1 auto;
                justify-content: center;
                font-size: 12px;
                padding: 8px 10px;
            }

            .table-wrapper {
                border-radius: 0;
                border: none;
                box-shadow: none;
                background: transparent;
            }

            .table-scroll {
                overflow-x: visible;
            }

            table {
                min-width: 0;
                border-collapse: separate;
                border-spacing: 0 12px; /* jarak antar card */
            }

            thead {
                display: none;
            }

            tbody {
                display: block;
            }

            tr.user-row {
                display: block;
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 8px 22px rgba(0, 0, 0, 0.06);
                margin: 0 0 12px;
                padding: 10px 12px;
            }

            tbody tr:hover {
                transform: translateY(-1px);
            }

            td {
                display: flex;
                padding: 6px 0;
                border: none;
                font-size: 13px;
                align-items: flex-start;
                text-align: left;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 12px;
                color: #9ca3af;
                margin-right: 8px;
                min-width: 90px;
                max-width: 40%;
            }

            td.cell-id {
                align-items: center;
            }

            td.cell-id::before {
                min-width: auto;
                margin-right: 6px;
            }

            .action-cell {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .inline-form {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .inline-form .action-input,
            .inline-form .action-select {
                width: 100%;
            }

            .action-cell button,
            .action-cell form,
            .action-cell form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="header-left">
            <h1>👥 Kelola User</h1>
            <small>Atur akun, peran, dan akses pengguna aplikasi Anda.</small>
        </div>
        <div class="nav">
            <a href="admin_products.php">
                <span class="icon">🛒</span>
                <span>Kelola Produk</span>
            </a>
            <a href="admin_transactions.php">
                <span class="icon">💳</span>
                <span>Kelola Transaksi</span>
            </a>
            <a href="admin_users.php" class="active">
                <span class="icon">👥</span>
                <span>User</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="icon">🚪</span>
                <span>Logout</span>
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

<script>
    // Pencarian realtime
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const table = document.getElementById('userTable').getElementsByTagName('tbody')[0];

    function filterTable() {
        const filter = searchInput.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');
        let hasFilter = filter.trim() !== '';

        clearSearch.style.display = hasFilter ? 'block' : 'none';

        for (let i = 0; i < rows.length; i++) {
            const usernameCell = rows[i].getElementsByTagName('td')[1];
            if (!usernameCell) continue;

            const text = usernameCell.textContent || usernameCell.innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            filterTable();
            searchInput.focus();
        });
    }

    // Auto hide alert setelah beberapa detik
    const alertBox = document.getElementById('alertBox');
    if (alertBox) {
        setTimeout(() => {
            if (alertBox) alertBox.style.display = 'none';
        }, 4000);
    }
</script>
</body>
</html>

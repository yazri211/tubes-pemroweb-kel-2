<?php
include 'auth_admin.php';

// daftar kategori (sinkron dengan home.php)
$categories = [
    'makeup' => 'Makeup',
    'skincare' => 'Skincare',
    'haircare' => 'Haircare',
    'bodycare' => 'Bodycare',
    'nailcare' => 'Nailcare',
    'fragrance' => 'Fragrance'
];

// TAMBAH PRODUK
if (isset($_POST['tambah_produk'])) {
    $name = htmlspecialchars($_POST['name']);
    $price = intval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    if (!array_key_exists($category, $categories)) $category = '';
    $image = "produk.jpg"; // Default image
    
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $file['size'] <= 2000000) {
            $image = time() . "_" . $file['name'];
            move_uploaded_file($file['tmp_name'], '../assets/' . $image);
        }
    }
    
    if ($name && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $name, $price, $description, $image, $category);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_products.php");
        exit();
    }
}

// UPDATE PRODUK
if (isset($_POST['update_produk'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $price = intval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    if (!array_key_exists($category, $categories)) $category = '';
    
    if ($name && $price > 0) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, category = ? WHERE id = ?");
        $stmt->bind_param("sissi", $name, $price, $description, $category, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_products.php");
        exit();
    }
}

// HAPUS PRODUK
if (isset($_POST['hapus_produk'])) {
    $id = intval($_POST['id']);
    
    $result = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $file = '../assets/' . $row['image'];
        if (file_exists($file)) unlink($file);
    }
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_products.php");
    exit();
}

// LOAD PRODUK
$products = $conn->query("SELECT id, name, price, description, image, category FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #fdf2f8; /* soft pink */
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 32px 40px;
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #fed7e2;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding: 18px 22px;
            border-radius: 18px;
            background: #ec4899; /* pink solid */
            color: white;
            box-shadow: 0 12px 28px rgba(236, 72, 153, 0.45);
        }
        
        .header h1 {
            font-size: 26px;
            font-weight: 700;
        }

        .header h1 span {
            margin-right: 6px;
        }
        
        .nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .nav a {
            color: #ec4899;
            text-decoration: none;
            padding: 9px 16px;
            border-radius: 999px;
            background: #fdf2f8;
            border: 1px solid #f9a8d4;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.22s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav a:hover {
            background: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(190, 24, 93, 0.15);
        }

        .nav a:last-child {
            background: #fecaca;
            border-color: #f97373;
            color: #b91c1c;
        }
        
        .section-title {
            font-size: 20px;
            margin: 26px 0 16px 0;
            color: #ec4899;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .section-title span.icon {
            font-size: 22px;
        }
        
        .form-group {
            display: grid;
            gap: 18px;
            margin-bottom: 24px;
            padding: 22px 20px 20px;
            background: #fff7fb;
            border-radius: 18px;
            border: 1px solid #fed7e2;
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 32px rgba(236, 72, 153, 0.18);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
        }
        
        input, textarea, select {
            padding: 11px 12px;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', Arial, sans-serif;
            background: #ffffff;
            transition: border 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.18);
            transform: translateY(-1px);
        }
        
        textarea {
            resize: vertical;
            min-height: 90px;
        }
        
        button {
            padding: 11px 20px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.22s ease;
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-primary {
            background: #ec4899;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(236, 72, 153, 0.45);
        }
        
        .btn-update {
            background: #16a34a; /* green solid */
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-update:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(22, 163, 74, 0.45);
        }

        .btn-delete {
            background: #dc2626; /* red solid */
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(220, 38, 38, 0.45);
        }
        
        .btn-edit {
            background: #f97316; /* orange solid */
            color: white;
            padding: 8px 16px;
            font-size: 13px;
        }

        .btn-edit:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(249, 115, 22, 0.45);
        }

        .table-wrapper {
            width: 100%;
            margin-top: 16px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
            background: #fff;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            table-layout: auto;
            min-width: 800px; /* di layar besar masih tabel */
        }
        
        thead {
            background: #ec4899;
        }

        th {
            color: #fdf2f8;
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        
        td {
            padding: 13px 16px;
            border-bottom: 1px solid #f3e8ff;
            vertical-align: top;
            font-size: 13px;
        }

        tr.product-row {
            transition: background 0.15s ease, transform 0.12s ease, box-shadow 0.12s ease;
        }
        
        tr.product-row:nth-child(even) {
            background: #fff9fd;
        }
        
        tr.product-row:hover {
            background: #fdf2f8;
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(236, 72, 153, 0.15);
        }
        
        .product-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 14px;
            border: 2px solid #fecdd3;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .product-img:hover {
            transform: scale(1.06);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
            border-color: #ec4899;
        }

        .description-cell {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .price-badge {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 12px;
            font-weight: 600;
        }

        .category-pill {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 500;
        }
        
        .action-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .edit-form {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.28s ease, opacity 0.26s ease, margin-top 0.26s ease;
        }
        
        .edit-form.show {
            max-height: 280px;
            opacity: 1;
            margin-top: 10px;
        }
        
        .edit-form-inner {
            padding: 12px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .edit-form .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            align-items: flex-start;
        }

        .edit-form textarea {
            min-height: 60px;
        }
        
        /* RESPONSIVE UMUM (tablet besar ke bawah) */
        @media (max-width: 900px) {
            .container {
                padding: 20px 16px 26px;
                border-radius: 18px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .nav {
                flex-wrap: wrap;
            }

            .section-title {
                margin-top: 20px;
            }
        }
        
        /* IPAD & HP => MODE CARD */
        @media (max-width: 1024px) {
            body {
                padding: 12px;
            }

            .header h1 {
                font-size: 22px;
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

            .form-group {
                padding: 16px 12px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            button {
                width: 100%;
            }

            .table-wrapper {
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

            tr.product-row {
                display: block;
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
                padding: 10px 12px;
                margin-bottom: 10px;
                transform: none;
            }

            tr.product-row:hover {
                transform: translateY(-1px);
            }

            td {
                display: flex;
                padding: 6px 0;
                border: none;
                font-size: 13px;
                align-items: flex-start;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 12px;
                color: #9ca3af;
                margin-right: 8px;
                min-width: 80px;
                max-width: 40%;
            }

            td.cell-image {
                justify-content: center;
            }

            td.cell-image::before {
                content: '';
                display: none;
            }

            .product-img {
                width: 80px;
                height: 80px;
                margin-bottom: 4px;
            }

            .action-cell {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .action-cell button,
            .action-cell form button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><span>📦</span>Kelola Produk</h1>
        <div class="nav">
            <a href="admin_users.php">👥 Kelola User</a>
            <a href="admin_transactions.php">💳 Kelola Transaksi</a>
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>
    </div>

    <h2 class="section-title">
        <span class="icon">➕</span>
        <span>Tambah Produk Baru</span>
    </h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <div class="form-row">
                <input type="text" name="name" placeholder="Nama produk" required>
                <input type="number" name="price" placeholder="Harga (Rp)" min="1" required>
            </div>
            <textarea name="description" placeholder="Deskripsi produk (opsional)"></textarea>
            <div class="form-row">
                <select name="category" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="image" accept="image/*">
                <button class="btn-primary" name="tambah_produk">
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>
    </form>

    <h2 class="section-title">
        <span class="icon">📋</span>
        <span>Daftar Produk</span>
    </h2>

    <div class="table-wrapper">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($p = $products->fetch_assoc()) { ?>
                <tr class="product-row">
                    <td class="cell-image" data-label="Gambar">
                        <img src="../assets/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
                    </td>
                    <td data-label="Nama">
                        <?= htmlspecialchars($p['name']) ?>
                    </td>
                    <td data-label="Harga">
                        <span class="price-badge">
                            Rp <?= number_format($p['price'], 0, ',', '.') ?>
                        </span>
                    </td>
                    <td class="description-cell" data-label="Deskripsi">
                        <?= htmlspecialchars($p['description']) ?>
                    </td>
                    <td data-label="Kategori">
                        <span class="category-pill">
                            <?= htmlspecialchars(isset($categories[$p['category']]) ? $categories[$p['category']] : $p['category']) ?>
                        </span>
                    </td>
                    <td data-label="Aksi">
                        <div class="action-cell">
                            <button type="button" class="btn-edit" onclick="toggleEdit(<?= $p['id'] ?>)">Edit</button>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus produk ini?')">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button class="btn-delete" name="hapus_produk">Hapus</button>
                            </form>
                        </div>
                        <div class="edit-form" id="edit-<?= $p['id'] ?>">
                            <div class="edit-form-inner">
                                <form method="post">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <div class="form-row">
                                        <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
                                        <input type="number" name="price" value="<?= $p['price'] ?>" required>
                                        <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>
                                        <select name="category">
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php foreach ($categories as $k => $label): ?>
                                                <option value="<?php echo htmlspecialchars($k); ?>" <?php if ($p['category'] === $k) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-update" name="update_produk">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
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
function toggleEdit(id) {
    const form = document.getElementById('edit-' + id);
    if (!form) return;
    form.classList.toggle('show');
}
</script>

</body>
</html>

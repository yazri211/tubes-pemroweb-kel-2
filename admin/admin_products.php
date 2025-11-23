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
    // validate category, fallback to empty string if invalid
    if (!array_key_exists($category, $categories)) $category = '';
    $image = "produk.jpg"; // Default image
    
    // Handle file upload
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

// LOAD PRODUK (tambahkan category)
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
        
        .nav a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .nav a:last-child {
            background: #b43074ff;
        }
        
        .nav a:last-child:hover {
            background: #65143eff;
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
        
        .btn-primary:hover {
            background: #ffb3d9 ;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 107, 181, 0.3);
        }
        
        .btn-update {
            background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%);
            color: white;
            padding: 10px 18px;
            font-size: 14px;
        }
        
        .btn-update:hover {
            background: linear-gradient(135deg, #4cae4c 0%, #5cb85c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(76, 174, 76, 0.3);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #d10000ff 0%, #c9302c 100%);
            color: white;
            padding: 10px 18px;
            font-size: 14px;
        }
        
        .btn-edit {
            background:#5cb85c;
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
            table-layout: auto; /* Agar kolom menyesuaikan konten */
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
            vertical-align: top; /* Agar teks di atas jika ada wrap */
        }
        
        tr:nth-child(even) {
            background: #fff9fb;
        }
        
        tr:hover {
            background: #ffebf2;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ffb3d9;
            transition: transform 0.3s ease;
        }
        
        .product-img:hover {
            transform: scale(1.1);
        }
        
        .description-cell {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .action-cell {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .edit-form {
            display: none;
            width: 100%;
            margin-top: 10px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
            border: 1px solid #ffb3d9;
        }
        
        .edit-form.show {
            display: block;
        }
        
        .edit-form .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .edit-form input, .edit-form textarea, .edit-form select {
            flex: 1;
            min-width: 120px;
        }
        
        .edit-form textarea {
            min-height: 60px;
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
            
            .edit-form .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📦 Kelola Produk</h1>
        <div class="nav">
            <a href="admin_users.php">Kelola User</a>
            <a href="admin_transactions.php">Kelola Transaksi</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <h2 class="section-title">➕ Tambah Produk Baru</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <div class="form-row">
                <input type="text" name="name" placeholder="Nama produk" required>
                <input type="number" name="price" placeholder="Harga (Rp)" min="1" required>
            </div>
            <textarea name="description" placeholder="Deskripsi produk"></textarea>
            <div class="form-row">
                <select name="category" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="image" accept="image/*">
                <button class="btn-primary" name="tambah_produk">Tambah Produk</button>
            </div>
        </div>
    </form>

    <h2 class="section-title">📋 Daftar Produk</h2>
    <table>
        <tr>
            <th>Gambar</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Deskripsi</th>
            <th>Kategori</th>
            <th>Aksi</th>
        </tr>

        <?php while($p = $products->fetch_assoc()) { ?>
        <tr>
            <td>
                <img src="../assets/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
            </td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
            <td class="description-cell"><?= htmlspecialchars($p['description']) ?></td>
            <td><?= htmlspecialchars(isset($categories[$p['category']]) ? $categories[$p['category']] : $p['category']) ?></td>
            <td>
                <div class="action-cell">
                    <button class="btn-edit" onclick="toggleEdit(<?= $p['id'] ?>)">Edit</button>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Yakin hapus produk ini?')">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button class="btn-delete" name="hapus_produk">Hapus</button>
                    </form>
                </div>
                <div class="edit-form" id="edit-<?= $p['id'] ?>">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <div class="form-row">
                            <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
                            <input type="number" name="price" value="<?= $p['price'] ?>" required>
                            <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>
                            <select name="category">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $k => $label): ?>
                                    <option value="<?php echo htmlspecialchars($k); ?>" <?php if ($p['category'] === $k) echo 'selected'; ?>><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-update" name="update_produk">Update</button>
                        </div>
                    </form>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<script>
function toggleEdit(id) {
    const form = document.getElementById('edit-' + id);
    form.classList.toggle('show');
}
</script>

</body>
</html>
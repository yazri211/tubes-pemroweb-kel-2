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
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0; // stok default 0
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
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssi", $name, $price, $description, $image, $category, $stock);
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
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0;
    
    if ($name && $price > 0) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, category = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("sissii", $name, $price, $description, $category, $stock, $id);
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
$products = $conn->query("SELECT id, name, price, description, image, category, stock FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_products.css">
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
                <input type="number" name="stock" placeholder="Stok" min="0" value="0" required>
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
                        <th>Stok</th>
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
                    <td data-label="Stok">
                        <span class="stock-badge"><?= intval($p['stock']) ?></span>
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
                                        <input type="number" name="stock" value="<?= intval($p['stock']) ?>" min="0" required>
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
<script src="js/admin_products.js"></script>
</body>
</html>
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
    $name = mysqli_real_escape_string($conn, htmlspecialchars($_POST['name']));
    $price = intval($_POST['price']);
    $description = mysqli_real_escape_string($conn, htmlspecialchars($_POST['description']));
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    if (!array_key_exists($category, $categories)) $category = '';
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0;
    $image = "produk.jpg"; // default image

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $file['size'] <= 2000000) {
            $image = time() . "_" . $file['name'];
            move_uploaded_file($file['tmp_name'], '../assets/' . $image);
        }
    }

    // VALIDASI WAJIB ISI
    if (empty($name) || $price <= 0 || empty($category) || $stock < 0) {
        header("Location: admin_products.php?error=empty_fields");
        exit();
    }

    $sql = "
        INSERT INTO products (name, price, description, image, category, stock)
        VALUES ('$name', $price, '$description', '$image', '$category', $stock)
    ";
    mysqli_query($conn, $sql);
    header("Location: admin_products.php?status=added");
    exit();

}

// UPDATE PRODUK
if (isset($_POST['update_produk'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, htmlspecialchars($_POST['name']));
    $price = intval($_POST['price']);
    $description = mysqli_real_escape_string($conn, htmlspecialchars($_POST['description']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    if (!array_key_exists($category, $categories)) $category = '';
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0;

    if ($name && $price > 0) {
        $sql = "
            UPDATE products 
            SET name='$name', price=$price, description='$description', 
                category='$category', stock=$stock
            WHERE id=$id
        ";
        mysqli_query($conn, $sql);
        header("Location: admin_products.php");
        exit();
    }
}

// HAPUS PRODUK
if (isset($_POST['hapus_produk'])) {
    $id = intval($_POST['id']);

    $result = mysqli_query($conn, "SELECT image FROM products WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        $file = '../assets/' . $row['image'];
        if (file_exists($file)) unlink($file);
    }

    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: admin_products.php");
    exit();
}

// LOAD PRODUK
$products = mysqli_query($conn, "
    SELECT id, name, price, description, image, category, stock 
    FROM products ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
    <link rel="icon" type="image/png" href="../assets/logo no wm.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_products.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-left">
            <h1><span>📦</span> Kelola Produk</h1>
            <p class="header-subtitle">Kelola inventori produk, harga, stok, dan kategori dengan mudah.</p>
        </div>
        <div class="nav">
            <a href="admin_users.php"><span>👥 Kelola User</span></a>
            <a href="admin_transactions.php"><span>💳 Kelola Transaksi</span></a>
            <a href="../auth/logout.php" class="logout"><span>🚪 Logout</span></a>
        </div>
    </div>

    <h2 class="section-title"><span class="icon">➕</span>Tambah Produk Baru</h2>
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
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="stock" placeholder="Stok" min="0" value="0" required>
                <input type="file" name="image" accept="image/*">
                <button class="btn-primary" name="tambah_produk">Tambah Produk</button>
            </div>
        </div>
    </form>

    <h2 class="section-title"><span class="icon">📋</span>Daftar Produk</h2>

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
                <?php while($p = mysqli_fetch_assoc($products)) { ?>
                    <tr class="product-row">
                        <td><img src="../assets/<?= htmlspecialchars($p['image']) ?>" class="product-img"></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>Rp <?= number_format($p['price'],0,',','.') ?></td>
                        <td><?= intval($p['stock']) ?></td>
                        <td><?= htmlspecialchars($p['description']) ?></td>
                        <td><?= htmlspecialchars($categories[$p['category']] ?? $p['category']) ?></td>
                        <td>
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
                                        <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
                                        <input type="number" name="price" value="<?= $p['price'] ?>" required>
                                        <input type="number" name="stock" value="<?= intval($p['stock']) ?>" required>
                                        <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>
                                        <select name="category">
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php foreach ($categories as $k => $label): ?>
                                                <option value="<?= $k ?>" <?= $p['category']==$k?'selected':'' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn-update" name="update_produk">Simpan</button>
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

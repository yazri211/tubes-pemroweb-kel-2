<?php

include '../conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


$query = "
    SELECT cart.id AS cart_id, cart.quantity,
    products.id AS product_id, products.name, products.price, products.image, products.stock
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = $user_id
";
$result = mysqli_query($conn, $query);
if ($result === false) {
    die("Query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #ffe6f2;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 10px;
    }
    h2 {
        color: #d63384;
        text-align: center;
    }
    a.button {
        text-decoration: none;
        color: white;
        background: #ff66b3;
        padding: 8px 14px;
        border-radius: 5px;
        font-weight: bold;
    }
    a.button:hover { background: #ff4da6; }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    th, td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ffcce6;
    }
    th {
        background: #ffb3d9;
        color: #660033;
        font-size: 15px;
    }
    tr:hover { background: #ffe6f7; }
    button {
        background: #ff66b3;
        color: white;
        border: none;
        padding: 6px 10px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 14px;
    }
    button:hover { background: #ff3385; }
    input[type="number"] {
        padding: 5px;
        border: 1px solid #ff99cc;
        border-radius: 5px;
        width: 60px;
    }
    img.product-thumb {
        width:80px;
        height:auto;
        display:block;
        margin: 0 auto;
    }
    .actions { display:flex; gap:8px; justify-content:center; align-items:center; }
    /* Responsif untuk HP */
    @media screen and (max-width: 768px) {
        .container { padding: 8px; }
        table, thead, tbody, th, td, tr { display: block; }
        th { display: none; }
        tr {
            margin-bottom: 14px;
            border: 1px solid #ffb3d9;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }
        td {
            border: none;
            margin: 6px 0;
            text-align: left;
            position: relative;
            padding-left: 110px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        td:before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: bold;
            color: #d63384;
            width: 90px;
        }
        img.product-thumb { width: 90px !important; flex: 0 0 90px; }
        .actions { flex-direction: column; gap:6px; width:120px; }
        input[type="number"] { width: 100%; max-width:120px; }
        button, a.button { width: 100%; margin-top: 6px; display:block; text-align:center; }
    }
</style>
</head>
<body>

<div class="container">
    <a class="button" href="../home.php">home</a>
    <h2>Keranjang Belanja</h2>

<?php if (mysqli_num_rows($result) == 0): ?>
    <p>Keranjang kosong.</p>
<?php else: ?>

<table class="cart-table">
    <tr>
        <th>Pilih</th>
        <th>Gambar</th>
        <th>Nama Produk</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Total</th>
        <th>Aksi</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>
        <td data-label="Pilih">
            <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($row['cart_id']) ?>" form="checkoutForm">
        </td>

        <td data-label="Gambar">
            <?php if (!empty($row['image'])): ?>
                <a href="../detail_produk.php?id=<?= urlencode((int)$row['product_id']) ?>">
                    <img class="product-thumb" src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                </a>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>

        <td data-label="Nama Produk">
            <a href="../detail_produk.php?id=<?= urlencode((int)$row['product_id']) ?>" style="color:#d63384; font-weight:bold;">
                <?= htmlspecialchars($row['name']) ?>
            </a>
        </td>

        <td data-label="Harga">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>

        <td data-label="Jumlah">
            <form action="edit_cart.php" method="POST" style="display:inline-block; margin:0;">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                <input type="number" name="quantity" value="<?= htmlspecialchars($row['quantity']) ?>" min="1" max="<?= htmlspecialchars((int)$row['stock']) ?>" title="Maksimal stok: <?= htmlspecialchars((int)$row['stock']) ?>">
                <button type="submit">Update</button>
            </form>
            <small style="color:#999;">Stok: <?= htmlspecialchars((int)$row['stock']) ?></small>
        </td>

        <td data-label="Total">Rp <?= number_format($row['price'] * $row['quantity'], 0, ',', '.') ?></td>

        <td data-label="Aksi">
            <form action="delete_cart.php" method="POST" onsubmit="return confirm('Hapus item dari keranjang?');" style="display:inline-block; margin:0;">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>

<form id="checkoutForm" action="../checkout/checkout.php" method="POST">
    <button type="submit" style="width:200px; display:block; margin:0 auto;">Checkout</button>
</form>

<?php endif; ?>

</body>
</html>

<?php
// tidak perlu menutup koneksi di sini jika akan digunakan lebih lanjut
mysqli_free_result($result);
?>
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
    products.id AS product_id, products.name, products.price, products.image
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
    .home-wrap { text-align: left; margin: 8px 0 12px; }
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
        background: rgba(255, 179, 217, 1);
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
    .cart-summary { display:none; }
    form.inline-form { display:inline-flex; gap:8px; align-items:center; }
    form.inline-form input[type="number"] { width:70px; }
    /* Responsif untuk HP */
    @media screen and (max-width: 768px) {
        .container { padding: 8px; }
        .home-wrap { text-align: left; }
        table, thead, tbody, th, td, tr { display: block; }
        th { display: none; }
        /* hide the header row entirely (prevent empty rounded block) */
        .cart-table thead,
        .cart-table tr:first-child { display: none; height: 0; margin: 0; padding: 0; }
        tr {
            margin-bottom: 14px;
            border: 1px solid #ffb3d9;
            border-radius: 10px;
            padding: 10px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: block;
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
            flex-wrap: wrap;
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
          /* Keep the top home button compact and left-aligned on mobile */
          .home-wrap a.button { display:inline-block; width:auto; margin:6px 0; }

          /* Remove table-level background/rounded corners on small screens so
              individual rows act as cards and no empty rounded bars appear */
          .cart-table { background: transparent; border-radius: 0; box-shadow: none; margin-top: 0; border: none; }
    }
</style>
</head>
<body>

<div class="container">
    <h2>Keranjang Belanja</h2>
    <div class="home-wrap">
        <a class="button" href="../home.php">home</a>
    </div>

<?php if (mysqli_num_rows($result) == 0): ?>
    <p>Keranjang kosong.</p>
<?php else: ?>

<?php $grand_total = 0; ?>

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
            <form action="edit_cart.php" method="POST" class="inline-form">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                <input type="number" name="quantity" value="<?= htmlspecialchars($row['quantity']) ?>" min="1">
                <button type="submit">Update</button>
            </form>
        </td>

        <?php $row_total = $row['price'] * $row['quantity']; $grand_total += $row_total; ?>
        <td data-label="Total">Rp <?= number_format($row_total, 0, ',', '.') ?></td>

        <td data-label="Aksi">
            <form action="delete_cart.php" method="POST" onsubmit="return confirm('Hapus item dari keranjang?');" class="inline-form">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>

<!-- Desktop summary -->
<div class="cart-summary-desktop" style="margin-top:16px; display:flex; justify-content:flex-end; gap:12px; align-items:center;">
    <div style="font-weight:700; color:#d63384;">Total: Rp <?= number_format($grand_total,0,',','.') ?></div>
    <form id="checkoutForm" action="../checkout/checkout.php" method="POST">
        <button type="submit" class="checkout-btn">Checkout</button>
    </form>
</div>

<!-- Mobile fixed summary bar (only visible on small screens) -->
<div class="cart-summary" aria-hidden="false">
    <div style="flex:1">
        <div class="small">Total</div>
        <div class="total">Rp <?= number_format($grand_total,0,',','.') ?></div>
    </div>
    <form id="checkoutForm" action="../checkout/checkout.php" method="POST">
        <button type="submit" class="checkout-btn">Checkout</button>
    </form>
</div>
<?php endif; ?>

</body>
</html>

<?php
// tidak perlu menutup koneksi di sini jika akan digunakan lebih lanjut
mysqli_free_result($result);
?>



<?php

include '../conn.php';
session_start();

// ambil user_id dengan pola yang sama seperti di home
$user_id = null;

if (!empty($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
} elseif (!empty($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    $user_id = (int) $_SESSION['user']['id'];
}

// kalau tetap tidak ada, paksa login
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit();
}

// SEKARANG query pakai $user_id
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cart.css">
</head>
<body>

<div class="container">
    <div class="page-header">
        <h2>
            Keranjang Belanja
            <span class="badge">Live Update</span>
        </h2>
        <div class="home-wrap">
            <a class="btn-base btn-ghost btn-pill" href="../home.php">
                <span style="font-size:0.875rem;">⟵</span>
                <span>Kembali</span>
            </a>
        </div>
    </div>

<?php if (mysqli_num_rows($result) == 0): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <h3>Keranjangmu masih kosong</h3>
        <p>Tambahkan produk ke keranjang untuk mulai belanja.</p>
        <a href="../home.php" class="btn-base btn-primary btn-pill">Lihat Produk</a>
    </div>
<?php else: ?>

<div class="table-responsive">
    <table class="cart-table">
        <thead>
        <tr>
            <th>
                <!-- hanya muncul di layar besar / tablet -->
                <div style="display:inline-flex;align-items:center;gap:0.375rem;font-weight:500;">
                    <input type="checkbox" id="selectAllDesktop" style="cursor:pointer;">
                    <span style="cursor:default;">Pilih semua</span>
                </div>
            </th>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <?php $row_total = $row['price'] * $row['quantity']; ?>
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
                    <span class="sub-text">Tidak ada gambar</span>
                <?php endif; ?>
            </td>
            <td data-label="Nama Produk">
                <a href="../detail_produk.php?id=<?= urlencode((int)$row['product_id']) ?>" class="product-link">
                    <span><?= htmlspecialchars($row['name']) ?></span>
                    <span class="tag">Detail</span>
                </a>
            </td>
            <td data-label="Harga">
                <div class="price-text">Rp <?= number_format($row['price'], 0, ',', '.') ?></div>
                <div class="sub-text">/ pcs</div>
            </td>
            <td data-label="Jumlah">
                <form action="edit_cart.php" method="POST" class="inline-form">
                    <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                    <input type="number" name="quantity" value="<?= htmlspecialchars($row['quantity']) ?>" min="1">
                    <button type="submit" class="btn-base btn-ghost btn-small btn-pill">
                        Simpan
                    </button>
                </form>
            </td>
            <td data-label="Total">
                <div class="price-text">Rp <?= number_format($row_total, 0, ',', '.') ?></div>
            </td>
            <td data-label="Aksi">
                <form action="delete_cart.php" method="POST" onsubmit="return confirm('Hapus item dari keranjang?');" class="inline-form">
                    <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                    <button type="submit" class="btn-base btn-danger btn-small btn-pill">
                        Hapus
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- PILIH SEMUA VERSI MOBILE (muncul hanya di max-width: 480px) -->
<div class="select-all-mobile">
    <label for="selectAllMobile">
        <input type="checkbox" id="selectAllMobile">
        Pilih semua
    </label>
</div>

<br>

<form id="checkoutForm" action="../checkout/checkout.php" method="POST">
    <input type="hidden" name="selectedIds" id="selectedIds">
</form>

<div class="cart-summary-desktop">
    <div class="total-pill">
        <div class="label">Total pesanan</div>
        <div id="totalDisplayDesktop" class="amount">Rp 0</div>
    </div>
    <button type="submit" class="checkout-btn" form="checkoutForm">
        <span class="icon">🧾</span>
        <span>Pesan sekarang</span>
    </button>
</div>

<div class="cart-summary" aria-hidden="false">
    <div style="flex:1">
        <div class="small">Total pesanan</div>
        <div id="totalDisplayMobile" class="total">Rp 0</div>
    </div>
    <button type="submit" class="checkout-btn" form="checkoutForm">
        <span class="icon">🧾</span>
        <span>Pesan</span>
    </button>
</div>

<?php endif; ?>
</div>

<script src="cart.js"></script>

</body>
</html>

<?php
mysqli_free_result($result);
?>

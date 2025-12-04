<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: order_history.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

$q = "
    SELECT id, total, metode_pembayaran, alamat, pengiriman, ongkir, admin_fee, status, created_at 
    FROM transactions 
    WHERE id = {$order_id} AND user_id = {$user_id}
    LIMIT 1
";
$query = mysqli_query($conn, $q);
if (!$query) {
    die("Query error: " . mysqli_error($conn));
}
$order = mysqli_fetch_assoc($query);

if (!$order) {
    header("Location: order_history.php");
    exit();
}

// Ambil item transaksi
$items_q = mysqli_query($conn, "
    SELECT product_id, product_name, price, quantity, subtotal
    FROM transaction_items
    WHERE transaction_id = {$order_id}
");
if ($items_q === false) {
    die("Query error: " . mysqli_error($conn));
}

// Hitung subtotal produk
$subtotal_produk = 0;
$items = [];
while ($r = mysqli_fetch_assoc($items_q)) {
    $r['price'] = (float)$r['price'];
    $r['quantity'] = (int)$r['quantity'];
    $r['subtotal'] = (float)$r['subtotal'];
    $subtotal_produk += $r['subtotal'];
    $items[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Detail Transaksi #<?= htmlspecialchars($order['id']) ?></title>
    <link rel="icon" type="image/png" href="../assets/logo no wm.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="order_detail.css?v=<?= time() ?>">
</head>
<body>
<div class="container" role="main" aria-labelledby="title">
    <div class="header">
        <div class="header-left">
            <h1 id="title">Detail Transaksi #<?= htmlspecialchars($order['id']) ?></h1>
            <small>Ringkasan pesanan dan penilaian produk Anda.</small>
        </div>
        <div>
            <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                <?= ucfirst(htmlspecialchars($order['status'])) ?>
            </span>
        </div>
    </div>

    <div class="info-box" role="region" aria-label="Informasi transaksi">
        <div class="info-title">Informasi Transaksi</div>
        <div class="info-row">
            <div class="info-item">
                <label>Tanggal Transaksi</label>
                <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></span>
            </div>
            <div class="info-item">
                <label>Metode Pembayaran</label>
                <span><?= htmlspecialchars($order['metode_pembayaran']) ?></span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-item">
                <label>Jenis Pengiriman</label>
                <span><?= htmlspecialchars($order['pengiriman']) ?></span>
            </div>
            <div class="info-item">
                <label>Ongkir</label>
                <span>Rp <?= number_format((float)$order['ongkir'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="info-item">
            <label>Alamat Pengiriman</label>
            <span><?= nl2br(htmlspecialchars($order['alamat'])) ?></span>
        </div>
    </div>

    <h2 class="section-title">
        <span class="icon">📦</span>
        <span>Detail Produk</span>
    </h2>

    <table role="table" aria-label="Daftar produk">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): 
            $prod_id = intval($it['product_id']);
            $price_fmt = number_format($it['price'], 0, ',', '.');
            $subtotal_fmt = number_format($it['subtotal'], 0, ',', '.');
        ?>
            <tr>
                <td data-label="Nama Produk"><?= htmlspecialchars($it['product_name']) ?></td>
                <td data-label="Harga">Rp <?= $price_fmt ?></td>
                <td data-label="Jumlah"><?= (int)$it['quantity'] ?></td>
                <td data-label="Subtotal">Rp <?= $subtotal_fmt ?></td>
            </tr>

            <?php if ($order['status'] === 'completed' || $order['status'] === 'selesai'): ?>
            <tr>
                <td colspan="4" style="background:transparent; border-bottom:none; padding-top:4px;">
                    <?php
                    $safe_user = intval($user_id);
                    $safe_prod = intval($prod_id);
                    $check_sql = "SELECT rating FROM product_reviews WHERE user_id = {$safe_user} AND product_id = {$safe_prod} AND transaction_id = {$order_id} LIMIT 1";
                    $check_q = mysqli_query($conn, $check_sql);
                    if ($check_q && mysqli_num_rows($check_q) > 0) :
                        $rv = mysqli_fetch_assoc($check_q);
                        $rating = intval($rv['rating']);
                        echo '<strong>Rating Anda:</strong> ' .
                             str_repeat('★', $rating) .
                             str_repeat('☆', 5 - $rating);
                    else :
                    ?>
                        <form class="rating-form" action="../review_add.php" method="POST" aria-label="Formulir rating">
                            <input type="hidden" name="product_id" value="<?= $prod_id ?>">
                            <input type="hidden" name="transaction_id" value="<?= $order_id ?>">
                            <label for="rating-<?= $prod_id ?>" style="font-weight:700;color:var(--pink-mid);font-size:13px;">Berikan Rating:</label>
                            <select id="rating-<?= $prod_id ?>" name="rating" required>
                                <option value="">Pilih Bintang</option>
                                <option value="5">★★★★★ (5)</option>
                                <option value="4">★★★★☆ (4)</option>
                                <option value="3">★★★☆☆ (3)</option>
                                <option value="2">★★☆☆☆ (2)</option>
                                <option value="1">★☆☆☆☆ (1)</option>
                            </select>
                            <button type="submit">Kirim Rating</button>
                        </form>
                    <?php
                    endif;
                    ?>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary" aria-label="Ringkasan pembayaran">
        <div class="summary-row">
            <span>Subtotal Produk:</span>
            <span>Rp <?= number_format($subtotal_produk, 0, ',', '.') ?></span>
        </div>
        <div class="summary-row">
            <span>Ongkir:</span>
            <span>Rp <?= number_format((float)$order['ongkir'], 0, ',', '.') ?></span>
        </div>
        <div class="summary-row">
            <span>Admin Fee:</span>
            <span>Rp <?= number_format((float)$order['admin_fee'], 0, ',', '.') ?></span>
        </div>
        <div class="summary-row total">
            <span>Total Pembayaran:</span>
            <span>Rp <?= number_format((float)$order['total'], 0, ',', '.') ?></span>
        </div>
    </div>

    <a href="order_history.php" class="btn">← Kembali ke Riwayat Transaksi</a>
</div>
</body>
</html>

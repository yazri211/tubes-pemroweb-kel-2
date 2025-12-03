<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../conn.php';

// Helper untuk menampilkan halaman "tidak ada produk" (se-tema)
function no_product_page($msg = "Tidak ada produk yang dipilih.") {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Tidak Ada Produk</title>
        <link rel="stylesheet" href="checkout.css">
    </head>
    <body>
        <div class="card" role="alert" aria-live="assertive">
            <div class="emoji">🛒</div>
            <h2><?= htmlspecialchars($msg) ?></h2>
            <p><a class="btn" href="../cart/cart.php">← Kembali ke Keranjang</a></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// MODE 1: Single product via query string (beli sekarang dari katalog)
$single_product = null;
if (isset($_GET['product_id'])) {
    $pid = intval($_GET['product_id']);
    if ($pid <= 0) no_product_page("ID produk tidak valid.");

    $res = mysqli_query($conn, "SELECT id AS product_id, name, price, stock FROM products WHERE id = $pid LIMIT 1");
    if (!$res || mysqli_num_rows($res) === 0) {
        no_product_page("Produk tidak ditemukan.");
    }
    $row = mysqli_fetch_assoc($res);
    $single_product = [
        'product_id' => (int)$row['product_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'quantity' => 1
    ];
}

// MODE 2: Cart mode (form posted or cart_ids param)
$cart_items = [];
if (!$single_product) {
    $selected = [];
    if (isset($_POST['selected']) && is_array($_POST['selected']) && count($_POST['selected'])>0) {
        $selected = array_map('intval', $_POST['selected']);
    } elseif (isset($_POST['cart_ids']) && trim($_POST['cart_ids']) !== '') {
        $tmp = explode(',', $_POST['cart_ids']);
        $selected = array_map('intval', $tmp);
    } elseif (isset($_GET['cart_ids']) && trim($_GET['cart_ids']) !== '') {
        $tmp = explode(',', $_GET['cart_ids']);
        $selected = array_map('intval', $tmp);
    }

    $selected = array_filter($selected, function($v){ return $v > 0; });

    if (count($selected) > 0) {
        $ids_csv = implode(',', $selected);
        $sql = "
            SELECT cart.id AS cart_id, cart.quantity, cart.product_id,
                   products.name, products.price
            FROM cart
            JOIN products ON cart.product_id = products.id
            WHERE cart.id IN ($ids_csv)
        ";
        $query = mysqli_query($conn, $sql);
        if ($query === false) {
            die("Query error: " . mysqli_error($conn));
        }
        while ($r = mysqli_fetch_assoc($query)) {
            $cart_items[] = [
                'cart_id' => (int)$r['cart_id'],
                'product_id' => (int)$r['product_id'],
                'name' => $r['name'],
                'price' => (float)$r['price'],
                'quantity' => (int)$r['quantity']
            ];
        }
    }
}

// If neither single_product nor cart_items -> show friendly message
if (!$single_product && count($cart_items) === 0) {
    no_product_page("Tidak ada produk untuk checkout.");
}

// compute totals
$total_produk = 0;
$items_for_render = [];
if ($single_product) {
    $items_for_render[] = $single_product;
    $total_produk = $single_product['price'] * $single_product['quantity'];
} else {
    foreach ($cart_items as $it) {
        $items_for_render[] = $it;
        $total_produk += $it['price'] * $it['quantity'];
    }
}

// prepare cart_ids csv for form (if any)
$cart_ids_csv = '';
if (!$single_product) {
    $cart_ids = array_map(function($i){ return intval($i['cart_id']); }, $cart_items);
    $cart_ids_csv = implode(',', $cart_ids);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Checkout - Beauty Shop</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
<div class="checkout-shell" role="main" aria-labelledby="checkoutTitle">
    <header class="checkout-header">
        <div>
            <span class="pill-step">
                Checkout • Beauty Shop
            </span>
            <h1 class="checkout-title" id="checkoutTitle">Ringkasan Pembayaran</h1>
            <p class="checkout-subtitle">Cek kembali produk, alamat, dan metode pembayaran sebelum kamu membuat pesanan.</p>
        </div>
    </header>

    <!-- NOTE: removed 'novalidate' so HTML5 required validation aktif -->
    <form action="checkout_process.php" method="POST">
        <?php if ($single_product): ?>
            <input type="hidden" name="mode" value="single">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($single_product['product_id']) ?>">
            <input type="hidden" name="quantity" value="<?= htmlspecialchars($single_product['quantity']) ?>">
        <?php else: ?>
            <input type="hidden" name="mode" value="cart">
            <input type="hidden" name="cart_ids" value="<?= htmlspecialchars($cart_ids_csv) ?>">
        <?php endif; ?>

        <input type="hidden" id="baseTotal" value="<?= htmlspecialchars($total_produk) ?>">

        <div class="checkout-layout">
            <!-- LEFT COLUMN -->
            <section>
                <h3 class="section-title"><span class="emoji">📋</span> Ringkasan Produk</h3>

                <!-- desktop table -->
                <table class="product-table" role="table" aria-label="Ringkasan produk">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items_for_render as $it):
                        $sub = $it['price'] * $it['quantity'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($it['name']) ?></td>
                            <td>Rp <?= number_format($it['price'],0,',','.') ?></td>
                            <td><?= (int)$it['quantity'] ?></td>
                            <td>Rp <?= number_format($sub,0,',','.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- mobile cards -->
                <?php foreach ($items_for_render as $it):
                    $sub = $it['price'] * $it['quantity'];
                ?>
                    <div class="item-card" aria-hidden="false">
                        <div class="row">
                            <div>
                                <div class="name"><?= htmlspecialchars($it['name']) ?></div>
                                <div class="meta">
                                    Rp <?= number_format($it['price'],0,',','.') ?> • Qty <?= (int)$it['quantity'] ?>
                                </div>
                            </div>
                            <div class="sub">Rp <?= number_format($sub,0,',','.') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="field-group">
                    <div class="field-label">
                        Metode Pembayaran
                        <span class="small">Pilih cara kamu membayar pesanan.</span>
                    </div>
                    <select name="metode_pembayaran" required aria-required="true">
                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="COD">COD (Bayar di Tempat)</option>
                    </select>
                </div>

                <div class="field-group">
                    <div class="field-label">
                        Alamat Pengiriman
                        <span class="small">Tulis detail lengkap (jalan, blok, patokan, dll).</span>
                    </div>
                    <textarea name="alamat" placeholder="Masukkan alamat pengiriman lengkap Anda..." required aria-required="true"></textarea>
                </div>

                <div class="field-group">
                    <div class="field-label">
                        Jenis Pengiriman
                        <span class="small">Ongkir akan menyesuaikan layanan yang kamu pilih.</span>
                    </div>
                    <select name="pengiriman" id="pengiriman" onchange="updateTotal()" required aria-required="true">
                        <option value="">-- Pilih Jenis Pengiriman --</option>
                        <option value="Reguler|20000">Reguler (+ Rp 20.000) - 3–5 hari</option>
                        <option value="Express|35000">Express (+ Rp 35.000) - 1–2 hari</option>
                        <option value="Kargo|50000">Kargo (+ Rp 50.000) - Same Day</option>
                    </select>
                    <p class="muted" style="margin-top:4px;">Biaya administrasi tetap Rp 5.000 untuk setiap transaksi.</p>
                </div>
            </section>

            <!-- RIGHT COLUMN / SUMMARY -->
            <aside>
                <div class="summary-card" aria-label="Rincian biaya" aria-live="polite">
                    <div class="summary-header">
                        <div class="summary-title">Ringkasan Pembayaran</div>
                        <span class="summary-tag">Aman • Terlindungi</span>
                    </div>

                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Subtotal Produk</span>
                            <span id="subtotalDisplay">Rp <?= number_format($total_produk,0,',','.') ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Ongkos Kirim</span>
                            <span id="ongkirDisplay">Rp 0</span>
                        </div>
                        <div class="summary-row">
                            <span>Administrasi</span>
                            <span id="adminDisplay">Rp 5.000</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Pembayaran</span>
                            <span id="finalTotal">Rp <?= number_format($total_produk + 5000,0,',','.') ?></span>
                        </div>
                    </div>

                    <input type="hidden" name="total_final" id="total_final_input"
                           value="<?= htmlspecialchars($total_produk + 5000) ?>">

                    <button type="submit" class="btn-primary">
                        <span class="icon">💳</span>
                        <span>Buat Pesanan &amp; Bayar</span>
                    </button>

                    <a href="<?= $single_product ? '../' : '../cart/cart.php' ?>" class="btn-back">
                        <span>←</span>
                        <span>Kembali ke <?= $single_product ? 'Katalog' : 'Keranjang' ?></span>
                    </a>
                </div>
            </aside>
        </div>
    </form>
</div>
<script src="checkout.js"></script>
</body>
</html>

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
        <style>
            :root{
                --bg:#ffdee9;
                --bg2:#b5fffc;
                --pink-mid:#ff6aa6;
                --pink-dark:#d63384;
                --card-shadow: 0 18px 45px rgba(0,0,0,0.12);
                --radius:18px;
                --max-w:860px;
                --side-pad:20px;
            }
            *{box-sizing:border-box;margin:0;padding:0}
            html,body{height:100%}
            body{
                font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
                background:
                  radial-gradient(circle at 0% 0%, #ffe3f3 0, transparent 55%),
                  radial-gradient(circle at 100% 100%, #d6f6ff 0, transparent 55%),
                  linear-gradient(135deg, var(--bg), var(--bg2));
                background-size:140% 140%;
                animation:bgMove 18s ease-in-out infinite alternate;
                padding:32px var(--side-pad);
                color:#222;
            }
            @keyframes bgMove{
              0%{background-position:0% 0%,100% 100%,0% 50%;}
              100%{background-position:10% 5%,90% 95%,100% 50%;}
            }
            .card{
                max-width:var(--max-w);
                margin:44px auto;
                background:
                  radial-gradient(circle at 0% 0%, rgba(255,255,255,0.96), rgba(255,255,255,0.90)),
                  linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.92));
                padding:28px 24px;
                border-radius:var(--radius);
                box-shadow:var(--card-shadow);
                text-align:center;
                backdrop-filter:blur(14px);
                border:1px solid rgba(255,255,255,0.8);
            }
            .emoji{font-size:56px;margin-bottom:12px}
            h2{color:var(--pink-dark);margin-bottom:8px;font-size:22px}
            p{color:#666;margin-bottom:18px}
            .btn{
                display:inline-block;
                padding:10px 16px;
                border-radius:999px;
                background:linear-gradient(135deg,#ff6aa6,#ff9cc7);
                color:#fff;
                font-weight:800;
                text-decoration:none;
                transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
                box-shadow:0 14px 30px rgba(255,106,166,0.35);
            }
            .btn:hover{
                transform:translateY(-3px) scale(1.01);
                filter:brightness(1.05);
                box-shadow:0 20px 45px rgba(255,106,166,0.5);
            }
            @media (max-width:520px){
              body{padding:20px var(--side-pad);}
              .card{margin:24px auto;padding:22px 18px;}
            }
        </style>
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

    $stmt = mysqli_prepare($conn, "SELECT id AS product_id, name, price, stock FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $pid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
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
    <style>
        :root{
            --max-w:960px;
            --side-pad:18px;
            --bg1:#ffdee9;
            --bg2:#b5fffc;
            --card-bg:rgba(255,255,255,0.94);
            --pink:#ff6aa6;
            --pink-soft:#ff9cc7;
            --pink-dark:#d63384;
            --muted:#6b6b6b;
            --border-subtle:rgba(255,255,255,0.8);
            --card-shadow:0 18px 45px rgba(0,0,0,0.12);
            --radius:20px;
            --gap:18px;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%}
        body{
            font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
            color:#222;
            background:
              radial-gradient(circle at 0% 0%, #ffe3f3 0, transparent 55%),
              radial-gradient(circle at 100% 100%, #d6f6ff 0, transparent 55%),
              linear-gradient(135deg, var(--bg1), var(--bg2));
            background-size:140% 140%;
            animation:bgMove 18s ease-in-out infinite alternate;
            padding:28px var(--side-pad);
        }
        @keyframes bgMove{
          0%{background-position:0% 0%,100% 100%,0% 50%;}
          100%{background-position:10% 5%,90% 95%,100% 50%;}
        }

        .checkout-shell{
            max-width:var(--max-w);
            margin:0 auto;
            background:
              radial-gradient(circle at 0% 0%, rgba(255,255,255,0.96), rgba(255,255,255,0.90)),
              linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.92));
            border-radius:var(--radius);
            box-shadow:var(--card-shadow);
            padding:26px 26px 24px;
            border:1px solid var(--border-subtle);
            backdrop-filter:blur(16px);
        }

        .checkout-header{
            display:flex;
            align-items:flex-start;
            gap:14px;
            margin-bottom:18px;
        }
        .pill-step{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:4px 10px;
            border-radius:999px;
            background:rgba(255,106,166,0.08);
            color:var(--pink);
            font-size:0.78rem;
            font-weight:700;
        }
        .checkout-title{
            font-size:1.6rem;
            margin:4px 0 4px;
            letter-spacing:0.03em;
            background:linear-gradient(120deg,#ff6aa6,#ff9cc7,#ffa8e6);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
            font-weight:800;
        }
        .checkout-subtitle{
            color:var(--muted);
            font-size:0.95rem;
        }

        .checkout-layout{
            display:grid;
            grid-template-columns:minmax(0,3fr) minmax(0,2.2fr);
            gap:24px;
            margin-top:12px;
        }

        h3.section-title{
            font-size:1rem;
            font-weight:750;
            color:var(--pink-dark);
            margin:12px 0 8px;
            display:flex;
            align-items:center;
            gap:6px;
        }
        h3.section-title span.emoji{
            font-size:1.1rem;
        }

        /* PRODUCTS TABLE */
        .product-table{
            width:100%;
            border-collapse:collapse;
            font-size:0.9rem;
            margin-top:6px;
            border-radius:12px;
            overflow:hidden;
            background:#fff;
        }
        .product-table thead{
            background:linear-gradient(135deg,#ffe1f1,#ffd3ec);
        }
        .product-table th{
            text-align:left;
            padding:10px 12px;
            font-size:0.86rem;
            text-transform:uppercase;
            letter-spacing:0.04em;
            color:var(--pink-dark);
        }
        .product-table td{
            padding:10px 12px;
            border-bottom:1px solid #ffe3f4;
            color:#222;
            vertical-align:middle;
        }

        /* RATA TENGAH KOLOM JUMLAH */
        .product-table th:nth-child(3),
        .product-table td:nth-child(3){
            text-align:center;
            width:72px;
        }

        /* RATA KANAN HARGA & SUBTOTAL */
        .product-table th:nth-child(2),
        .product-table td:nth-child(2),
        .product-table th:nth-child(4),
        .product-table td:nth-child(4){
            text-align:right;
            padding-right:16px;
        }

        .product-table tr:last-child td{border-bottom:none;}
        .product-table tbody tr:hover{
            background:#fff7fb;
        }

        /* MOBILE ITEM CARDS */
        .item-card{
            display:none;
            background:#fff;
            border-radius:14px;
            padding:12px 12px 10px;
            margin-top:8px;
            box-shadow:0 10px 26px rgba(0,0,0,0.04);
        }
        .item-card .row{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:10px;
        }
        .item-card .name{
            font-weight:750;
            color:var(--pink-dark);
            font-size:0.95rem;
        }
        .item-card .meta{
            font-size:0.82rem;
            color:var(--muted);
        }
        .item-card .sub{
            font-weight:750;
            font-size:0.94rem;
        }

        .field-group{
            margin-top:10px;
        }
        .field-label{
            font-size:0.86rem;
            font-weight:650;
            margin-bottom:4px;
            color:#333;
        }
        .field-label span.small{
            font-weight:500;
            color:var(--muted);
            font-size:0.78rem;
        }

        select, textarea{
            width:100%;
            padding:11px 12px;
            border-radius:11px;
            border:1px solid rgba(0,0,0,0.06);
            font-size:0.9rem;
            font-family:inherit;
            background:rgba(255,255,255,0.95);
            transition:border-color .16s ease, box-shadow .16s ease,
                     transform .08s ease, background .16s ease;
        }
        select:focus, textarea:focus{
            outline:none;
            border-color:rgba(0,0,0,0.20);
            box-shadow:0 0 0 1px rgba(0,0,0,0.08),0 7px 16px rgba(0,0,0,0.06);
            transform:translateY(-1px);
            background:#fff;
        }
        textarea{
            min-height:100px;
            resize:vertical;
        }

        .muted{color:var(--muted);font-size:0.86rem;}

        /* SUMMARY CARD */
        .summary-card{
            background:linear-gradient(170deg,#fff8fb 0,#ffe9f6 100%);
            border-radius:16px;
            padding:16px 16px 14px;
            border:1px solid rgba(255,255,255,0.8);
            box-shadow:0 14px 32px rgba(255,106,166,0.22);
            position:sticky;
            top:16px;
        }
        .summary-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:10px;
        }
        .summary-title{
            font-size:0.96rem;
            font-weight:750;
            color:var(--pink-dark);
        }
        .summary-tag{
            padding:3px 9px;
            border-radius:999px;
            font-size:0.74rem;
            background:rgba(255,255,255,0.9);
            color:var(--muted);
            border:1px solid rgba(255,255,255,0.9);
        }
        .summary-rows{
            margin:8px 0 6px;
        }
        .summary-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            font-size:0.9rem;
            margin-bottom:8px;
        }
        .summary-row span:first-child{
            color:var(--muted);
        }
        .summary-row.total{
            padding-top:8px;
            margin-top:6px;
            border-top:2px dashed rgba(255,255,255,0.9);
            font-size:1rem;
            font-weight:800;
            color:var(--pink-dark);
        }

        .btn-primary{
            width:100%;
            margin-top:10px;
            padding:13px 16px;
            border-radius:999px;
            border:none;
            cursor:pointer;
            background:linear-gradient(135deg,var(--pink),var(--pink-soft));
            color:#fff;
            font-weight:800;
            font-size:0.98rem;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:6px;
            box-shadow:0 16px 34px rgba(255,106,166,0.45);
            transition:transform .16s cubic-bezier(.16,1,.3,1),
                       box-shadow .16s ease,
                       filter .16s ease;
        }
        .btn-primary span.icon{
            font-size:1.1rem;
        }
        .btn-primary:hover{
            transform:translateY(-2px) scale(1.01);
            box-shadow:0 22px 44px rgba(255,106,166,0.6);
            filter:brightness(1.05);
        }
        .btn-primary:active{
            transform:translateY(1px) scale(0.98);
            box-shadow:0 10px 26px rgba(255,106,166,0.35);
        }

        .btn-back{
            display:inline-flex;
            margin-top:10px;
            width:100%;
            padding:10px 14px;
            border-radius:999px;
            border:1px solid rgba(0,0,0,0.06);
            background:rgba(255,255,255,0.95);
            color:var(--pink-dark);
            text-decoration:none;
            font-weight:700;
            font-size:0.9rem;
            align-items:center;
            justify-content:center;
            gap:6px;
            transition:background .16s ease, transform .12s ease, box-shadow .16s ease;
        }
        .btn-back:hover{
            background:#fff0f8;
            transform:translateY(-1px);
            box-shadow:0 10px 26px rgba(0,0,0,0.06);
        }

        @media (max-width:880px){
            .checkout-layout{
                grid-template-columns:1fr;
                gap:18px;
            }
            .summary-card{position:static;}
        }
        @media (max-width:640px){
            body{padding:20px var(--side-pad);}
            .checkout-shell{padding:20px 18px 18px;border-radius:16px;}
            .checkout-title{font-size:1.35rem;}
            .checkout-header{flex-direction:column;}
            .product-table{display:none;}
            .item-card{display:block;}
        }
        @media (max-width:420px){
            .checkout-shell{padding:18px 14px 16px;}
        }
    </style>
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

    <form action="checkout_process.php" method="POST" novalidate>
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

<script>
function updateTotal() {
    let subtotal = Number(document.getElementById('baseTotal').value) || 0;
    let adminFee = 5000;
    let pengirimanEl = document.getElementById('pengiriman');
    let shippingCost = 0;

    if (pengirimanEl && pengirimanEl.value) {
        let parts = pengirimanEl.value.split("|");
        if (parts.length > 1) {
            shippingCost = Number(parts[1]) || 0;
        }
    }

    let finalTotal = subtotal + shippingCost + adminFee;

    const format = (n) => n.toLocaleString('id-ID');

    const subEl = document.getElementById('subtotalDisplay');
    if (subEl) subEl.innerText = "Rp " + format(subtotal);

    const ongkirDisplay = document.getElementById('ongkirDisplay');
    if (ongkirDisplay) ongkirDisplay.innerText = "Rp " + format(shippingCost);

    const adminDisplay = document.getElementById('adminDisplay');
    if (adminDisplay) adminDisplay.innerText = "Rp " + format(adminFee);

    const finalTotalEl = document.getElementById('finalTotal');
    if (finalTotalEl) finalTotalEl.innerText = "Rp " + format(finalTotal);

    const hidden = document.getElementById('total_final_input');
    if (hidden) hidden.value = finalTotal;
}

document.addEventListener('DOMContentLoaded', function(){
    updateTotal();
});
</script>
</body>
</html>

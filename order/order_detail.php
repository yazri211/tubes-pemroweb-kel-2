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

// Ambil data transaksi (sederhana; bisa diubah ke prepared statement jika diperlukan)
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

    <style>
        :root{
            --max-w:900px;
            --bg: #ffe6f2;
            --pink-light: #ffb3d9;
            --pink-mid: #ff66b3;
            --pink-dark: #d63384;
            --muted: #6b6b6b;
            --card-shadow: 0 6px 20px rgba(255,105,180,0.12);
            --radius: 12px;
            --gap: 16px;
        }

        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;font-family:"Poppins", Arial, sans-serif;background:var(--bg);color:#222;}
        a{color:inherit;text-decoration:none}
        .container{
            max-width:var(--max-w);
            margin:20px auto;
            background:#fff;
            padding:22px;
            border-radius:var(--radius);
            box-shadow:var(--card-shadow);
        }

        /* header */
        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-bottom:18px;
            padding-bottom:14px;
            border-bottom:3px solid var(--pink-light);
        }
        .header h1{font-size:20px;color:var(--pink-dark);font-weight:800}
        .status-badge{
            padding:8px 14px;
            border-radius:999px;
            font-weight:800;
            font-size:13px;
        }
        .status-pending{background:#fff3cd;color:#856404}
        .status-completed{background:#d4edda;color:#155724}
        .status-canceled{background:#f8d7da;color:#721c24}

        .nav a{
            display:inline-block;
            padding:8px 12px;
            border-radius:10px;
            border:2px solid var(--pink-dark);
            color:var(--pink-dark);
            font-weight:700;
            background:#fff;
        }
        .nav a:hover{ background:var(--pink-dark); color:#fff; transform:translateY(-3px); }

        /* info box */
        .info-box{
            background:linear-gradient(180deg,#fff7fa 0%, #fff0f5 100%);
            padding:14px;
            border-radius:12px;
            margin-bottom:14px;
            border-left:6px solid var(--pink-mid);
        }
        .info-row{ display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:10px; }
        .info-row label{display:block; font-weight:700; color:var(--pink-dark); margin-bottom:6px;}
        .info-row span{display:block; color:var(--muted)}

        /* table */
        table{ width:100%; border-collapse:collapse; margin-top:8px; }
        th{
            background:var(--pink-light);
            color:var(--pink-dark);
            text-align:left;
            padding:12px;
            font-weight:800;
            font-size:14px;
        }
        td{ padding:12px 10px; border-bottom:1px solid #ffdfe8; vertical-align:middle; color:#222; }
        tr:hover{ background:#fff0f6; }

        /* rating / form */
        .rating-form { margin-top:8px; display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .rating-form select { padding:8px;border-radius:8px;border:1px solid #ffdfe8; font-weight:700; }
        .rating-form button { padding:8px 12px;border-radius:8px;border:none;background:var(--pink-mid);color:#fff;font-weight:800; cursor:pointer; }
        .rating-form button:hover { background:var(--pink-dark); }

        /* summary */
        .summary{
            background:#fff;
            padding:14px;
            border-radius:12px;
            margin-top:16px;
            box-shadow:0 4px 14px rgba(0,0,0,0.04);
            text-align:right;
        }
        .summary-row{ display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; color:#333; font-weight:700; }
        .summary-row.total{ border-top:3px solid var(--pink-light); padding-top:10px; font-size:18px; color:var(--pink-dark) }

        .btn{
            display:inline-block;
            padding:10px 14px;
            border-radius:12px;
            background:var(--pink-mid);
            color:#fff;
            font-weight:800;
            text-decoration:none;
            transition:transform .14s ease, background .14s ease;
        }
        .btn:hover{ transform:translateY(-3px); background:var(--pink-dark); color:#fff }

        /* responsive */
        @media (max-width:768px){
            .info-row{ grid-template-columns:1fr; }
            table, thead, tbody, th, td, tr{ display:block; width:100% }
            th{ display:none; }
            tr{ background:#fff; margin-bottom:12px; padding:12px; border-radius:10px; box-shadow:var(--card-shadow); }
            td{ border:none; padding:8px 0; display:flex; justify-content:space-between; }
            td:before{ content:attr(data-label); font-weight:800; color:var(--pink-dark); margin-right:8px; width:45%; text-align:left; }
            .summary{text-align:left}
        }
    </style>
</head>
<body>
<div class="container" role="main" aria-labelledby="title">
    <div class="header">
        <h1 id="title">Detail Transaksi #<?= htmlspecialchars($order['id']) ?></h1>
        <div>
            <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span>
        </div>
    </div>

    <div class="info-box" role="region" aria-label="Informasi transaksi">
        <div class="info-row">
            <div>
                <label>Tanggal Transaksi</label>
                <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></span>
            </div>
            <div>
                <label>Metode Pembayaran</label>
                <span><?= htmlspecialchars($order['metode_pembayaran']) ?></span>
            </div>
        </div>

        <div class="info-row">
            <div>
                <label>Jenis Pengiriman</label>
                <span><?= htmlspecialchars($order['pengiriman']) ?></span>
            </div>
            <div>
                <label>Ongkir</label>
                <span>Rp <?= number_format((float)$order['ongkir'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div>
            <label>Alamat Pengiriman</label>
            <span><?= nl2br(htmlspecialchars($order['alamat'])) ?></span>
        </div>
    </div>

    <h2 style="margin:0 0 12px 0; color:var(--pink-dark); font-size:18px;">📦 Detail Produk</h2>

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

            <!-- Rating area: tampilkan sekali per produk jika status completed -->
            <?php if ($order['status'] === 'completed'): ?>
            <tr>
                <td colspan="4" style="background:transparent; border-bottom:none; padding-top:6px;">
                    <?php
                    // Cek apakah user sudah memberi rating untuk produk ini pada transaksi ini
                    $safe_user = intval($user_id);
                    $safe_prod = intval($prod_id);
                    $check_sql = "SELECT rating FROM product_reviews WHERE user_id = {$safe_user} AND product_id = {$safe_prod} AND transaction_id = {$order_id} LIMIT 1";
                    $check_q = mysqli_query($conn, $check_sql);
                    if ($check_q && mysqli_num_rows($check_q) > 0) :
                        $rv = mysqli_fetch_assoc($check_q);
                        $rating = intval($rv['rating']);
                        echo '<strong>Rating Anda:</strong> ' . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                    else :
                    ?>
                        <form class="rating-form" action="../review_add.php" method="POST" aria-label="Formulir rating">
                            <input type="hidden" name="product_id" value="<?= $prod_id ?>">
                            <input type="hidden" name="transaction_id" value="<?= $order_id ?>">
                            <label for="rating-<?= $prod_id ?>" style="font-weight:700;color:var(--pink-dark);">Rating:</label>
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
        <div class="summary-row"><span>Subtotal Produk:</span><span>Rp <?= number_format($subtotal_produk, 0, ',', '.') ?></span></div>
        <div class="summary-row"><span>Ongkir:</span><span>Rp <?= number_format((float)$order['ongkir'], 0, ',', '.') ?></span></div>
        <div class="summary-row"><span>Admin Fee:</span><span>Rp <?= number_format((float)$order['admin_fee'], 0, ',', '.') ?></span></div>
        <div class="summary-row total"><span>Total Pembayaran:</span><span>Rp <?= number_format((float)$order['total'], 0, ',', '.') ?></span></div>
    </div>

    <div style="margin-top:14px;">
        <a href="order_history.php" class="btn">← Kembali ke Riwayat Transaksi</a>
    </div>
</div>
</body>
</html>

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

    <!-- Font modern -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #ff4d94;      /* pink utama */
        --primary-soft: #ffe0ec; /* pink lembut */
        --primary-light: #fff2f7;/* pink sangat lembut */
        --text-main: #111827;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --bg-body: #f3f4f6;
        --radius-lg: 16px;
        --shadow-soft: 0 10px 24px rgba(15, 23, 42, 0.07);
        --transition-fast: 0.18s ease;
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', Arial, sans-serif;
        background: var(--bg-body);
        margin: 0;
        padding: 24px 16px 24px;
        color: var(--text-main);
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 18px 18px 20px;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: var(--shadow-soft);
        position: relative;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    h2 {
        color: var(--text-main);
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.2px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    h2 span.badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-weight: 500;
    }

    .home-wrap { text-align: right; }

    .btn-base {
        border: none;
        outline: none;
        cursor: pointer;
        border-radius: 999px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
        text-decoration: none;
        transition: transform var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast), color var(--transition-fast), border-color var(--transition-fast);
        white-space: nowrap;
    }

    .btn-pill {
        border-radius: 999px;
    }

    .btn-primary {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 8px 18px rgba(255, 77, 148, 0.25);
    }

    .btn-primary:hover {
        background: #e0367f;
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(255, 77, 148, 0.35);
    }

    .btn-ghost {
        background: #ffffff;
        border: 1px solid #d1d5db;
        color: var(--text-main);
    }

    .btn-ghost:hover {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
    }

    .btn-small {
        padding: 6px 10px;
        font-size: 12px;
        box-shadow: none;
    }

    .btn-danger {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #fee2e2;
    }

    .empty-state {
        text-align: center;
        padding: 40px 16px 26px;
    }

    .empty-state-icon {
        width: 82px;
        height: 82px;
        border-radius: 50%;
        background: var(--primary-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 36px;
        color: var(--primary);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.1);
    }

    .empty-state h3 {
        margin: 0 0 6px;
        font-size: 18px;
        color: var(--text-main);
    }

    .empty-state p {
        margin: 0 0 16px;
        font-size: 13px;
        color: var(--text-muted);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-top: 10px;
    }

    .cart-table {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
    }

    /* HEADER TABEL PINK #ff4d94 – dipaksa ke tabel keranjang saja */
    .cart-table thead {
        background: #ff4d94 !important;
    }

    .cart-table thead th {
        background: transparent;
        color: #ffffff;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    th, td {
        padding: 11px 12px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    /* Hover hanya untuk baris data, bukan header */
    .cart-table tbody tr {
        transition: background var(--transition-fast);
    }

    .cart-table tbody tr:hover {
        background: var(--primary-light);
    }

    input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    input[type="number"] {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 999px;
        width: 70px;
        font-size: 12px;
        font-family: inherit;
        text-align: center;
        outline: none;
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast);
        background: #fff;
    }

    input[type="number"]:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 77, 148, 0.25);
        background: #fff2f7;
    }

    img.product-thumb {
        width: 80px;
        height: auto;
        display: block;
        margin: 0 auto;
        border-radius: 10px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.1);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }

    a.product-link {
        color: var(--text-main);
        font-weight: 600;
        text-decoration: none;
        transition: color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    a.product-link:hover {
        color: var(--primary);
    }

    a.product-link span.tag {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .price-text {
        font-weight: 600;
        color: var(--text-main);
    }

    .sub-text {
        font-size: 11px;
        color: var(--text-muted);
    }

    .actions {
        display:flex;
        gap:6px;
        justify-content:center;
        align-items:center;
        flex-wrap: wrap;
    }

    form.inline-form {
        display:inline-flex;
        gap:8px;
        align-items:center;
        justify-content:center;
        flex-wrap: wrap;
    }

    .checkout-btn {
        border-radius: 999px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: 13px;
        background: var(--primary);
        color: #fff;
        border: none;
        cursor:pointer;
        box-shadow: 0 10px 22px rgba(255, 77, 148, 0.28);
        display:inline-flex;
        align-items:center;
        gap: 6px;
        transition: transform var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast);
    }

    .checkout-btn:hover {
        background: #e0367f;
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(255, 77, 148, 0.35);
    }

    .checkout-btn span.icon {
        font-size: 16px;
        line-height: 1;
    }

    .cart-summary-desktop {
        margin-top:16px;
        display:flex;
        justify-content:flex-end;
        gap:16px;
        align-items:center;
        font-size: 14px;
    }

    .cart-summary-desktop .label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .cart-summary-desktop .amount {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .total-pill {
        padding: 8px 14px;
        border-radius: 999px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
    }

    .cart-summary {
        position:fixed;
        left:0;
        right:0;
        bottom:0;
        background:#ffffff;
        border-top:1px solid #e5e7eb;
        padding:8px 14px;
        display:none;
        align-items:center;
        justify-content:space-between;
        box-shadow:0 -10px 26px rgba(15,23,42,0.12);
        z-index:999;
        font-size:13px;
    }
    .cart-summary .small {
        font-size:11px;
        color:var(--text-muted);
        margin-bottom:2px;
    }
    .cart-summary .total {
        font-weight:700;
        color:var(--text-main);
        font-size:15px;
    }

    .cart-summary button.checkout-btn {
        padding: 9px 16px;
        font-size: 13px;
        box-shadow: 0 10px 22px rgba(255, 77, 148, 0.28);
    }

    /* Responsif untuk HP */
    @media screen and (max-width: 768px) {
        body {
            padding: 16px 10px 86px;
        }
        .container {
            padding: 14px 12px 16px;
            border-radius: 14px;
        }
        .page-header {
            align-items: flex-start;
            gap: 6px;
        }
        h2 {
            font-size: 18px;
        }

        .home-wrap {
            text-align: left;
        }

        table, thead, tbody, th, td, tr { display: block; }

        th { display:none; }
        th:first-child {
            display:block;
            border:none;
            background:transparent;
            padding:4px 4px 6px;
            text-align:left;
            position: static;
            color: var(--text-main);
        }

        tr {
            margin-bottom: 12px;
            border-radius: 12px;
            padding: 10px;
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15,23,42,0.08);
            display: block;
            border: 1px solid var(--border);
        }
        td {
            border: none;
            margin: 6px 0;
            text-align: left;
            position: relative;
            padding-left: 106px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 12px;
        }
        td:before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: 600;
            color: var(--text-muted);
            width: 90px;
            font-size: 11px;
        }
        td[data-label="Pilih"] {
            padding-left: 10px;
        }
        td[data-label="Pilih"]::before {
            content: "";
        }

        img.product-thumb {
            width: 90px !important;
            flex: 0 0 90px;
        }

        .actions {
            flex-direction: row;
            gap:6px;
            width:100%;
            justify-content:flex-start;
        }

        input[type="number"] {
            width: 100%;
            max-width:110px;
        }

        .home-wrap .btn-base {
            padding-inline: 12px;
            font-size: 12px;
        }

        .cart-summary-desktop {
            display:none;
        }

        .cart-summary {
            display:flex;
        }
    }

    @media screen and (max-width: 480px) {
        h2 {
            font-size: 17px;
        }
        .empty-state h3 {
            font-size: 16px;
        }
        .empty-state-icon {
            width: 72px;
            height: 72px;
            font-size: 30px;
        }
    }
</style>
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
                <span style="font-size:14px;">⟵</span>
                <span>Kembali</span>
            </a>
        </div>
    </div>

<?php if (mysqli_num_rows($result) == 0): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            🛒
        </div>
        <h3>Keranjangmu masih kosong</h3>
        <p>Tambahkan produk ke keranjang untuk mulai belanja.</p>
        <a href="../home.php" class="btn-base btn-primary btn-pill">
            Lihat Produk
        </a>
    </div>
<?php else: ?>

<table class="cart-table">
    <thead>
    <tr>
        <th>
            <!-- TEKS 'Pilih semua' tidak bisa diklik -->
            <div style="display:inline-flex; align-items:center; gap:6px; font-weight:500;">
                <input type="checkbox" id="selectAll" style="cursor:pointer;">
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

        <?php
            $row_total = $row['price'] * $row['quantity'];
        ?>
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

<script>
function formatRupiah(num) {
    return "Rp " + num.toLocaleString("id-ID");
}

function updateSelectedItemsAndTotal() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    const totalDisplayDesktop = document.getElementById("totalDisplayDesktop");
    const totalDisplayMobile = document.getElementById("totalDisplayMobile");
    const selectedIdsField = document.getElementById("selectedIds");
    const selectAll = document.getElementById("selectAll");

    let total = 0;
    let ids = [];
    let checkedCount = 0;

    checkboxes.forEach(ch => {
        if (ch.checked) {
            checkedCount++;
            const row = ch.closest("tr");
            if (!row) return;

            const hargaCell = row.querySelector('td[data-label="Harga"] .price-text');
            const qtyInput = row.querySelector('input[name="quantity"]');

            if (!hargaCell || !qtyInput) return;

            const priceText = hargaCell.textContent.replace(/[Rp.\s]/g, '');
            const price = parseInt(priceText || "0", 10);
            const qty = parseInt(qtyInput.value || "0", 10);

            if (!isNaN(price) && !isNaN(qty)) {
                total += price * qty;
                ids.push(ch.value);
            }
        }
    });

    if (totalDisplayDesktop) {
        totalDisplayDesktop.textContent = formatRupiah(total);
    }
    if (totalDisplayMobile) {
        totalDisplayMobile.textContent = formatRupiah(total);
    }

    if (selectedIdsField) {
        selectedIdsField.value = ids.join(",");
    }

    if (selectAll) {
        if (checkboxes.length === 0) {
            selectAll.checked = false;
        } else {
            selectAll.checked = (checkedCount === checkboxes.length);
        }
    }
}

document.addEventListener("change", function(e){
    if (e.target.matches('input[name="selected[]"]') || e.target.matches('input[name="quantity"]')) {
        updateSelectedItemsAndTotal();
    }
});

const checkoutForm = document.getElementById("checkoutForm");
if (checkoutForm) {
    checkoutForm.addEventListener("submit", function(e){
        const selectedIdsField = document.getElementById("selectedIds");
        if (!selectedIdsField || selectedIdsField.value.trim() === "") {
            e.preventDefault();
            alert("Silakan pilih minimal 1 produk terlebih dahulu.");
        }
    });
}

const selectAll = document.getElementById("selectAll");
if (selectAll) {
    selectAll.addEventListener("change", function() {
        const checkboxes = document.querySelectorAll('input[name="selected[]"]');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelectedItemsAndTotal();
    });
}

updateSelectedItemsAndTotal();
</script>

</body>
</html>

<?php
mysqli_free_result($result);
?>

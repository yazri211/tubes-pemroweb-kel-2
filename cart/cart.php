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

<style>
:root {
    --primary: #ff4d94;
    --primary-soft: #ffe0ec;
    --primary-light: #fff2f7;
    --text-main: #111827;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --bg-body: #f3f4f6;
    --radius-lg: 1rem;
    --shadow-soft: 0 0.625rem 1.5rem rgba(15, 23, 42, 0.07);
    --transition-fast: 0.18s ease;
}

* { box-sizing: border-box; }

body {
    font-family: 'Poppins', Arial, sans-serif;
    background: var(--bg-body);
    margin: 0;
    padding: 1.5rem 1rem 1.5rem;
    color: var(--text-main);
    font-size: 0.875rem;
}

.container {
    max-width: 68.75rem;
    margin: 0 auto;
    padding: 1.125rem 1.125rem 1.25rem;
    background: #ffffff;
    border-radius: 1.125rem;
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
    border: 0.0625rem solid #e5e7eb;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.125rem;
    flex-wrap: wrap;
}

h2 {
    color: var(--text-main);
    font-size: 1.375rem;
    font-weight: 700;
    letter-spacing: 0.0125rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

h2 span.badge {
    font-size: 0.6875rem;
    padding: 0.25rem 0.5rem;
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
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    justify-content: center;
    text-decoration: none;
    transition: transform var(--transition-fast), box-shadow var(--transition-fast),
                background var(--transition-fast), color var(--transition-fast),
                border-color var(--transition-fast);
    white-space: nowrap;
}

.btn-pill { border-radius: 999px; }

.btn-primary {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 0.5rem 1.125rem rgba(255, 77, 148, 0.25);
}

.btn-primary:hover {
    background: #e0367f;
    transform: translateY(-0.0625rem);
    box-shadow: 0 0.75rem 1.625rem rgba(255, 77, 148, 0.35);
}

.btn-ghost {
    background: #ffffff;
    border: 0.0625rem solid #d1d5db;
    color: var(--text-main);
}

.btn-ghost:hover {
    background: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-0.0625rem);
}

.btn-small {
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
    box-shadow: none;
}

.btn-danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 0.0625rem solid #fecaca;
}

.btn-danger:hover { background: #fee2e2; }

.empty-state {
    text-align: center;
    padding: 2.5rem 1rem 1.625rem;
}

.empty-state-icon {
    width: 5.125rem;
    height: 5.125rem;
    border-radius: 50%;
    background: var(--primary-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.875rem;
    font-size: 2.25rem;
    color: var(--primary);
    box-shadow: 0 0.75rem 1.625rem rgba(15, 23, 42, 0.1);
}

.empty-state h3 {
    margin: 0 0 0.375rem;
    font-size: 1.125rem;
    color: var(--text-main);
}

.empty-state p {
    margin: 0 0 1rem;
    font-size: 0.8125rem;
    color: var(--text-muted);
}

/* TABEL */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
    border-radius: var(--radius-lg);
    margin-top: 0.625rem;
}

.cart-table {
    background: #ffffff;
    border: 0.0625rem solid var(--border);
    border-radius: 0.75rem;
}

.cart-table thead tr { background: var(--primary); }

.cart-table thead th {
    background: none;
    color: #ffffff;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03125rem;
    font-weight: 600;
}

th, td {
    padding: 0.6875rem 0.75rem;
    text-align: center;
    border-bottom: 0.0625rem solid #e5e7eb;
    font-size: 0.8125rem;
}

tr:last-child td { border-bottom: none; }

.cart-table tbody tr { background: #ffffff; }

input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    cursor: pointer;
    accent-color: var(--primary);
}

input[type="number"] {
    padding: 0.375rem 0.5rem;
    border: 0.0625rem solid #d1d5db;
    border-radius: 999px;
    width: 4.375rem;
    font-size: 0.75rem;
    font-family: inherit;
    text-align: center;
    outline: none;
    transition: border-color var(--transition-fast), box-shadow var(--transition-fast),
                background var(--transition-fast);
    background: #fff;
}

input[type="number"]:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.1875rem rgba(255, 77, 148, 0.25);
    background: #fff2f7;
}

img.product-thumb {
    width: 5rem;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 0.625rem;
    box-shadow: 0 0.5rem 1.125rem rgba(15, 23, 42, 0.1);
}

a.product-link {
    color: var(--text-main);
    font-weight: 600;
    text-decoration: none;
    transition: color var(--transition-fast);
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

a.product-link:hover { color: var(--primary); }

a.product-link span.tag {
    font-size: 0.625rem;
    padding: 0.125rem 0.5rem;
    border-radius: 999px;
    background: var(--primary-soft);
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.03125rem;
}

.price-text { font-weight: 600; color: var(--text-main); }

.sub-text { font-size: 0.6875rem; color: var(--text-muted); }

.actions {
    display: flex;
    gap: 0.375rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

form.inline-form {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.checkout-btn {
    border-radius: 999px;
    padding: 0.5rem 1.125rem;
    font-weight: 600;
    font-size: 0.8125rem;
    background: var(--primary);
    color: #fff;
    border: none;
    cursor: pointer;
    box-shadow: 0 0.625rem 1.375rem rgba(255, 77, 148, 0.28);
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: transform var(--transition-fast), box-shadow var(--transition-fast),
                background var(--transition-fast);
}

.checkout-btn:hover {
    background: #e0367f;
    transform: translateY(-0.0625rem);
    box-shadow: 0 0.875rem 1.875rem rgba(255, 77, 148, 0.35);
}

.checkout-btn span.icon {
    font-size: 1rem;
    line-height: 1;
}

.cart-summary-desktop {
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    align-items: center;
    font-size: 0.875rem;
}

.cart-summary-desktop .label {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.cart-summary-desktop .amount {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-main);
}

.total-pill {
    padding: 0.5rem 0.875rem;
    border-radius: 999px;
    background: #f9fafb;
    border: 0.0625rem dashed #d1d5db;
}

.cart-summary {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ffffff;
    border-top: 0.0625rem solid #e5e7eb;
    padding: 0.5rem 0.875rem;
    display: none;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 -0.625rem 1.625rem rgba(15, 23, 42, 0.12);
    z-index: 999;
    font-size: 0.8125rem;
}

.cart-summary .small {
    font-size: 0.6875rem;
    color: var(--text-muted);
    margin-bottom: 0.125rem;
}

.cart-summary .total {
    font-weight: 700;
    color: var(--text-main);
    font-size: 0.9375rem;
}

/* SELECT ALL MOBILE BAR (default: sembunyi) */
.select-all-mobile {
    display: none;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
}
.select-all-mobile label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    color: var(--primary);
    font-weight: 600;
}

/* TABLET (≈ 669px – 1024px) */
@media screen and (min-width: 41.8125rem) and (max-width: 64rem) {
    body { padding: 1.25rem 0.75rem 1.5rem; }

    .container {
        padding: 1rem 0.875rem 1.125rem;
        border-radius: 1rem;
        max-width: 62.5rem;
    }

    .page-header { gap: 0.5rem; }

    h2 { font-size: 1.25rem; }

    th, td {
        padding: 0.5625rem 0.625rem;
        font-size: 0.75rem;
    }

    .home-wrap .btn-base {
        padding-inline: 0.875rem;
        font-size: 0.75rem;
    }

    .cart-summary-desktop { display: flex; }
    .cart-summary { display: none; }
}

/* HP / ≤ 668px – tabel kecil & bisa geser */
@media screen and (max-width: 41.75rem) {
    body {
        padding: 1rem 0.625rem 5.25rem;
    }

    .container {
        padding: 0.875rem 0.75rem 1rem;
        border-radius: 0.875rem;
    }

    .page-header {
        align-items: flex-start;
        gap: 0.375rem;
    }

    h2 { font-size: 1.125rem; }

    .home-wrap { text-align: left; }

    th, td {
        padding: 0.5rem 0.375rem;
        font-size: 0.75rem;
        white-space: normal;
    }

    .cart-table { font-size: 0.75rem; }

    img.product-thumb { width: 3.75rem; }

    .btn-base {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-small {
        padding: 0.3125rem 0.625rem;
        font-size: 0.6875rem;
    }

    input[type="number"] {
        width: 3.75rem;
        font-size: 0.6875rem;
    }

    .cart-summary-desktop { display: none; }
    .cart-summary { display: flex; }
}

/* HP sangat kecil / ≤ 480px – card, label pink, nama produk rapi,
   dan tampilkan pilih semua di bawah */
@media screen and (max-width: 30rem) {
    body {
        padding: 0.75rem 0.5rem 5rem;
        font-size: 0.8rem;
    }

    h2 { font-size: 1rem; }

    .table-responsive {
        overflow-x: visible;
    }

    table,
    thead,
    tbody,
    th,
    td,
    tr {
        display: block;
        width: 100%;
    }

    thead { display: none; }

    .cart-table {
        border-radius: 0;
        border: none;
    }

    .cart-table tbody tr {
        margin-bottom: 0.75rem;
        padding: 0.75rem 0.75rem 0.875rem;
        border-radius: 0.75rem;
        border: 0.0625rem solid var(--border);
        box-shadow: 0 0.375rem 0.875rem rgba(15, 23, 42, 0.06);
        background: #fff;
    }

    .cart-table tbody tr:last-child {
        margin-bottom: 0.5rem;
    }

    .cart-table tbody td {
        border: none;
        padding: 0.35rem 0.5rem 0.35rem 5.3rem;
        text-align: left;
        position: relative;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        white-space: normal;
    }

    .cart-table tbody td::before {
        content: attr(data-label);
        position: absolute;
        left: 0.75rem;
        font-weight: 600;
        color: var(--primary);          /* LABEL PINK DI HP */
        font-size: 0.65rem;
    }

    /* kolom checkbox: label disembunyikan */
    .cart-table tbody td[data-label="Pilih"] {
        padding-left: 0.75rem;
        justify-content: flex-start;
    }
    .cart-table tbody td[data-label="Pilih"]::before {
        content: "";
    }

    /* Nama Produk lebih rapi, ada jarak dengan DETAIL */
    .cart-table tbody td[data-label="Nama Produk"] {
        flex-direction: column;
        align-items: flex-start;
    }
    .cart-table tbody td[data-label="Nama Produk"] .product-link {
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 0.25rem;
    }
    .cart-table tbody td[data-label="Nama Produk"] .product-link span:first-child {
        margin-bottom: 0.1rem;
    }

    img.product-thumb {
        width: 3.5rem;
        box-shadow: 0 0.3rem 0.7rem rgba(15, 23, 42, 0.12);
    }

    .btn-base {
        font-size: 0.7rem;
        padding: 0.3rem 0.7rem;
    }

    .btn-small {
        font-size: 0.65rem;
        padding: 0.25rem 0.6rem;
    }

    input[type="number"] {
        width: 3.3rem;
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }

    /* tampilkan pilih semua mobile di bawah */
    .select-all-mobile {
        display: flex;
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

<script>
function formatRupiah(num) {
    return "Rp " + num.toLocaleString("id-ID");
}

function updateSelectedItemsAndTotal() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    const totalDisplayDesktop = document.getElementById("totalDisplayDesktop");
    const totalDisplayMobile = document.getElementById("totalDisplayMobile");
    const selectedIdsField = document.getElementById("selectedIds");
    const selectAllDesktop = document.getElementById("selectAllDesktop");
    const selectAllMobile  = document.getElementById("selectAllMobile");

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

    // sinkron state "pilih semua" desktop & mobile
    const allChecked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
    if (selectAllDesktop) selectAllDesktop.checked = allChecked;
    if (selectAllMobile)  selectAllMobile.checked  = allChecked;
}

// event change item / qty
document.addEventListener("change", function(e){
    if (e.target.matches('input[name="selected[]"]') || e.target.matches('input[name="quantity"]')) {
        updateSelectedItemsAndTotal();
    }
});

// validasi submit
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

// handler pilih semua (desktop & mobile)
function handleSelectAllChange(checked) {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = checked);
    updateSelectedItemsAndTotal();
}

const selectAllDesktop = document.getElementById("selectAllDesktop");
if (selectAllDesktop) {
    selectAllDesktop.addEventListener("change", function() {
        handleSelectAllChange(this.checked);
    });
}

const selectAllMobile = document.getElementById("selectAllMobile");
if (selectAllMobile) {
    selectAllMobile.addEventListener("change", function() {
        handleSelectAllChange(this.checked);
    });
}

updateSelectedItemsAndTotal();
</script>

</body>
</html>

<?php
mysqli_free_result($result);
?>

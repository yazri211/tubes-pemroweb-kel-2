<?php
include 'conn.php';
session_start();

if (!isset($_GET['id'])) {
  echo "Produk tidak ditemukan.";
  exit();
}

$id = (int)$_GET['id'];

$query = $conn->query("SELECT * FROM products WHERE id = $id");

if ($query->num_rows == 0) {
  echo "Produk tidak ditemukan.";
  exit();
}

$product = $query->fetch_assoc();

$rating_q = mysqli_query($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM `product_reviews` WHERE product_id = " . (int)$product['id']);
$rating_data = mysqli_fetch_assoc($rating_q);
$avg_rating = $rating_data && $rating_data['avg_rating'] ? round($rating_data['avg_rating'],1) : 0;
$rating_count = $rating_data ? (int)$rating_data['cnt'] : 0;

$stock = isset($product['stock']) ? (int)$product['stock'] : 0;

function renderStars($rating) {
    $fullStar = '<svg xmlns="http://www.w3.org/2000/svg" fill="#ffd700" viewBox="0 0 24 24" width="20" height="20"><path d="M12 17.27l6.18 3.73-1.64-7.19 5.46-4.73-7.28-.62L12 2 10.27 8.46l-7.28.62 5.46 4.73-1.64 7.19z"/></svg>';
    $halfStar = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><defs><linearGradient id="half-grad"><stop offset="50%" stop-color="#ffd700"/><stop offset="50%" stop-color="#e5e7eb"/></linearGradient></defs><path d="M12 17.27l6.18 3.73-1.64-7.19 5.46-4.73-7.28-.62L12 2z" fill="url(#half-grad)"/><path d="M12 2l-1.73 6.46-7.28.62 5.46 4.73-1.64 7.19L12 17.27z" fill="#e5e7eb"/></svg>';
    $emptyStar = '<svg xmlns="http://www.w3.org/2000/svg" fill="#e5e7eb" viewBox="0 0 24 24" width="20" height="20"><path d="M12 17.27l6.18 3.73-1.64-7.19 5.46-4.73-7.28-.62L12 2 10.27 8.46l-7.28.62 5.46 4.73-1.64 7.19z"/></svg>';

    $starsHTML = '';
    for ($i=1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $starsHTML .= $fullStar;
        } elseif ($rating > $i - 1 && $rating < $i) {
            $starsHTML .= $halfStar;
        } else {
            $starsHTML .= $emptyStar;
        }
    }
    return $starsHTML;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Detail Produk - <?= htmlspecialchars($product['name'], ENT_QUOTES); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="detail_produk.css">
</head>
<body>

<div class="wrapper">
  <div class="top-bar">
    <div class="top-bar-left">
      <span class="title"><span>🛍️</span><span>Detail Produk</span></span>
      <span class="subtitle">Lihat informasi lengkap sebelum menambahkan ke keranjang.</span>
    </div>
    <a href="home.php" class="back-link" aria-label="Kembali ke Daftar Produk">
      <span class="icon">←</span>
      <span>Kembali</span>
    </a>
  </div>

  <div class="container">
    <!-- IMAGE -->
    <div class="image-box">
      <img src="assets/<?= htmlspecialchars($product['image'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES); ?>">
    </div>

    <!-- INFO -->
    <div class="info">
      <h1><?= htmlspecialchars($product['name'], ENT_QUOTES); ?></h1>

      <div class="rating-row" aria-label="Rating produk">
        <div class="rating-stars" aria-hidden="true">
          <?= $rating_count > 0 ? renderStars($avg_rating) : '' ?>
        </div>
        <?php if ($rating_count > 0): ?>
          <div class="rating-badge">
            ⭐ <?= number_format($avg_rating,1) ?>/5
          </div>
          <div class="rating-text">
            <?= $rating_count ?> ulasan
          </div>
        <?php else: ?>
          <div class="rating-text">
            Belum ada rating
          </div>
        <?php endif; ?>
      </div>

      <div class="price-row" aria-label="Harga produk">
        <div class="price">Rp <?= number_format($product['price'], 0, ',', '.'); ?></div>
        <div class="price-label">/ item</div>
      </div>

      <div class="stock-row">
        <?php if ($stock > 0): ?>
          <div class="stock-pill ok">
            <span>✔</span><span>Tersedia</span>
          </div>
          <div class="stock-text">Stok: <strong><?= $stock; ?></strong> pcs</div>
        <?php else: ?>
          <div class="stock-pill out">
            <span>✖</span><span>Stok Habis</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="qty-cart" aria-label="Jumlah pembelian produk">
        <div class="qty-label">Jumlah:</div>
        <div class="qty-box" id="qty-box">
          <button type="button" class="qty-btn" id="qty-decrease" aria-label="Kurangi jumlah">−</button>
          <input type="number" id="qty-input" name="qty" value="1" min="1" step="1" aria-live="polite" aria-atomic="true" aria-label="Jumlah produk">
          <button type="button" class="qty-btn" id="qty-increase" aria-label="Tambah jumlah">+</button>
        </div>

        <button class="btn add-to-cart"
            id="add-to-cart-btn"
            data-id="<?= $product['id']; ?>"
            data-stock="<?= $stock; ?>"
            <?= $stock === 0 ? 'disabled' : '' ?>>
            🛒 Tambahkan ke Keranjang
        </button>

      </div>
    </div>
  </div>

  <!-- DESKRIPSI -->
  <div class="desc-section">
    <h2 class="desc-label">
      <span class="icon">📄</span>
      <span>Deskripsi Produk</span>
    </h2>
    <div class="desc"><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES)); ?></div>
  </div>
</div>

<script>
  const STOCK_DATA = <?= json_encode($stock); ?>;
</script>

<script>
  
function updateCartCount() {
    fetch("cart/cart_count.php")
        .then(response => response.text())
        .then(data => {
            const el = document.getElementById("cart-count");
            if (el) el.innerText = data;
        })
        .catch(err => {
            console.error('Gagal mengambil cart count:', err);
        });
}

updateCartCount();

(function(){
    const stock = STOCK_DATA;
    const qtyInput = document.getElementById('qty-input');
    const btnInc = document.getElementById('qty-increase');
    const btnDec = document.getElementById('qty-decrease');
    const addBtn = document.getElementById('add-to-cart-btn');

    if (qtyInput) {
        qtyInput.setAttribute('max', stock > 0 ? stock : 1);
        if (stock === 0) qtyInput.value = 0;
        else if (parseInt(qtyInput.value) < 1) qtyInput.value = 1;
        else if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;
    }

    if (stock === 0 && addBtn) {
        addBtn.disabled = true;
    }

    function clampQty(val) {
        val = parseInt(val) || 0;
        if (val < 1) val = 1;
        if (stock > 0 && val > stock) val = stock;
        return val;
    }

    btnInc && btnInc.addEventListener('click', function(){
        let v = clampQty(qtyInput.value) + 1;
        if (stock > 0 && v > stock) v = stock;
        qtyInput.value = v;
    });

    btnDec && btnDec.addEventListener('click', function(){
        let v = clampQty(qtyInput.value) - 1;
        if (v < 1) v = 1;
        qtyInput.value = v;
    });

    qtyInput && qtyInput.addEventListener('input', function(){
        let v = qtyInput.value.replace(/[^\d]/g,'');
        qtyInput.value = v;
    });

    qtyInput && qtyInput.addEventListener('change', function(){
        let v = clampQty(qtyInput.value);
        qtyInput.value = v;
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('.add-to-cart')) {
            e.stopPropagation();

            const productId = e.target.dataset.id;
            let qty = parseInt(qtyInput.value) || 1;

            if (stock === 0) {
                alert('Stok habis.');
                return;
            }
            if (qty < 1) qty = 1;
            if (qty > stock) qty = stock;

            const yakin = confirm("Yakin ingin menambahkan " + qty + " item ke keranjang?");
            if (!yakin) return;

            e.target.disabled = true;

            fetch("cart/cart_add.php?id=" + encodeURIComponent(productId) + "&qty=" + encodeURIComponent(qty))
                .then(response => response.json())
                .then(json => {
                    if (json && json.success) {
                        alert("Berhasil menambahkan " + json.added + " item ke keranjang!");
                    } else {
                        alert("Gagal: " + (json.message || "Unknown error"));
                    }
                    updateCartCount();
                })
                .catch(err => {
                    console.error('Gagal menambah keranjang:', err);
                    alert('Terjadi kesalahan saat menambahkan ke keranjang.');
                })
                .finally(() => {
                    if (!e.target.hasAttribute('data-perm')) e.target.disabled = false;
                });
        }
    });
})();

</script>
</body>
</html>

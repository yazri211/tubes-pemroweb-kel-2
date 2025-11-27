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
  <style>
    :root {
      --bg: #fdf2f8;
      --card-bg: #ffffff;
      --pink-light: #fecdd3;
      --pink-mid: #ec4899;
      --pink-dark: #be185d;
      --text-main: #111827;
      --text-muted: #6b7280;
      --radius-lg: 18px;
      --radius-md: 12px;
      --shadow-main: 0 12px 30px rgba(0,0,0,0.08);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      height: 100%;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      color: var(--text-main);
      padding: 18px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .wrapper {
      max-width: 1100px;
      width: 100%;
      background: var(--card-bg);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-main);
      padding: 24px 22px 26px;
    }

    /* TOP BAR */
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f3e8ff;
      flex-wrap: wrap;
    }

    .top-bar-left {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .top-bar-left span.title {
      font-weight: 700;
      font-size: 18px;
      color: var(--pink-dark);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .top-bar-left span.subtitle {
      font-size: 12px;
      color: var(--text-muted);
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      color: var(--pink-mid);
      font-weight: 600;
      border: 1px solid var(--pink-mid);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 13px;
      transition: all 0.2s ease;
      background: #fff;
    }
    .back-link span.icon {
      font-size: 15px;
    }
    .back-link:hover {
      background: var(--pink-mid);
      color: #fff;
      transform: translateY(-1px);
    }

    .container {
      display: grid;
      grid-template-columns: minmax(0, 360px) minmax(0, 1fr);
      gap: 26px;
      align-items: flex-start;
      margin-top: 10px;
    }

    /* IMAGE SIDE */
    .image-box {
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 10px 26px rgba(236,72,153,0.25);
      background: #fee2f2;
      position: relative;
    }

    .image-box img {
      display: block;
      width: 100%;
      height: 360px;
      object-fit: cover;
      border-radius: var(--radius-lg);
    }

    .image-badge {
      position: absolute;
      top: 12px;
      left: 12px;
      background: rgba(255,255,255,0.9);
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      color: var(--pink-dark);
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    /* INFO SIDE */
    .info {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .info h1 {
      font-size: 22px;
      margin: 0;
      color: var(--pink-dark);
      font-weight: 800;
    }

    .rating-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
      flex-wrap: wrap;
    }

    .rating-stars svg {
      flex-shrink: 0;
    }

    .rating-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fef3c7;
      color: #92400e;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
    }

    .rating-text {
      font-size: 12px;
      color: var(--text-muted);
    }

    .price-row {
      margin-top: 10px;
      display: flex;
      align-items: baseline;
      gap: 8px;
      flex-wrap: wrap;
    }

    .price {
      font-size: 22px;
      font-weight: 800;
      color: var(--pink-mid);
    }

    .price-label {
      font-size: 12px;
      color: var(--text-muted);
    }

    .stock-row {
      margin-top: 4px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .stock-pill {
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 999px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .stock-pill.ok {
      background: #dcfce7;
      color: #166534;
    }

    .stock-pill.out {
      background: #fee2e2;
      color: #b91c1c;
    }

    .stock-text {
      font-size: 12px;
      color: var(--text-muted);
    }

    .qty-cart {
      margin-top: 14px;
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .qty-label {
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      user-select: none;
    }

    .qty-box {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      border: 1px solid var(--pink-light);
      border-radius: var(--radius-md);
      overflow: hidden;
      background: #fef2f7;
      user-select: none;
    }

    .qty-btn {
      background: transparent;
      border: none;
      font-weight: 700;
      font-size: 18px;
      line-height: 1;
      padding: 6px 10px;
      color: var(--pink-mid);
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
    }

    .qty-btn:hover:not(:disabled) {
      background: var(--pink-mid);
      color: #fff;
    }

    .qty-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    input[type="number"] {
      width: 70px;
      border: none;
      outline: none;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
      background: transparent;
      color: var(--pink-dark);
      font-family: 'Poppins', sans-serif;
      padding: 8px 0;
      -moz-appearance: textfield;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none;
      margin: 0; 
    }

    .btn {
      background: var(--pink-mid);
      color: white;
      border: none;
      padding: 10px 18px;
      font-size: 14px;
      font-weight: 700;
      border-radius: 999px;
      cursor: pointer;
      transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
      box-shadow: 0 6px 18px rgba(236,72,153,0.5);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
    }

    .btn:hover:not(:disabled) {
      background: var(--pink-dark);
      box-shadow: 0 10px 26px rgba(190,24,93,0.6);
      transform: translateY(-1px);
    }

    .btn:disabled {
      background: #f3e8ff;
      color: #9ca3af;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }

    /* DESC */
    .desc-section {
      margin-top: 20px;
      padding-top: 12px;
      border-top: 1px solid #f3e8ff;
    }

    .desc-label {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 6px;
      color: var(--pink-dark);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .desc-label span.icon {
      font-size: 18px;
    }

    .desc {
      font-size: 13px;
      line-height: 1.6;
      color: var(--text-muted);
      white-space: pre-line;
    }

    /* RESPONSIVE */
    @media (max-width: 992px) {
      body {
        padding: 14px;
      }
      .wrapper {
        padding: 20px 16px 22px;
        border-radius: 16px;
      }
      .container {
        grid-template-columns: minmax(0, 1fr);
      }
      .image-box img {
        height: 320px;
      }
    }

    @media (max-width: 640px) {
      body {
        padding: 10px;
      }
      .top-bar {
        align-items: flex-start;
      }
      .container {
        gap: 18px;
      }
      .image-box img {
        height: auto;
      }
      .qty-cart {
        flex-direction: column;
        align-items: stretch;
      }
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
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

        <button class="btn add-to-cart" id="add-to-cart-btn" data-id="<?= (int)$product['id']; ?>" <?= $stock === 0 ? 'disabled' : '' ?>>
          <span>🛒</span><span>Tambahkan ke Keranjang</span>
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
      const stock = <?= json_encode($stock); ?>;
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
                  .then(response => response.text())
                  .then(data => {
                      alert("Produk berhasil ditambahkan ke keranjang!");
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

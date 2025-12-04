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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/logo no wm.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="detail_produk.css?v=<?= time() ?>">
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

<script src="detail_produk.js"></script>
</body>
</html>

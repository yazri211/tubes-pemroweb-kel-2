<?php
session_start();
include 'conn.php';

//
// ------------------ DETEKSI LOGIN UNTUK FRONT-END ------------------
//
$isLoggedIn = false;
if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
} elseif (!empty($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    $isLoggedIn = true;
}

// ==================================================================

// ambil parameter search & category
$search   = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// prepare safe values
$searchTerm   = '%' . $search . '%';
$searchSafe   = mysqli_real_escape_string($conn, $searchTerm);
$categorySafe = mysqli_real_escape_string($conn, $category);

// build query dengan filter opsional
$sql = "SELECT * FROM products WHERE 1";

if (!empty($search)) {
    $sql .= " AND (name LIKE '$searchSafe' OR description LIKE '$searchSafe')";
}

if (!empty($category)) {
    $sql .= " AND category = '$categorySafe'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// categories list
$categories = [
    'makeup'     => 'Makeup',
    'skincare'   => 'Skincare',
    'haircare'   => 'Haircare',
    'bodycare'   => 'Bodycare',
    'nailcare'   => 'Nailcare',
    'fragrance'  => 'Fragrance'
];

// placeholder image
$placeholder = 'assets/placeholder.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title>Daftar Produk - Beauty Shop</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <!-- NAVBAR -->
    <header class="site-header" role="banner">
        <div class="nav-inner">
            <div class="nav-left" aria-hidden="false">
                <div class="brand" aria-hidden="false">
                    <div class="brand-logo" aria-hidden="true">
                        <img src="assets/logo.jpg" alt="Beauty Shop">
                    </div>
                    <div>
                        <div class="brand-title">Beauty Shop</div>
                        <div class="brand-sub">Online Cosmetics</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT ACTIONS -->
            <div class="nav-actions" role="group" aria-label="Aksi">
                <div class="nav-search" id="navSearch" aria-label="Pencarian produk">
                    <form method="GET" action="" role="search" aria-label="Form pencarian">
                        <?php if ($category !== ''): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category, ENT_QUOTES); ?>">
                        <?php endif; ?>
                        <label for="searchInput" class="sr-only" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">Cari produk</label>
                        <input
                            id="searchInput"
                            type="text"
                            name="q"
                            class="search-input"
                            placeholder="Cari produk..."
                            value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>"
                            aria-label="Cari produk">

                        <span id="clearSearch" class="clear-search" role="button" tabindex="0" aria-label="Bersihkan pencarian">✖</span>
                        <button type="submit" aria-label="Cari">Cari</button>
                    </form>
                </div>

                <!-- Cart -->
                <button class="cart-btn" id="cartBtn" onclick="location.href='cart/cart.php'" aria-label="Buka keranjang">
                    <?php if (file_exists('assets/icon-cart.svg')): ?>
                        <img src="assets/icon-cart.svg" alt="Keranjang">
                    <?php else: ?>
                        🛒
                    <?php endif; ?>
                    <span id="cart-count" class="cart-count" aria-live="polite">0</span>
                </button>

                <!-- Profile -->
                <div class="profile-wrapper" style="position:relative;">
                    <button id="profileBtn" class="profile-btn" aria-haspopup="true" aria-expanded="false" aria-controls="profileDropdown" title="Akun">
                        <?php if (file_exists('assets/icon-user.svg')): ?>
                            <img src="assets/icon-user.svg" alt="Akun">
                        <?php elseif (file_exists('assets/profile.png')): ?>
                            <img src="assets/profile.png" alt="Akun">
                        <?php else: ?>
                            👤
                        <?php endif; ?>
                    </button>

                    <div id="profileDropdown" class="profile-dropdown" role="menu" aria-labelledby="profileBtn">
                        <?php if ($isLoggedIn): ?>
                            <a href="home.php" role="menuitem" tabindex="0">🏠 Beranda</a>
                            <a href="order/order_history.php" role="menuitem" tabindex="0">📦 Pesanan Saya</a>
                            <a href="auth/logout.php" role="menuitem" tabindex="0">🔓 Keluar</a>
                        <?php else: ?>
                            <a id="loginMenu" href="auth/login.php" role="menuitem" tabindex="0">Masuk</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container" role="main">
        <!-- categories -->
        <ul class="categories" role="list">
            <?php
            function build_query($params){ return '?' . http_build_query($params); }
            $allParams = [];
            if ($search !== '') $allParams['q'] = $search;
            $allHref = build_query($allParams);
            $isAllActive = ($category === '');
            ?>
            <li style="list-style:none; display:inline-block;">
                <a href="<?php echo htmlspecialchars($allHref, ENT_QUOTES); ?>">
                    <button class="cat-btn <?php echo $isAllActive ? 'active' : ''; ?>">Semua</button>
                </a>
            </li>

            <?php foreach ($categories as $key => $label):
                $params = [];
                if ($search !== '') $params['q'] = $search;
                $params['category'] = $key;
                $href = build_query($params);
                $active = ($category === $key);
            ?>
                <li style="list-style:none; display:inline-block; margin-left:6px;">
                    <a href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>">
                        <button class="cat-btn <?php echo $active ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($label, ENT_QUOTES); ?>
                        </button>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- HERO SLIDER: hanya di SEMUA (tanpa category) & tanpa search -->
        <?php if ($search === '' && $category === ''): ?>
        <section class="hero" aria-label="Promo utama">
            <div class="hero-track" id="heroTrack">
                <div class="hero-slide">
                    <img class="hero-img" src="assets/iklan.jpg" alt="Promo spesial Beauty Shop">
                </div>

                <div class="hero-slide">
                    <img class="hero-img" src="assets/iklan2.jpg" alt="Promo skincare">
                    <div class="hero-overlay">
                        <h2>tes tes</h2>
                        <p>Paket skincare lengkap untuk semua jenis kulit, siap kirim hari ini.</p>
                    </div>
                </div>

                <div class="hero-slide">
                    <img class="hero-img" src="assets/iklan3.jpg" alt="Promo fragrance">
                    <div class="hero-overlay">
                        <h2>Wangi Tahan Lama</h2>
                        <p>Pilihan fragrance eksklusif untuk menambah rasa percaya diri kamu.</p>
                    </div>
                </div>
            </div>

            <button class="hero-nav hero-prev" type="button" aria-label="Sebelumnya">‹</button>
            <button class="hero-nav hero-next" type="button" aria-label="Berikutnya">›</button>

            <div class="hero-dots" id="heroDots" aria-hidden="false"></div>
        </section>
        <?php endif; ?>

        <div class="product" aria-live="polite">
            <?php
            if ($result && $result->num_rows === 0) {
                echo '<p>Tidak ditemukan produk yang sesuai.</p>';
            } elseif ($result) {
                while ($card = $result->fetch_assoc()):
                    $imageFile = !empty($card['image']) ? htmlspecialchars($card['image'], ENT_QUOTES) : '';
                    $imagePath = $imageFile ? 'assets/' . $imageFile : $placeholder;
                    $shortDesc = strip_tags($card['description'] ?? '');
                    if (mb_strlen($shortDesc) > 110) $shortDesc = mb_substr($shortDesc, 0, 107) . '...';
                    $catLabel = isset($categories[$card['category']]) ? $categories[$card['category']] : ucfirst($card['category']);
                    $price = number_format($card['price'], 0, ',', '.');
                    $stock = isset($card['stock']) ? (int)$card['stock'] : null;
                    $habis = ($stock !== null && $stock <= 0);
                    ?>
                    <article class="product-card" aria-labelledby="p-<?php echo (int)$card['id']; ?>">
                        <div class="img-wrap" role="img" aria-label="<?php echo htmlspecialchars($card['name'], ENT_QUOTES); ?>">
                            <?php if ($habis): ?>
                                <span class="badge-stock-out">Stok habis</span>
                            <?php endif; ?>

                            <a href="detail_produk.php?id=<?php echo (int)$card['id']; ?>" style="display:block;width:100%;height:100%;">
                                <img src="<?php echo $imagePath; ?>"
                                     alt="<?php echo htmlspecialchars($card['name'], ENT_QUOTES); ?>"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='<?php echo $placeholder; ?>';">
                            </a>
                        </div>

                        <div class="card-body">
                            <a href="detail_produk.php?id=<?php echo (int)$card['id']; ?>" style="text-decoration:none;color:inherit">
                                <h3 id="p-<?php echo (int)$card['id']; ?>" class="card-title"><?php echo htmlspecialchars($card['name'], ENT_QUOTES); ?></h3>
                            </a>

                            <p class="card-desc"><?php echo htmlspecialchars($shortDesc, ENT_QUOTES); ?></p>

                            <div class="price-row">
                                <div>
                                    <div class="price">Rp <?php echo $price; ?></div>
                                </div>
                                <?php if (!empty($card['old_price']) && $card['old_price'] > $card['price']): ?>
                                    <div class="old-price">Rp <?php echo number_format($card['old_price'],0,',','.'); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="card-actions">
                                <!-- Saran 4: tombol beli sekarang mengikuti stok -->
                                <button
                                    class="btn btn-primary"
                                    <?php echo $habis ? 'disabled title="Stok habis"' : ''; ?>
                                    <?php if (!$habis): ?>
                                        onclick="location.href='checkout/checkout.php?product_id=<?php echo (int)$card['id']; ?>'"
                                    <?php endif; ?>
                                >
                                    <?php echo $habis ? 'Stok habis' : 'Beli sekarang'; ?>
                                </button>

                                <!-- Saran 4: add-to-cart disable jika stok habis -->
                                <button class="btn btn-ghost icon-btn add-to-cart"
                                        data-id="<?php echo (int)$card['id']; ?>"
                                        data-logged="<?php echo $isLoggedIn ? '1' : '0'; ?>"
                                        aria-label="<?php echo $habis ? 'Stok habis' : 'Tambah ke keranjang'; ?>"
                                        title="<?php echo $habis ? 'Stok habis' : 'Tambah ke keranjang'; ?>"
                                        <?php echo $habis ? 'disabled aria-disabled="true"' : ''; ?>>
                                    🛒
                                </button>
                            </div>
                        </div>
                    </article>
                <?php
                endwhile;
            } else {
                echo '<p>Terjadi kesalahan saat mengambil produk.</p>';
            }

            if (isset($result) && $result instanceof mysqli_result) {
                $result->free();
            }
            ?>
        </div>
    </main>

    <div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="home.js"></script>
</body>
</html>

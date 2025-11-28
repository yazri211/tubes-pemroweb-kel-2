<?php 
include 'conn.php';
?>


<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan conn.php sudah di-include di halaman yang memanggil navbar
// atau include di sini jika belum:
if (!isset($conn)) {
    include __DIR__ . '/conn.php';
}

// Deteksi login untuk front-end
$isLoggedIn = false;
if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
} elseif (!empty($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    $isLoggedIn = true;
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
?>
<link rel="stylesheet" href="home.css">

<!-- NAVBAR PARTIAL - Hanya header, bukan struktur HTML lengkap -->
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

            <!-- RIGHT ACTIONS: search moved here so it sits beside the cart -->
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

                        <!-- clear (x) button: muncul hanya kalau ada teks -->
                        <span id="clearSearch" class="clear-search" role="button" tabindex="0" aria-label="Bersihkan pencarian">✖</span>

                        <button type="submit" aria-label="Cari">Cari</button>
                    </form>
                </div>

                <!-- Cart: icon-only using svg if present, otherwise fallback to emoji -->
                <button class="cart-btn" id="cartBtn" onclick="location.href='cart/cart.php'" aria-label="Buka keranjang">
                    <?php if (file_exists('assets/icon-cart.svg')): ?>
                        <img src="assets/icon-cart.svg" alt="Keranjang">
                    <?php else: ?>
                        🛒
                    <?php endif; ?>
                    <span id="cart-count" class="cart-count" aria-live="polite">0</span>
                </button>

                <!-- Profile: image on button (use icon-user.svg or profile placeholder) -->
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
                            <a href="home.php" role="menuitem" tabindex="0">🏠 Home</a>
                            <a href="order/order_history.php" role="menuitem" tabindex="0">📦 Pesanan Saya</a>
                            <a href="auth/logout.php" role="menuitem" tabindex="0">🔓 Logout</a>
                        <?php else: ?>
                            <!-- Jika belum login: hanya tampil opsi Login.
                                 Kita beri id agar JS dapat menambahkan return param saat diklik -->
                            <a id="loginMenu" href="auth/login.php" role="menuitem" tabindex="0">🔐 Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </header>
    <script src="navbar.js"></script>
    <!-- End of navbar partial -->

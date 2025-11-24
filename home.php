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


$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// prepare safe values
$searchTerm = '%' . $search . '%';
$searchSafe = mysqli_real_escape_string($conn, $searchTerm);
$categorySafe = mysqli_real_escape_string($conn, $category);

// build query with optional filters
// NOTE: fragrance is allowed now (removed exclusion)
$sql = "SELECT * FROM products WHERE 1";

if (!empty($search)) {
    $sql .= " AND (name LIKE '$searchSafe' OR description LIKE '$searchSafe')";
}

if (!empty($category)) {
    // still allow filtering by categories including 'fragrance'
    $sql .= " AND category = '$categorySafe'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// categories list: fragrance included
$categories = [
    'makeup' => 'Makeup',
    'skincare' => 'Skincare',
    'haircare' => 'Haircare',
    'bodycare' => 'Bodycare',
    'nailcare' => 'Nailcare',
    'fragrance' => 'Fragrance'
];

// path to placeholder image (make sure this file exists: assets/placeholder.png)
$placeholder = 'assets/placeholder.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title>Daftar Produk - Beauty Shop</title>

    <style>
    /* ====== VARIABLES & RESET ====== */
    :root{
        --max-w: 1180px;
        --bg: #f8f8fa;
        --accent: #ff4d94;
        --accent-2: #ff77b7;
        --muted: #6b6b6b;
        --card-shadow: 0 6px 18px rgba(255,105,180,0.08);
        --radius: 12px;
        --gap: 18px;
        --nav-height: 64px;
        --glass: rgba(255,255,255,0.9);
        --toast-bg: rgba(0,0,0,0.8);
        --transition-fast: 140ms;
        --transition-medium: 220ms;
    }

    /* ==========================
       FLUID TYPOGRAPHY (clamp)
       --------------------------
       - Base font-size is fluid using clamp()
       - Most sizes use rem so they scale automatically
    */
    html {
      /* fallback min 12px, preferred scale with viewport, max 16px */
      font-size: clamp(12px, calc(10px + 1.2vw), 16px);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* make body use rem-based sizing (1rem == html font-size) */
    body { font-size: 1rem; }

    *, *::before, *::after { box-sizing: border-box; }
    html,body{
        height:100%;
        margin:0;
        font-family:"Poppins", Arial, sans-serif;
        background:var(--bg);
        color:#222;
        line-height:1.35;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        -webkit-tap-highlight-color: rgba(0,0,0,0);
    }
    a { color: inherit; text-decoration: none; }
    button { font-family: inherit; cursor: pointer; border: none; background: none; -webkit-appearance:none; }

    /* NAVBAR: fixed so it stays visible on scroll */
    .site-header{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999; /* high so it stays above content */
        background: #ffffff;
        box-shadow: 0 0.5rem 1.5rem rgba(18,18,18,0.04);
        border-bottom: 0.0625rem solid rgba(0,0,0,0.04);
    }
    .nav-inner{
        max-width:var(--max-w); margin:0 auto; padding:0.5rem 0.75rem; height:var(--nav-height);
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        flex-wrap:wrap;
    }
    .nav-left{ display:flex; align-items:center; gap:0.75rem; flex:0 1 auto; min-width:0; }
    .brand{ display:flex; align-items:center; gap:0.625rem; flex-shrink:0; }
    .brand-logo{ width:3.25rem; height:3.25rem; border-radius:0.625rem; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; background:linear-gradient(135deg,var(--accent-2),var(--accent)); box-shadow:0 0.375rem 1.125rem rgba(255,105,180,0.08); }
    .brand-logo img{ width:100%; height:100%; object-fit:cover; display:block; }
    .brand-title{ font-weight:800; font-size:1.125rem; color:var(--accent); line-height:1; }
    .brand-sub{ font-size:0.75rem; color:#8a8a8a; margin-top:0.125rem; }

    .nav-links{ display:none; }

    .nav-actions{ display:flex; gap:0.625rem; align-items:center; justify-content:flex-end; margin-left:auto; margin-right:0; padding-right:0.25rem; flex-shrink:0; }

    .cart-btn{
        position:relative;
        width:2.75rem;
        height:2.75rem;
        border-radius:50%;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:#fff;
        border:0.125rem solid var(--accent);
        color:var(--accent);
        font-size:1.125rem;
        cursor:pointer;
        transition: transform .12s ease, background .12s ease, color .12s ease;
        box-shadow: 0 0.375rem 1.125rem rgba(255,105,180,0.06);
    }
    .cart-btn img{ width:1.25rem; height:1.25rem; display:block; }
    .cart-btn:hover, .cart-btn:focus {
        background:var(--accent);
        color:#fff;
        transform:translateY(-0.1875rem);
        outline:none;
    }
    .cart-count{
        position:absolute; top:-0.375rem; right:-0.375rem; background:var(--accent); color:#fff; font-size:0.75rem; padding:0.25rem 0.4375rem; border-radius:999px; box-shadow:0 0.375rem 0.875rem rgba(255,77,148,0.12);
        line-height:1;
    }

    .profile-btn{ width:2.75rem; height:2.75rem; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:#fff; border:0.125rem solid var(--accent); color:var(--accent); font-size:1.125rem; cursor:pointer; transition: transform .12s ease, background .12s ease, color .12s ease; box-shadow: 0 0.375rem 1.125rem rgba(255,105,180,0.06); position:relative; }
    .profile-btn img{ width:1.25rem; height:1.25rem; border-radius:50%; display:block; object-fit:cover; }
    .profile-btn:hover, .profile-btn:focus { background:var(--accent); color:#fff; transform:translateY(-0.1875rem); outline:none; }

    .profile-dropdown{ position:absolute; top:calc(var(--nav-height) + 0.75rem); right:0.375rem; min-width:11.25rem; background:#fff; border-radius:0.75rem; box-shadow:0 0.875rem 2.5rem rgba(18,18,18,0.12); border:0.0625rem solid rgba(0,0,0,0.04); padding:0.375rem; display:none; z-index:1001; }
    .profile-dropdown.show{ display:block; }
    .profile-dropdown a { display:flex; gap:0.625rem; padding:0.625rem 0.75rem; align-items:center; border-radius:0.5rem; color:#222; font-weight:600; text-decoration:none; }
    .profile-dropdown a:hover, .profile-dropdown a:focus { background: rgba(255,77,148,0.06); color:var(--accent); outline:none; }

    .mobile-menu{ display:none; width:100%; background:var(--glass); padding:0.75rem 1.125rem; border-top:0.0625rem solid rgba(0,0,0,0.03) }

    /* SEARCH inside nav-actions */
    .nav-search { display:flex; align-items:center; gap:0.5rem; position:relative; margin-left:0; }
    .search-input {
        width: 13.75rem;
        max-width: min(20rem, calc(var(--max-w) - 16.25rem));
        padding: 0.5rem 0.75rem;
        border-radius: 0.625rem;
        border: 0.0625rem solid #ececec;
        background: #fff;
        box-shadow: 0 0.25rem 0.625rem rgba(14,14,14,0.03);
        transition: width .24s ease, box-shadow .18s ease, border-color .18s ease;
        font-size: 0.875rem;
        outline: none;
    }
    .search-input:focus, .nav-search.expanded .search-input { 
        width: 20rem;
        box-shadow: 0 0.75rem 1.75rem rgba(18,18,18,0.06); 
        border-color: rgba(255,77,148,0.12); 
    }
    .nav-search button[type="submit"]{ padding:0.5625rem 0.875rem; border-radius:0.625rem; background:var(--accent); color:#fff; font-weight:700; cursor:pointer; box-shadow:0 0.5rem 1.25rem rgba(255,77,148,0.06); }

    /* clear-search */
    .clear-search {
        position: absolute;
        right: calc(0.75rem + 3rem + 0.5rem); /* converted from 12px + 48px + 8px */
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.125rem;
        cursor: pointer;
        color: #999;
        display: none;
        user-select: none;
        line-height:1;
        padding:0.375rem;
        border-radius:0.5rem;
    }
    .clear-search:hover, .clear-search:focus { color: var(--accent); background: rgba(255,77,148,0.04); }
    .clear-search:focus { outline: 0.125rem solid rgba(255,77,148,0.12); }

    /* rest of page */
    main.container{ 
        max-width:var(--max-w); 
        margin:1.125rem auto; 
        padding-left:1.125rem; 
        padding-right:1.125rem; 
        padding-bottom:3.75rem;
        /* reserve space for fixed header */
        padding-top: calc(var(--nav-height) + 1.125rem);
    }
    .categories{ display:flex; flex-wrap:nowrap; gap:0.625rem; margin:0.5rem 0 1.125rem; padding:0; list-style:none; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .categories::-webkit-scrollbar { height:0.375rem; }
    .categories::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius:999px; }
    .cat-btn{ padding:0.5rem 0.875rem; border-radius:999px; border:0.0625rem solid #ffdfe8; background:#fff; color:var(--accent); cursor:pointer; font-weight:600; font-size:0.875rem; white-space:nowrap; }
    .cat-btn.active{ background:var(--accent); color:#fff; border-color:var(--accent) }

    /* DEFAULT GRID: desktop large screens */
    .product { display:grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; align-items:stretch; }
    .product-card{ background:#fff; border-radius:0.875rem; box-shadow:var(--card-shadow); overflow:hidden; display:flex; flex-direction:column; transition: transform .18s ease, box-shadow .18s ease; min-height:23.75rem; }
    .product-card:hover{ transform:translateY(-0.375rem); box-shadow:0 1.25rem 2.5rem rgba(255,105,180,0.08) }
    .img-wrap{ position:relative; aspect-ratio:4/3; overflow:hidden; background:linear-gradient(180deg,#fff7fa 0%, #fff0f5 100%); display:flex; align-items:center; justify-content:center; }
    .img-wrap img{ width:100%; height:100%; object-fit:cover; display:block; transition: transform .45s ease; }
    .product-card:hover .img-wrap img{ transform:scale(1.05); }

    .card-body{ padding:1rem; display:flex; flex-direction:column; gap:0.5rem; flex:1 1 auto; }
    .card-title{ font-size:1rem;  font-weight:800; color:#222; line-height:1.2; min-height:2.125rem; margin:0; }
    .card-desc{ font-size:0.8125rem; color:var(--muted); min-height:1.875rem; margin:0; overflow:hidden; margin-top:0.25rem; }

    .price-row{ display:flex; align-items:center; justify-content:space-between; gap:0.5rem; margin-top:0.375rem; }
    .price{ font-size:1.0625rem; font-weight:900; color:var(--accent); white-space:nowrap; }
    .old-price{ font-size:0.8125rem; color:#b4b4b4; text-decoration:line-through; }

    .card-actions{ display:flex; gap:0.625rem; margin-top:auto; padding-top:0.375rem; align-items:center; }
    .btn{ flex:1; padding:0.75rem 0.875rem; border-radius:0.75rem; border:none; cursor:pointer; font-weight:800; font-size:0.875rem; }
    .btn-primary{ background:var(--accent); color:#fff; box-shadow:0 0.5rem 1.25rem rgba(255,77,148,0.06); height:3rem; display:inline-flex; align-items:center; justify-content:center; }
    .btn-ghost{ background:#fff; border:0.0625rem solid #ffdfe8; color:var(--accent); min-width:3.25rem; height:2.75rem; display:inline-flex; align-items:center; justify-content:center; border-radius:0.75rem; }
    .icon-btn{ width:3rem; min-width:3rem; height:2.75rem; border-radius:0.75rem; font-size:1.125rem; }

    .toast { position: fixed; right: 1.25rem; bottom: 1.25rem; background: var(--toast-bg); color: #fff; padding: 0.625rem 0.875rem; border-radius: 0.625rem; box-shadow: 0 0.5rem 1.875rem rgba(0,0,0,0.3); z-index: 9999; opacity: 0; transform: translateY(8px); transition: opacity .22s ease, transform .22s ease; pointer-events: none; font-weight:700; }
    .toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

    /* Keep single product card same visual size as multiple cards (desktop/tablet) */
    .product.single {
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }
    .product.single .product-card {
        width: 16.25rem;
        max-width: 16.25rem;
        min-width: 15rem;
        margin: 0;
    }

    /* Tablet & responsive grid rules (UPDATED):
       - >=1200px : 4 columns (desktop)
       - 992px - 1199px : 3 columns (large tablet / small laptop)
       - 768px - 991px : 3 columns (tablet landscape)
       - 540px - 767px : 2 columns (small tablets & phablets)
       - 380px - 539px : 2 columns (phones)
       - <380px : 1 column (very small phones)
    */

    /* large tablets / small laptops */
    @media (max-width: 1199px) and (min-width: 992px) {
        .product { grid-template-columns: repeat(3, 1fr); gap: 1.125rem; }
        .product-card { min-height:22.5rem; }
    }

    /* tablet landscape and regular tablets */
    @media (max-width: 991px) and (min-width: 768px) {
        .product { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .product-card { min-height:22.5rem; }
    }

    /* small tablets & large phones */
    @media (max-width: 767px) and (min-width: 540px) {
        .product { grid-template-columns: repeat(2, 1fr); gap: 0.875rem; }
        .product-card { min-height: auto; }
    }

    /* phones (most mobile devices) */
    @media (max-width: 539px) and (min-width: 380px) {
        .product { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
        .product-card { min-height: auto; }
    }

    /* very small phones -> single column */
    @media (max-width: 379px) {
        .product { grid-template-columns: repeat(1, 1fr); gap: 0.625rem; }
        .img-wrap { aspect-ratio: 16/9; }
        .card-desc { font-size:0.8125rem; min-height:2.5rem; }
        .btn-primary { height:3rem; font-size:0.9375rem; }
    }

    /* RESPONSIVE - other nav adjustments */
    @media (max-width:900px){
        .nav-links{ display:none; }
        .nav-search { flex:1; justify-content:flex-end; }
        .search-input { width:8.75rem; }
        .search-input:focus, .nav-search.expanded .search-input { width:100%; max-width: 30rem; }
        .profile-dropdown { right:0.5rem; top: calc(var(--nav-height) + 0.5rem); }
        .mobile-menu{ display:block; }
    }

    /* MOBILE SMALLER LAYOUT (improved) */

    /* Fix for mid-small screens: 441px — 551px
       Addresses navbar wrapping / misalignment issues */
    @media (min-width:441px) and (max-width:551px) {
        .nav-inner {
            /* allow a bit more vertical room and center items */
            padding: 0.5rem 0.75rem;
            height: auto;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-title { font-size: 1rem; }
        .brand-sub { display: none; }

        /* make search take majority of available space but keep buttons visible */
        .nav-actions { display:flex; align-items:center; gap:0.5rem; width:100%; }
        .nav-search { order: 1; flex: 1 1 58%; max-width: 42rem; }
        .search-input { width: 12.5rem; max-width: 100%; }
        .nav-search button[type="submit"]{ padding:0.5rem 0.75rem; }

        /* keep cart/profile to the right but reduce chance of wrapping */
        .profile-wrapper { order: 2; margin-left: 0.5rem; }
        .cart-btn { order: 3; margin-left: auto; }

        /* slightly reduce icon sizes so they fit nicely */
        .cart-btn, .profile-btn { width:2.5rem; height:2.5rem; font-size:1rem; }
        .cart-btn img, .profile-btn img { width:1rem; height:1rem; }

        /* avoid the clear icon overlapping on this range */
        .clear-search { right: 4.25rem; }
    }

    @media (max-width:440px){
        .nav-inner {
            flex-wrap:wrap;
            align-items:center;
            padding:0.5rem 0.625rem;
            gap:0.5rem;
            height: auto;
        }

        .nav-left {
            order: 1;
            width: auto;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .brand-logo {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.625rem;
        }
        .brand-title { font-size: 0.9375rem; }
        .brand-sub { display: none; }

        /* actions container spans full width */
        .nav-actions {
            order: 2;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0;
        }

        /* Search becomes flexible and sits at left */
        .nav-search {
            order: 1;
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }
        .nav-search form { width: 100%; display:flex; gap:0.5rem; align-items:center; }
        .search-input { width: 100%; max-width: none; padding:0.625rem 0.75rem; font-size:0.9375rem; border-radius:0.625rem; }

        /* make submit button bigger on mobile for touch */
        .nav-search button[type="submit"] { padding:0.625rem 0.875rem; font-size:0.9375rem; border-radius:0.625rem; }

        /* clear-search reposition to be closer to the right edge on small screens */
        .clear-search { right: 4.5rem; font-size:1.125rem; padding:0.5rem; }

        /* cart + profile grouped to the right - enlarge slightly for finger taps */
        .cart-btn { order: 2; width:2.75rem; height:2.75rem; flex: 0 0 auto; border-width:0.125rem; }
        .profile-wrapper { order: 3; flex: 0 0 auto; display: flex; align-items: center; }
        .profile-btn { order: 3; width:2.75rem; height:2.75rem; }

        .cart-count { top: -0.375rem; right: -0.375rem; padding: 0.1875rem 0.375rem; font-size: 0.6875rem; }

        .mobile-menu { display: none; }

        .product-card { min-height: auto; border-radius:0.75rem; }
        .img-wrap { aspect-ratio: 16/9; border-bottom-left-radius: 0.625rem; border-bottom-right-radius: 0.625rem; }

        .card-body { padding:0.875rem; gap:0.5rem; }
        .card-title { font-size:1rem; }
        .card-desc { font-size:0.875rem; min-height:2.5rem; }

        .btn-primary { height:3.125rem; font-size:1rem; border-radius:0.75rem; }
        .btn-ghost { height:3.125rem; width:3.125rem; border-radius:0.75rem; }
    }

    @media (max-width:360px){
        main.container { padding-left:0.75rem; padding-right:0.75rem; }
        .search-input { font-size:0.875rem; padding:0.625rem; }
        .btn-primary { height:3rem; font-size:0.9375rem; }
        /* slight adjustment to base html on very small screens for legibility */
        html { font-size: clamp(11px, calc(9px + 1.0vw), 14px); }
    }
  .slider {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    width: 100%;
    gap: 20px;
  }

  .slider::-webkit-scrollbar {
    display: none;
  }

  .slide-item {
    position: relative;
    flex: 0 0 100%; 
    height: 50vh;
    scroll-snap-align: center;
  }

  .slide-item img {
    width: 100%;
    height: 90%;
    margin-top: 15px;
    object-fit: cover;
    filter: brightness(60%);
  }

  @media (min-width: 768px) {
    .slide-item {
      height: 70vh;
    }
  }

    

    </style>
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
            <li style="list-style:none; display:inline-block;"><a href="<?php echo htmlspecialchars($allHref, ENT_QUOTES); ?>"><button class="cat-btn <?php echo $isAllActive ? 'active' : ''; ?>">Semua</button></a></li>

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
       
        
<section class="slider">
  <div class="slide-item">
    <img src="assets/iklan.jpg" alt="Banner 1">
  </div>

  <div class="slide-item">
    <img src="GAMBAR-LAIN-2.jpg" alt="Banner 2">
  </div>

  <div class="slide-item">
    <img src="GAMBAR-LAIN-3.jpg" alt="Banner 3">
  </div>
</section>

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
                    ?>
                    <article class="product-card" aria-labelledby="p-<?php echo (int)$card['id']; ?>">
                        <div class="img-wrap" role="img" aria-label="<?php echo htmlspecialchars($card['name'], ENT_QUOTES); ?>">
                            <a href="detail_produk.php?id=<?php echo (int)$card['id']; ?>" style="display:block;width:100%;height:100%;">
                                <img src="<?php echo $imagePath; ?> "
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
                                <button class="btn btn-primary" onclick="location.href='checkout/checkout.php?product_id=<?php echo (int)$card['id']; ?>'">Beli sekarang</button>

                                <!-- TOMBOL: tambahkan data-logged -->
                                <button class="btn btn-ghost icon-btn add-to-cart"
                                        data-id="<?php echo (int)$card['id']; ?>"
                                        data-logged="<?php echo $isLoggedIn ? '1' : '0'; ?>"
                                        aria-label="Tambah ke keranjang"
                                        title="Tambah ke keranjang">
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

    <!-- TOAST -->
    <div id="toast" class="toast" role="status" aria-live="polite"></div>

    <!-- SCRIPTS -->
    <script>
    // toast helper
    function showToast(message, timeout = 2000) {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(t._hideTimer);
        t._hideTimer = setTimeout(() => t.classList.remove('show'), timeout);
    }

    // update cart count (calls server endpoint that returns a number)
    function updateCartCount() {
        fetch("cart/cart_count.php", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(response => response.text())
            .then(data => {
                const el = document.getElementById("cart-count");
                if (el) el.innerText = data;
            })
            .catch(err => console.error('Gagal mengambil cart count:', err));
    }
    // initial load
    updateCartCount();

    // helper to get current path + query for return param
    function currentPathWithQuery(){
        try {
            return window.location.pathname + window.location.search;
        } catch (e) {
            return window.location.href;
        }
    }

    // If loginMenu exists (not logged-in state), attach return param to its href on click
    (function(){
        const loginMenu = document.getElementById('loginMenu');
        if (!loginMenu) return;
        loginMenu.addEventListener('click', function(e){
            // we add return param to href so user returns after login
            // If user used Ctrl/Meta to open in new tab, default behavior will still include the href we set.
            const returnTo = encodeURIComponent(currentPathWithQuery());
            const base = 'auth/login.php';
            // preserve existing search if any (unlikely), but replace for safety
            loginMenu.href = base + '?return=' + returnTo;
            // allow navigation to proceed
        });
    })();

    // add-to-cart handler (POST, checks data-logged, direct redirect if not logged)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.add-to-cart');
        if (!btn) return;
        const productId = btn.dataset.id;
        if (!productId) return;

        const logged = btn.getAttribute('data-logged') === '1';

        if (!logged) {
            // langsung redirect ke halaman login tanpa toast/delay
            const returnTo = encodeURIComponent(currentPathWithQuery());
            window.location.href = 'auth/login.php?return=' + returnTo;
            return;
        }

        // visual feedback
        btn.disabled = true;
        btn.style.opacity = '0.6';

        // send POST request
        const form = new URLSearchParams();
        form.append('id', productId);
        // jika mau qty, tambahkan: form.append('qty', '1');

        fetch('cart/cart_add.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: form.toString(),
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                // jika server masih menganggap unauthorized, arahkan ke login
                const returnTo = encodeURIComponent(currentPathWithQuery());
                window.location.href = 'auth/login.php?return=' + returnTo;
                throw new Error('Unauthorized');
            }
            if (!response.ok) {
                return response.json().then(j => { throw new Error(j.message || 'Server error'); });
            }
            return response.json();
        })
        .then(json => {
            if (json && json.status === 'ok') {
                showToast(json.message || 'Produk ditambahkan ke keranjang');
                if (json.cart_count !== undefined) {
                    const el = document.getElementById('cart-count');
                    if (el) el.innerText = json.cart_count;
                } else {
                    updateCartCount();
                }
            } else {
                showToast(json.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(err => {
            if (err.message !== 'Unauthorized') {
                console.error('Gagal menambah keranjang:', err);
                showToast('Gagal menambahkan ke keranjang');
            }
        })
        .finally(() => {
            setTimeout(() => { btn.disabled = false; btn.style.opacity = ''; }, 600);
        });
    });

    // NAV SEARCH expand/contract
    (function(){
        const navSearchWrap = document.getElementById('navSearch');
        const searchInput = navSearchWrap ? navSearchWrap.querySelector('.search-input') : null;

        if (searchInput && navSearchWrap) {
            searchInput.addEventListener('focus', () => navSearchWrap.classList.add('expanded'));
            searchInput.addEventListener('blur', () => {
                setTimeout(() => navSearchWrap.classList.remove('expanded'), 120);
            });

            document.addEventListener('click', (e) => {
                if (!navSearchWrap.contains(e.target)) {
                    navSearchWrap.classList.remove('expanded');
                }
            });
        }
    })();

    // PROFILE DROPDOWN: toggle, close on outside click / Esc
    (function(){
        const profileBtn = document.getElementById('profileBtn');
        const dropdown = document.getElementById('profileDropdown');

        if (!profileBtn || !dropdown) return;

        function openDropdown() {
            dropdown.classList.add('show');
            profileBtn.setAttribute('aria-expanded', 'true');
            const first = dropdown.querySelector('[role="menuitem"]');
            if (first) first.focus();
        }
        function closeDropdown() {
            dropdown.classList.remove('show');
            profileBtn.setAttribute('aria-expanded', 'false');
            profileBtn.focus();
        }
        function toggleDropdown() {
            if (dropdown.classList.contains('show')) closeDropdown();
            else openDropdown();
        }

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== profileBtn) {
                if (dropdown.classList.contains('show')) closeDropdown();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (dropdown.classList.contains('show')) closeDropdown();
            }
        });

        dropdown.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const target = document.activeElement;
                if (target && target.getAttribute('role') === 'menuitem') {
                    target.click();
                }
            }
        });
    })();

    // Accessibility: allow Enter key on product cards to open detail (for keyboard users)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            const active = document.activeElement;
            if (active && active.closest && active.closest('.product-card')) {
                const link = active.closest('.product-card').querySelector('a[href^="detail_produk.php"]');
                if (link) link.click();
            }
        }
    });

    (function(){
        const input = document.getElementById("searchInput");
        const clearBtn = document.getElementById("clearSearch");

        if (!input || !clearBtn) return;

        function toggleClear() {
            clearBtn.style.display = input.value.length > 0 ? "block" : "none";
        }

        // show/hide on input
        input.addEventListener("input", toggleClear);

        // click clears => remove q and category from URL and reload to "tampilan awal"
        clearBtn.addEventListener("click", function(e){
            e.preventDefault();
            input.value = "";
            toggleClear();

            // if URL API available, clean params and reload
            try {
                const url = new URL(window.location.href);
                let changed = false;
                if (url.searchParams.has('q')) { url.searchParams.delete('q'); changed = true; }
                if (url.searchParams.has('category')) { url.searchParams.delete('category'); changed = true; }

                // if there were any changes, navigate to cleaned URL to get default listing
                if (changed) {
                    // ensure we don't leave a trailing "?" (URL.toString handles it)
                    const cleaned = url.pathname + (url.search ? url.search : '');
                    // use assign to trigger a full reload (so server returns default list)
                    window.location.assign(cleaned);
                    return;
                }
            } catch (err) {
                // fallback: if URL not supported, try simple reload
                try { window.location.href = window.location.pathname; return; } catch (e) { /* ignore */ }
            }

            // If nothing to change, just focus input
            input.focus();
        });

        // keyboard accessible (Enter / Space)
        clearBtn.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                clearBtn.click();
            }
        });

        // initial check on page load
        toggleClear();
    })();

    // === Prevent search staying expanded when user clicks category links ===
    (function(){
        const navSearchWrap = document.getElementById('navSearch');
        const searchInput = navSearchWrap ? navSearchWrap.querySelector('.search-input') : null;
        const categoryLinks = document.querySelectorAll('.categories a');

        if (categoryLinks && categoryLinks.length) {
            categoryLinks.forEach(link => {
                link.addEventListener('click', () => {
                    try {
                        if (searchInput) searchInput.blur();
                        if (navSearchWrap) navSearchWrap.classList.remove('expanded');
                    } catch (e) { /* ignore */ }
                });
            });
        }

        // keyboard support for Enter/Space on focused category button/link
        document.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && document.activeElement) {
                const a = document.activeElement.closest && document.activeElement.closest('.categories a');
                if (a) {
                    if (searchInput) searchInput.blur();
                    if (navSearchWrap) navSearchWrap.classList.remove('expanded');
                }
            }
        });
    })();

    // Ensure single product keeps same visual size as multi-product layout (desktop/tablet)
    (function(){
        const productContainer = document.querySelector('.product');

        if (!productContainer) return;

        function updateSingleClass() {
            const cards = productContainer.querySelectorAll('.product-card');
            const count = cards.length;

            // Remove any helper classes
            productContainer.classList.remove('single', 'count-2', 'count-3');

            if (count === 1) {
                productContainer.classList.add('single');
            } else if (count === 2) {
                productContainer.classList.add('count-2');
            } else if (count === 3) {
                productContainer.classList.add('count-3');
            } else {
                // 4+ -> default grid (no helper class)
            }
        }

        // initial run
        updateSingleClass();

        // Observe DOM changes inside .product (safeguard if products replaced dynamically)
        try {
            const mo = new MutationObserver((mutations) => {
                clearTimeout(productContainer._singleTimer);
                productContainer._singleTimer = setTimeout(updateSingleClass, 40);
            });
            mo.observe(productContainer, { childList: true, subtree: false });
        } catch (e) {
            // fallback: update on window resize
            window.addEventListener('resize', updateSingleClass);
        }
    })();
    </script>
</body>
</html>

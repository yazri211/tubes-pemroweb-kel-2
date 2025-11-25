<?php
include '../conn.php';
session_start();

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: cart.php");
    exit();
}

// Ambil info produk (termasuk stok)
$product = $conn->query("SELECT id, stock FROM products WHERE id = $product_id");
if (!$product || $product->num_rows === 0) {
    // Produk tidak ditemukan
    header("Location: cart.php");
    exit();
}

$prod_data = $product->fetch_assoc();
$available_stock = (int)$prod_data['stock'];

// cek apakah produk sudah ada di keranjang user
$check = $conn->query("SELECT quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");

if ($check->num_rows > 0) {
    // kalau sudah ada, cek jika menambah 1 tidak melebihi stok
    $cart_item = $check->fetch_assoc();
    $current_qty = (int)$cart_item['quantity'];
    $new_qty = $current_qty + 1;
    
    if ($new_qty <= $available_stock) {
        // Update hanya jika tidak melebihi stok
        $conn->query("UPDATE cart SET quantity = $new_qty 
                      WHERE user_id = $user_id AND product_id = $product_id");
        // Redirect dengan pesan sukses
        header("Location: cart.php?msg=Produk+ditambahkan");
    } else {
        // Stok tidak cukup
        header("Location: cart.php?error=Stok+tidak+cukup");
    }
} else {
    // kalau belum ada, cek apakah stok cukup untuk menambah 1 item
    if ($available_stock > 0) {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) 
                      VALUES ($user_id, $product_id, 1)");
        header("Location: cart.php?msg=Produk+ditambahkan");
    } else {
        // Stok habis
        header("Location: cart.php?error=Produk+habis");
    }
}

exit();
?>

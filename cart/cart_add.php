<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include '../conn.php';

if (empty($_SESSION['user_id']) && (empty($_SESSION['user']['id']))) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if (!empty($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
} else {
    $user_id = (int) $_SESSION['user']['id'];
}

$product_id = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? $_GET['qty'] ?? 1);

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "product_id kosong"]);
    exit();
}

// Fetch product stock
$sqlProduct = "SELECT stock FROM products WHERE id = ? LIMIT 1";
$stmtProduct = mysqli_prepare($conn, $sqlProduct);
mysqli_stmt_bind_param($stmtProduct, "i", $product_id);
mysqli_stmt_execute($stmtProduct);
$resProduct = mysqli_stmt_get_result($stmtProduct);

if (!$resProduct || mysqli_num_rows($resProduct) === 0) {
    echo json_encode(["success" => false, "message" => "Produk tidak ditemukan"]);
    exit();
}

$productData = mysqli_fetch_assoc($resProduct);
$available_stock = (int)$productData['stock'];

if ($available_stock <= 0) {
    echo json_encode(["success" => false, "message" => "Stok habis", "stock" => 0]);
    exit();
}

// Limit qty to available stock
$qty_to_add = min($qty, $available_stock);

// cek apakah sudah ada di cart
$sqlCheck = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);

$added = 0;

if ($row = mysqli_fetch_assoc($resCheck)) {

    $current_qty = (int)$row['quantity'];
    $new_qty = $current_qty + $qty_to_add;
    
    if ($new_qty > $available_stock) {
        $new_qty = $available_stock;
        $added = $new_qty - $current_qty;
    } else {
        $added = $qty_to_add;
    }
    
    $sqlUpdate = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, "ii", $new_qty, $row['id']);
    mysqli_stmt_execute($stmtUpdate);
} else {
    // belum ada → insert baru
    $added = $qty_to_add;
    $sqlInsert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmtInsert = mysqli_prepare($conn, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "iii", $user_id, $product_id, $added);
    mysqli_stmt_execute($stmtInsert);
}

$remaining_stock = $available_stock;

echo json_encode([
    "success" => true,
    "added" => $added,
    "message" => "Produk berhasil ditambahkan ke keranjang",
    "stock" => $remaining_stock
]);

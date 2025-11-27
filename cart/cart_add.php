<?php
session_start();
include '../conn.php';

// kalau dipanggil dari fetch(), lebih enak kalau untuk tidak login balas 401
if (empty($_SESSION['user_id']) && (empty($_SESSION['user']['id']))) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

if (!empty($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
} else {
    $user_id = (int) $_SESSION['user']['id'];
}

if (empty($_POST['product_id'])) {
    http_response_code(400);
    echo "product_id kosong";
    exit();
}

$product_id = (int) $_POST['product_id'];
$qty        = 1;

// cek apakah sudah ada di cart
$sqlCheck = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);

if ($row = mysqli_fetch_assoc($resCheck)) {
    // sudah ada → update quantity
    $newQty = $row['quantity'] + $qty;
    $sqlUpdate = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, "ii", $newQty, $row['id']);
    mysqli_stmt_execute($stmtUpdate);
} else {
    // belum ada → insert baru, PENTING: masukkan user_id
    $sqlInsert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmtInsert = mysqli_prepare($conn, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "iii", $user_id, $product_id, $qty);
    mysqli_stmt_execute($stmtInsert);
}

echo "OK";

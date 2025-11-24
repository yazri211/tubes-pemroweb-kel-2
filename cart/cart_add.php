<?php
session_start();
require '../conn.php';

header('Content-Type: text/plain; charset=utf-8');

// Wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit();
}

// Cek login
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit();
}

$user_id    = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo 'Produk tidak valid';
    exit();
}

// cek apakah produk sudah ada di keranjang
$sqlCheck = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // kalau sudah ada, tambahkan jumlahnya
    $sqlUpdate = "UPDATE cart SET quantity = quantity + 1 
                  WHERE user_id = ? AND product_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $user_id, $product_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    // kalau belum ada, insert baru
    $sqlInsert = "INSERT INTO cart (user_id, product_id, quantity) 
                  VALUES (?, ?, 1)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ii", $user_id, $product_id);
    $stmtInsert->execute();
    $stmtInsert->close();
}

$stmt->close();

// Balasan simpel ke JS
echo 'OK';
exit();

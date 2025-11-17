<?php

include'../conn.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $konfir_password = $_POST['konfir_password'];

    if ($password != $konfir_password) {
        echo "<script>alert('konfirmasi password tidak sesuai')</script>";
    } else {

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email sudah digunakan!');</script>";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password_hash')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal registrasi!');</script>";
        }
    }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
</head>
<body>
    <h1>REGISTRASI</h1>

    <form action="" method="post">

        <ul>
            <li>
                <label for="username">username :</label>
                <input type="text" name="username" id="username">
            </li>

            <li>
                <label for="email">email :</label>
                <input type="email" name="email" id="email">
            </li>

            <li>
                <label for="password">password :</label>
                <input type="password" name="password" id="password">
            </li>

            <li>
                <label for="konfir_password">konfirmasi :</label>
                <input type="password" name="konfir_password" id="konfir_password">
            </li>

            <li>
                <button type="submit" name="register">register</button>
            </li>

            <li>
                <p>sudah punya akun? <a href="login.php">login</a> sekarang</p>
            </li>

            
        </ul>

    </form>

</body>
</html>
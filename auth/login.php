<?php
session_start();
include '../conn.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' ");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == "admin") {
                header("Location: ../admin/dasboard.php");
            } else {
                header("Location: ../home.php");
            }
            exit();
    }
    $error = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
    <h1>LOGIN</h1>
    <?php

    if(isset($error)){
        echo"<p>password/username salah</p>";
    }
    ?>
    
    <form action="" method="post">

        <ul>
            <li>
                <label for="username">username :</label>
                <input type="text" name="username" id="username">
            </li>

            <li>
                <label for="password">password :</label>
                <input type="password" name="password" id="password">
            </li>

            <li>
                <button type="submit" name="login">login</button>
            </li>

            <p>Belum punya akun? <a href="register.php">register</a></p>

            
        </ul>

    </form>

</body>
</html>
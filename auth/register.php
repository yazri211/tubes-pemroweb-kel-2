<?php

include '../conn.php';

$msg = "";

$old = [
    'username' => '',
    'email' => ''
];

if (isset($_POST['register'])) {
    // ambil dan trim input
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfir_password = $_POST['konfir_password'] ?? '';

    $old['username'] = $username;
    $old['email'] = $email;

    if ($password !== $konfir_password) {
        $msg = "Konfirmasi kata sandi tidak sesuai";
    } elseif ($username === '' || $email === '' || $password === '') {
        $msg = "Silakan isi semua field";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Format email tidak valid!";
    } else {

        // Cek apakah email sudah digunakan (tanpa prepared statement)
        $query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $msg = "Email sudah digunakan!";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = "INSERT INTO users (username, email, password) 
                       VALUES ('$username', '$email', '$password_hash')";

            if (mysqli_query($conn, $insert)) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
                exit;
            } else {
                $msg = "Gagal Mendaftar! (server)";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Registrasi - Beauty Shop</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
  <div class="container">
    <main class="login-card" role="main" aria-labelledby="regTitle" id="regCard">
      <div class="login-header">
        <div class="brand-pill">
          <img src="../assets/logo.jpg" alt="Logo Beauty Shop">
        </div>
        <div style="text-align:center">
          <h1 id="regTitle">Daftar</h1>
          <div class="badge-pill">
            <span>Beauty Shop Account</span>
          </div>
        </div>
      </div>

      <p class="subtitle">Buat akun untuk mengakses layanan kami.</p>

      <?php if ($msg !== ""): ?>
        <div class="error" role="alert">
          <div class="error-icon">!</div>
          <div class="error-text">
            <?php echo htmlspecialchars($msg); ?>
          </div>
        </div>
      <?php endif; ?>

      <form id="regForm" action="" method="post" novalidate>
        <ul>
          <li>
            <div class="field-label">
              <label for="username">Nama Pengguna</label>
            </div>
            <div class="input-wrap">
              <input type="text"
                     name="username"
                     id="username"
                     autocomplete="username"
                     inputmode="text"
                     required
                     minlength="3"
                     pattern="[A-Za-z0-9_]{3,}"
                     title="Nama Pengguna minimal 3 karakter (huruf, angka, underscore)"
                     value="<?php echo htmlspecialchars($old['username']); ?>">
            </div>
          </li>

          <li>
            <div class="field-label">
              <label for="email">Email</label>
            </div>
            <div class="input-wrap">
              <input type="email"
                     name="email"
                     id="email"
                     autocomplete="email"
                     required
                     value="<?php echo htmlspecialchars($old['email']); ?>">
            </div>
          </li>

          <li>
            <div class="field-label">
              <label for="password">Kata Sandi</label>
            </div>
            <div class="input-wrap">
              <input type="password"
                     name="password"
                     id="password"
                     autocomplete="new-password"
                     required
                     minlength="6">
              <button type="button"
                      class="toggle-pw"
                      data-target="password"
                      aria-label="Tampilkan kata sandi"
                      aria-pressed="false">
                <!-- mata terbuka (disembunyikan awal) -->
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" style="display:none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path
                    d="M2 12C4.5 8 7.5 6 12 6C16.5 6 19.5 8 22 12C19.5 16 16.5 18 12 18C7.5 18 4.5 16 2 12Z"
                    stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3.2"
                          stroke="#ff6aa6" stroke-width="2"/>
                </svg>
                <!-- mata tertutup (default) -->
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path
                    d="M2 12C4.5 8 7.5 6 12 6C16.5 6 19.5 8 22 12C19.5 16 16.5 18 12 18C7.5 18 4.5 16 2 12Z"
                    stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3.2"
                          stroke="#ff6aa6" stroke-width="2"/>
                  <path d="M4 4L20 20"
                        stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </li>

          <li>
            <div class="field-label">
              <label for="konfir_password">Konfirmasi Kata Sandi</label>
            </div>
            <div class="input-wrap">
              <input type="password"
                     name="konfir_password"
                     id="konfir_password"
                     autocomplete="new-password"
                     required
                     minlength="6">
              <button type="button"
                      class="toggle-pw"
                      data-target="konfir_password"
                      aria-label="Tampilkan konfirmasi kata sandi"
                      aria-pressed="false">
                <!-- mata terbuka (disembunyikan awal) -->
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" style="display:none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path
                    d="M2 12C4.5 8 7.5 6 12 6C16.5 6 19.5 8 22 12C19.5 16 16.5 18 12 18C7.5 18 4.5 16 2 12Z"
                    stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3.2"
                          stroke="#ff6aa6" stroke-width="2"/>
                </svg>
                <!-- mata tertutup (default) -->
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path
                    d="M2 12C4.5 8 7.5 6 12 6C16.5 6 19.5 8 22 12C19.5 16 16.5 18 12 18C7.5 18 4.5 16 2 12Z"
                    stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3.2"
                          stroke="#ff6aa6" stroke-width="2"/>
                  <path d="M4 4L20 20"
                        stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </li>

          <li>
            <button type="submit" name="register" id="submitBtn">
              <span class="btn-text">Daftar</span>
            </button>
          </li>

          <li>
            <p class="help-text">Sudah punya akun? <a href="login.php">Masuk sekarang</a></p>
          </li>
        </ul>
      </form>
    </main>
  </div>

  <script src="js/register.js"></script>
</body>
</html>

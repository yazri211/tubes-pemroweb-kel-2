<?php
session_start();
include '../conn.php';

define('DEV_MODE', true);

$error = false;
$error_msg = '';

$last_username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $last_username = $username;

    if ($username === '' || $password === '') {
        $error = true;
        $error_msg = 'Nama Pengguna dan kata sandi harus diisi.';
    } else {
        $username_safe = mysqli_real_escape_string($conn, $username);

        $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_safe' LIMIT 1");
        if (!$query) {
            $error = true;
            $error_msg = 'Terjadi kesalahan server. Silakan coba lagi.';
            if (DEV_MODE) {
                $error_msg .= ' (SQL error: ' . mysqli_error($conn) . ')';
            }
        } else {
            if (mysqli_num_rows($query) === 0) {
                $error = true;
                $error_msg = 'Nama Pengguna atau kata sandi salah.';
            } else {
                $user = mysqli_fetch_assoc($query);

                if (!isset($user['password']) || $user['password'] === '') {
                    $error = true;
                    $error_msg = 'Data pengguna tidak valid. Hubungi admin.';
                } else if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];

                    if ($user['role'] === "admin") {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../home.php");
                    }
                    exit();
                } else {
                    $error = true;
                    $error_msg = 'Nama Pengguna atau kata sandi salah.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Beauty Shop</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="container">
    <main class="login-card" role="main" aria-labelledby="loginTitle" id="loginCard">
      <div class="login-header">
        <div class="brand-pill">
          <img src="../assets/logo.jpg" alt="Logo Beauty Shop">
        </div>
        <div style="text-align:center">
          <h1 id="loginTitle">Masuk</h1>
          <div class="badge-pill">
            <span>Beauty Shop Dashboard</span>
          </div>
        </div>
      </div>

      <p class="subtitle">Gunakan akun kamu untuk melanjutkan.</p>

      <?php if ($error): ?>
        <div class="error" role="alert">
          <div class="error-icon">!</div>
          <div class="error-text">
            <?php echo htmlspecialchars($error_msg ?: 'Terjadi kesalahan.'); ?>
          </div>
        </div>
      <?php endif; ?>

      <form id="loginForm" action="" method="post" novalidate>
        <ul>
          <li>
            <div class="field-label">
              <label for="username">Nama Pengguna</label>
            </div>
            <div class="input-wrap">
              <input
                type="text"
                name="username"
                id="username"
                autocomplete="username"
                inputmode="text"
                required
                value="<?php echo htmlspecialchars($last_username); ?>"
                placeholder="contoh: beautyshop01"
              >
            </div>
          </li>

          <li>
            <div class="field-label">
              <label for="password">Kata Sandi</label>
            </div>
            <div class="input-wrap">
              <input type="password" name="password" id="password"
                     autocomplete="current-password" required placeholder="••••••••">
              <button type="button" class="toggle-pw" id="togglePw"
                      aria-pressed="false" aria-label="Tampilkan kata sandi">
                <!-- mata terbuka (disembunyikan awal) -->
                <svg class="icon-eye" viewBox="0 0 24 24" fill="none"
                     style="display:none" xmlns="http://www.w3.org/2000/svg"
                     aria-hidden="true">
                  <path
                    d="M2 12C4.5 8 7.5 6 12 6C16.5 6 19.5 8 22 12C19.5 16 16.5 18 12 18C7.5 18 4.5 16 2 12Z"
                    stroke="#ff6aa6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3.2"
                          stroke="#ff6aa6" stroke-width="2"/>
                </svg>
                <!-- mata tertutup (default) -->
                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
            <button type="submit" name="login" id="submitBtn">
              <span class="btn-text">Masuk</span>
            </button>
          </li>
        </ul>
      </form>

      <p class="help-text">
        Belum punya akun?
        <a href="register.php">Daftar sekarang</a>
      </p>
    </main>
  </div>
  <script src="js/login.js"></script>
</body>
</html>

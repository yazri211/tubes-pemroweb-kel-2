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
  <style>
    :root{
      --bg1: #ffdee9;
      --bg2: #b5fffc;
      --card-bg: #ffffff;
      --pink: #ff6aa6;
      --pink-soft: #ff9cc7;
      --muted: #666;
      --radius: 18px;
      --gap: 18px;
      --shadow-soft: 0 16px 40px rgba(0,0,0,0.10);
      --shadow-hover: 0 24px 60px rgba(0,0,0,0.16);
      --border-subtle: rgba(255,255,255,0.8);
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    *{box-sizing:border-box}

    html,body{
      height:100%;
      margin:0;
      padding:0;
      font-family: inherit;
      color:#111;
      background:
        radial-gradient(circle at 0% 0%, #ffe3f3 0, transparent 55%),
        radial-gradient(circle at 100% 100%, #d6f6ff 0, transparent 55%),
        linear-gradient(135deg, var(--bg1), var(--bg2));
      background-size: 140% 140%;
      animation:bgMove 18s ease-in-out infinite alternate;
      overflow:hidden;
    }

    @keyframes bgMove {
      0%{
        background-position:0% 0%, 100% 100%, 0% 50%;
      }
      100%{
        background-position:10% 5%, 90% 95%, 100% 50%;
      }
    }

    body::before,
    body::after{
      content:"";
      position:fixed;
      border-radius:999px;
      filter:blur(60px);
      opacity:0.55;
      z-index:0;
      pointer-events:none;
    }
    body::before{
      width:260px;
      height:260px;
      background:rgba(255,106,166,0.7);
      top:-60px;
      left:-40px;
    }
    body::after{
      width:280px;
      height:280px;
      background:rgba(136,200,255,0.8);
      bottom:-80px;
      right:-40px;
    }

    .container{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
      position:relative;
      z-index:1;
    }

    .login-card{
      width:100%;
      max-width:460px;
      background:
        radial-gradient(circle at 0% 0%, rgba(255,255,255,0.96), rgba(255,255,255,0.90)),
        linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.92));
      border-radius:var(--radius);
      padding:32px 32px 28px;
      box-shadow:var(--shadow-soft);
      border:1px solid var(--border-subtle);
      backdrop-filter: blur(14px);
      position:relative;
      overflow:hidden;
      transform-origin:50% 60%;
      opacity:0;
      transform:translateY(30px) scale(0.96);
    }

    .login-card.ready{
      animation:cardIn 0.8s cubic-bezier(.18,.89,.32,1.28) forwards;
    }

    .login-card::before{
      content:"";
      position:absolute;
      top:-80px;
      right:-120px;
      width:240px;
      height:240px;
      background:radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), rgba(255,154,205,0.25));
      opacity:0.7;
      pointer-events:none;
    }

    .login-header{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:10px;
      margin-bottom:24px;
      opacity:0;
      transform:translateY(12px);
    }

    form,
    .help-text{
      opacity:0;
      transform:translateY(12px);
    }

    .login-card.ready .login-header{
      animation:fadeUp 0.6s ease-out forwards;
      animation-delay:0.12s;
    }
    .login-card.ready form{
      animation:fadeUp 0.6s ease-out forwards;
      animation-delay:0.28s;
    }
    .login-card.ready .help-text{
      animation:fadeUp 0.6s ease-out forwards;
      animation-delay:0.44s;
    }

    .brand-pill{
      width:60px;
      height:60px;
      border-radius:999px;
      background:conic-gradient(from 210deg, #ff6aa6, #ffc1e1, #ff6aa6);
      display:flex;
      align-items:center;
      justify-content:center;
      box-shadow:0 12px 24px rgba(255,106,166,0.35);
      position:relative;
      overflow:hidden;
      animation:float 3s ease-in-out infinite;
    }

    .brand-pill img{
      width:70%;
      height:70%;
      object-fit:cover;
      border-radius:50%;
      display:block;
    }

    .login-card h1{
      margin:0;
      font-size:1.9rem;
      line-height:1.05;
      text-align:center;
      letter-spacing:0.04em;
      background:linear-gradient(120deg,#ff6aa6,#ff9cc7,#ffa8e6);
      -webkit-background-clip:text;
      background-clip:text;
      color:transparent;
    }

    .badge-pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:4px 10px;
      border-radius:999px;
      background:rgba(255,106,166,0.08);
      color:var(--pink);
      font-size:0.8rem;
      font-weight:600;
      margin-top:4px;
    }

    /* .badge-dot DIHAPUS */

    .subtitle{
      margin:10px 0 4px;
      color:var(--muted);
      font-size:0.98rem;
      text-align:center;
    }

    form ul{
      list-style:none;
      padding:0;
      margin:0;
      display:grid;
      gap:var(--gap);
    }

    .field-label{
      display:flex;
      align-items:center;
      justify-content:space-between;
      font-size:0.95rem;
      margin-bottom:6px;
      color:#333;
      font-weight:600;
    }
    .field-label small{
      font-size:0.78rem;
      color:var(--muted);
    }

    .input-wrap{
      position:relative;
      overflow:hidden;
      border-radius:11px;
    }

    input[type="text"],
    input[type="password"]{
      width:100%;
      padding:13px 16px;
      border-radius:11px;
      border:1px solid rgba(0,0,0,0.06);
      font-size:1rem;
      background:rgba(255,255,255,0.9);
      transition:border-color .16s ease, box-shadow .16s ease,
               transform .08s ease, background .16s ease;
    }

    input[type="password"]{
      padding-right:44px;
    }

    input[type="text"]:hover,
    input[type="password"]:hover{
      border-color:rgba(0,0,0,0.14);
      background:#f7f7f7;
    }

    input[type="text"]:focus,
    input[type="password"]:focus{
      outline:none;
      border-color:rgba(0,0,0,0.20);
      box-shadow:0 0 0 1px rgba(0,0,0,0.08),
                 0 8px 18px rgba(0,0,0,0.06);
      transform:translateY(-1px);
      background:#fff;
    }

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
      -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
      box-shadow:        0 0 0 1000px #ffffff inset !important;
      -webkit-text-fill-color: #111 !important;
      border-radius:11px;
      transition: background-color 5000s ease-in-out 0s;
    }

    .input-wrap::after{
      content:"";
      position:absolute;
      left:-40%;
      bottom:0;
      width:40%;
      height:2px;
      border-radius:999px;
      background:linear-gradient(90deg,rgba(255,106,166,0),rgba(255,106,166,0.9),rgba(255,106,166,0));
      transform:translateX(-110%);
      opacity:0;
      pointer-events:none;
    }
    .input-wrap.focused::after{
      animation:fieldUnderline 0.6s ease-out;
    }

    .toggle-pw{
      position:absolute;
      right:10px;
      top:50%;
      transform:translateY(-50%);
      background:transparent;
      border:0;
      color:var(--pink);
      cursor:pointer;
      padding:6px;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius:999px;
      transition:background .16s ease, transform .08s ease;
    }

    .toggle-pw:hover{
      background:rgba(255,106,166,0.08);
      transform:translateY(-50%) scale(1.02);
    }

    .toggle-pw:active{
      transform:translateY(-50%) scale(0.96);
    }

    .toggle-pw svg{
      width:20px;
      height:20px;
      display:block;
    }

    button[type="submit"]{
      width:100%;
      padding:14px 16px;
      border-radius:999px;
      border:0;
      background:linear-gradient(135deg,var(--pink),var(--pink-soft));
      color:white;
      font-weight:750;
      font-size:1.02rem;
      cursor:pointer;
      box-shadow:0 14px 30px rgba(255,106,166,0.35);
      display:flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      position:relative;
      overflow:hidden;
      transition:transform .14s cubic-bezier(.16,1,.3,1), box-shadow .14s ease, filter .18s ease;
    }

    button[type="submit"]::after{
      content:"";
      position:absolute;
      left:-40%;
      top:0;
      width:40%;
      height:100%;
      background:linear-gradient(to right, rgba(255,255,255,0.0), rgba(255,255,255,0.55), rgba(255,255,255,0.0));
      transform:skewX(-22deg) translateX(-120%);
      opacity:0;
      pointer-events:none;
    }

    button[type="submit"]:hover{
      transform:translateY(-1px) scale(1.01);
      box-shadow:var(--shadow-hover);
      filter:brightness(1.03);
    }

    button[type="submit"]:hover::after{
      animation:shine 0.9s ease-out;
    }

    button[type="submit"]:active{
      transform:translateY(1px) scale(0.985);
      box-shadow:0 10px 22px rgba(255,106,166,0.25);
    }

    button[disabled]{
      opacity:0.7;
      cursor:not-allowed;
      box-shadow:none;
      filter:grayscale(0.15);
    }

    .btn-loader{
      width:17px;
      height:17px;
      border-radius:50%;
      border:2px solid rgba(255,255,255,0.45);
      border-top-color:#fff;
      animation:spin .7s linear infinite;
    }

    .help-text{
      margin-top:14px;
      text-align:center;
      color:var(--muted);
      font-size:0.96rem;
    }

    .help-text a{
      color:var(--pink);
      text-decoration:none;
      font-weight:700;
      position:relative;
    }

    .help-text a:hover::after{
      transform:scaleX(1);
    }

    .help-text a::after{
      content:"";
      position:absolute;
      left:0;
      bottom:-2px;
      width:100%;
      height:2px;
      border-radius:999px;
      background:linear-gradient(90deg,var(--pink),var(--pink-soft));
      transform:scaleX(0);
      transform-origin:0 50%;
      transition:transform .18s ease;
    }

    .error{
      background:#fff0f6;
      color:#b0003a;
      padding:11px 12px;
      border-radius:11px;
      border:1px solid rgba(176,0,58,0.16);
      font-size:0.94rem;
      margin-bottom:12px;
      display:flex;
      gap:8px;
      align-items:flex-start;
      animation:shake .32s ease;
    }

    .error-icon{
      width:18px;
      height:18px;
      border-radius:999px;
      background:#b0003a;
      color:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:0.76rem;
      flex-shrink:0;
      margin-top:1px;
    }

    .error-text{
      flex:1;
    }

    @keyframes cardIn{
      0%{opacity:0; transform:translateY(30px) scale(0.96);}
      60%{opacity:1; transform:translateY(-4px) scale(1.01);}
      100%{opacity:1; transform:translateY(0) scale(1);}
    }

    @keyframes float{
      0%,100%{transform:translateY(0);}
      50%{transform:translateY(-5px);}
    }

    @keyframes shine{
      0%{transform:skewX(-22deg) translateX(-120%); opacity:0;}
      30%{opacity:1;}
      100%{transform:skewX(-22deg) translateX(260%); opacity:0;}
    }

    @keyframes spin{
      to{transform:rotate(360deg);}
    }

    @keyframes shake{
      0%{transform:translateX(0);}
      20%{transform:translateX(-4px);}
      40%{transform:translateX(4px);}
      60%{transform:translateX(-3px);}
      80%{transform:translateX(2px);}
      100%{transform:translateX(0);}
    }

    @keyframes fadeUp{
      0%{opacity:0; transform:translateY(12px);}
      100%{opacity:1; transform:translateY(0);}
    }

    @keyframes fieldUnderline{
      0%{transform:translateX(-130%); opacity:0;}
      20%{opacity:1;}
      100%{transform:translateX(260%); opacity:0;}
    }

    @media (max-width:520px){
      .container{padding:18px;}
      .login-card{
        padding:26px 20px 22px;
        max-width:420px;
        border-radius:16px;
      }
      .login-card h1{font-size:1.6rem;}
      input[type="text"], input[type="password"]{padding:11px 13px;font-size:0.96rem;}
      input[type="password"]{padding-right:40px;}
      button[type="submit"]{padding:12px;font-size:0.98rem;}
    }

    @media (max-width:380px){
      .login-card{
        padding:22px 16px 20px;
        max-width:360px;
        border-radius:14px;
      }
      .login-card h1{font-size:1.45rem;}
      form ul{gap:14px;}
      input[type="text"], input[type="password"]{padding:10px 11px;}
      input[type="password"]{padding-right:38px;}
      button[type="submit"]{padding:11px;font-size:0.96rem;}
    }
  </style>
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
            <!-- titik hijau dihapus, hanya teks -->
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

  <script>
    (function () {
      var form      = document.getElementById('loginForm');
      var username  = document.getElementById('username');
      var password  = document.getElementById('password');
      var toggle    = document.getElementById('togglePw');
      var submitBtn = document.getElementById('submitBtn');
      var card      = document.getElementById('loginCard');

      window.addEventListener('load', function () {
        if (card) {
          setTimeout(function(){
            card.classList.add('ready');
          }, 80);
        }
      });

      var wraps = document.querySelectorAll('.input-wrap');
      wraps.forEach(function(wrap){
        var inp = wrap.querySelector('input');
        if (!inp) return;
        inp.addEventListener('focus', function(){
          wrap.classList.add('focused');
        });
        inp.addEventListener('blur', function(){
          setTimeout(function(){ wrap.classList.remove('focused'); }, 200);
        });
      });

      toggle.addEventListener('click', function () {
        var eye    = toggle.querySelector('.icon-eye');
        var eyeOff = toggle.querySelector('.icon-eye-off');

        if (password.type === 'password') {
          password.type = 'text';
          toggle.setAttribute('aria-pressed', 'true');
          if (eye) eye.style.display = 'block';
          if (eyeOff) eyeOff.style.display = 'none';
          toggle.setAttribute('aria-label','Sembunyikan kata sandi');
        } else {
          password.type = 'password';
          toggle.setAttribute('aria-pressed', 'false');
          if (eye) eye.style.display = 'none';
          if (eyeOff) eyeOff.style.display = 'block';
          toggle.setAttribute('aria-label','Tampilkan kata sandi');
          password.focus();
        }
      });

      function showClientError(messages){
        var prev = form.querySelector('.client-error');
        if (prev) prev.remove();
        var box = document.createElement('div');
        box.className = 'error client-error';
        box.setAttribute('role','alert');

        var icon = document.createElement('div');
        icon.className = 'error-icon';
        icon.textContent = '!';

        var text = document.createElement('div');
        text.className = 'error-text';
        text.innerHTML = messages.map(function(s){
          return '<div>• ' + s + '</div>';
        }).join('');

        box.appendChild(icon);
        box.appendChild(text);

        form.prepend(box);
      }

      form.addEventListener('submit', function (e) {
        var prev = form.querySelector('.client-error');
        if (prev) prev.remove();

        var errors = [];
        if (!username.value.trim()) errors.push('Nama Pengguna tidak boleh kosong.');
        if (!password.value.trim()) errors.push('Kata Sandi tidak boleh kosong.');
        if (password.value && password.value.length > 0 &&
            password.value.length < 6) {
          errors.push('Kata Sandi minimal 6 karakter.');
        }

        if (errors.length) {
          e.preventDefault();
          showClientError(errors);
          if (!username.value.trim()) username.focus();
          else password.focus();
          return false;
        }

        submitBtn.disabled = true;
        var textSpan = submitBtn.querySelector('.btn-text');
        if (textSpan) textSpan.textContent = 'Memproses...';

        var loader = document.createElement('span');
        loader.className = 'btn-loader';
        submitBtn.appendChild(loader);
      });

      function updateSubmitState(){
        submitBtn.disabled = false;
        var textSpan = submitBtn.querySelector('.btn-text');
        if (textSpan) textSpan.textContent = 'Masuk';
        var loader = submitBtn.querySelector('.btn-loader');
        if (loader) loader.remove();
      }

      username.addEventListener('input', updateSubmitState);
      password.addEventListener('input', updateSubmitState);
      updateSubmitState();
    })();
  </script>
</body>
</html>

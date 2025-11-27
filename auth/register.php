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
            // Hash password & insert tanpa prepared statement
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

    /* .badge-dot dihapus, tidak dipakai lagi */

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
    input[type="password"],
    input[type="email"]{
      width:100%;
      padding:13px 16px;
      border-radius:11px;
      border:1px solid rgba(0,0,0,0.06);
      font-size:1rem;
      background:rgba(255,255,255,0.9);
      transition:border-color .16s ease, box-shadow .16s ease,
               transform .08s ease, background .16s ease;
    }

    input[type="text"]:hover,
    input[type="password"]:hover,
    input[type="email"]:hover{
      border-color:rgba(0,0,0,0.14);
      background:#f7f7f7;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="email"]:focus{
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

    .help-text a:hover::after{
      transform:scaleX(1);
    }

    .error, .error-box{
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

    .error-box{
      display:block;
      align-items:unset;
      gap:0;
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
      input[type="text"], input[type="password"], input[type="email"]{padding:11px 13px;font-size:0.96rem;}
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
      input[type="text"], input[type="password"], input[type="email"]{padding:10px 11px;}
      button[type="submit"]{padding:11px;font-size:0.96rem;}
    }
    </style>
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
            <!-- titik hijau dihapus, hanya teks -->
            <span>Beauty Shop Account</span>
          </div>
        </div>
      </div>

      <p class="subtitle">Buat akun untuk mengakses layanan kami.</p>

      <!-- Pesan server -->
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

  <script>
  (function () {
    var form      = document.getElementById('regForm');
    var username  = document.getElementById('username');
    var email     = document.getElementById('email');
    var password  = document.getElementById('password');
    var konfir    = document.getElementById('konfir_password');
    var submitBtn = document.getElementById('submitBtn');
    var card      = document.getElementById('regCard');

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

    document.querySelectorAll('.toggle-pw').forEach(function(btn){
      btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;

        var iconEye    = btn.querySelector('.icon-eye');
        var iconEyeOff = btn.querySelector('.icon-eye-off');
        var isKonfir   = (targetId === 'konfir_password');

        if (input.type === 'password') {
          input.type = 'text';
          btn.setAttribute('aria-pressed', 'true');
          if (iconEye) iconEye.style.display = 'block';
          if (iconEyeOff) iconEyeOff.style.display = 'none';
          btn.setAttribute(
            'aria-label',
            isKonfir ? 'Sembunyikan konfirmasi kata sandi' : 'Sembunyikan kata sandi'
          );
        } else {
          input.type = 'password';
          btn.setAttribute('aria-pressed', 'false');
          if (iconEye) iconEye.style.display = 'none';
          if (iconEyeOff) iconEyeOff.style.display = 'block';
          btn.setAttribute(
            'aria-label',
            isKonfir ? 'Tampilkan konfirmasi kata sandi' : 'Tampilkan kata sandi'
          );
          input.focus();
        }
      });
    });

    function showClientError(msg){
      var existing = document.querySelector('.error-box');
      if (existing) existing.remove();

      var formEl = document.getElementById('regForm');
      var box = document.createElement('div');
      box.className = 'error-box';
      box.setAttribute('role','alert');
      box.textContent = msg;

      formEl.parentNode.insertBefore(box, formEl);
    }

    if (form) {
      form.addEventListener('submit', function(e){
        var emailVal = email.value.trim();
        var userVal  = username.value.trim();
        var passVal  = password.value;
        var konfVal  = konfir.value;

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(emailVal)) {
          e.preventDefault();
          showClientError('Format email tidak valid!');
          email.focus();
          return;
        }

        if (userVal.length < 3) {
          e.preventDefault();
          showClientError('Nama Pengguna minimal 3 karakter.');
          username.focus();
          return;
        }

        if (passVal.length < 6) {
          e.preventDefault();
          showClientError('Kata Sandi minimal 6 karakter.');
          password.focus();
          return;
        }

        if (passVal !== konfVal) {
          e.preventDefault();
          showClientError('Konfirmasi kata sandi tidak sesuai.');
          konfir.focus();
          return;
        }

        submitBtn.disabled = true;
        var textSpan = submitBtn.querySelector('.btn-text');
        if (textSpan) textSpan.textContent = 'Mendaftarkan...';

        var loader = document.createElement('span');
        loader.className = 'btn-loader';
        submitBtn.appendChild(loader);
      });
    }

    ['username','email','password','konfir_password'].forEach(function(id){
      var el = document.getElementById(id);
      if (el) el.addEventListener('input', function(){
        var err = document.querySelector('.error-box');
        if (err) err.remove();
        if (submitBtn) {
          submitBtn.disabled = false;
          var textSpan = submitBtn.querySelector('.btn-text');
          if (textSpan) textSpan.textContent = 'Daftar';
          var loader = submitBtn.querySelector('.btn-loader');
          if (loader) loader.remove();
        }
      });
    });
  })();
  </script>
</body>
</html>

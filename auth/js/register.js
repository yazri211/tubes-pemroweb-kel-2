  (function () {
    // animasi card ready
    var card = document.getElementById('regCard');
    if (card) {
      window.addEventListener('load', function(){
        card.classList.add('ready');
      });
    }

    // underline animasi saat fokus
    document.querySelectorAll('.input-wrap input').forEach(function(input){
      input.addEventListener('focus', function(){
        input.parentElement.classList.add('focused');
      });
      input.addEventListener('blur', function(){
        input.parentElement.classList.remove('focused');
      });
    });

    // Toggle show/hide password & swap icons
    document.querySelectorAll('.toggle-pw').forEach(function(btn){
      btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;

        var iconEye = btn.querySelector('.icon-eye');
        var iconEyeOff = btn.querySelector('.icon-eye-off');

        if (input.type === 'password') {
          // jadi kelihatan
          input.type = 'text';
          btn.setAttribute('aria-pressed', 'true');
          if (iconEye) iconEye.style.display = 'block';   // tampilkan mata biasa
          if (iconEyeOff) iconEyeOff.style.display = 'none'; // sembunyikan mata silang
        } else {
          // disembunyikan lagi
          input.type = 'password';
          btn.setAttribute('aria-pressed', 'false');
          if (iconEye) iconEye.style.display = 'none';    // sembunyikan mata biasa
          if (iconEyeOff) iconEyeOff.style.display = 'block'; // tampilkan mata silang
        }
      });
    });

    // Client-side validation sebelum submit
    var form = document.getElementById('regForm');
    if (form) {
      form.addEventListener('submit', function(e){
        // bersihkan pesan error sebelumnya
        var existing = document.querySelector('.error-box');
        if (existing) existing.remove();

        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var konfir = document.getElementById('konfir_password').value;
        var username = document.getElementById('username').value.trim();

        // simple email regex (client-side only)
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          e.preventDefault();
          showClientError('Format email tidak valid!');
          document.getElementById('email').focus();
          return;
        }

        if (username.length < 3) {
          e.preventDefault();
          showClientError('Username minimal 3 karakter.');
          document.getElementById('username').focus();
          return;
        }

        if (password.length < 6) {
          e.preventDefault();
          showClientError('Password minimal 6 karakter.');
          document.getElementById('password').focus();
          return;
        }

        if (password !== konfir) {
          e.preventDefault();
          showClientError('Konfirmasi password tidak sesuai.');
          document.getElementById('konfir_password').focus();
          return;
        }
      });
    }

    function showClientError(msg) {
      var formEl = document.getElementById('regForm');
      var box = document.createElement('div');
      box.className = 'error-box';
      box.setAttribute('role','alert');
      box.innerText = msg;
      formEl.parentNode.insertBefore(box, formEl);
    }

    // Hapus error saat mengetik
    ['username','email','password','konfir_password'].forEach(function(id){
      var el = document.getElementById(id);
      if (el) el.addEventListener('input', function(){
        var err = document.querySelector('.error-box');
        if (err) err.remove();
      });
    });
  })();

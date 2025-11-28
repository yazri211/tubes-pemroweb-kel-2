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

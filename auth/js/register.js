
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

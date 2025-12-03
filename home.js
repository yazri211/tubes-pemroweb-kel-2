
function showToast(message, timeout = 2000) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = message;
    t.classList.add('show');
    clearTimeout(t._hideTimer);
    t._hideTimer = setTimeout(() => t.classList.remove('show'), timeout);
}

function updateCartCount() {
    fetch("cart/cart_count.php", {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(response => response.text())
        .then(data => {
            const el = document.getElementById("cart-count");
            if (el) el.innerText = data;
        })
        .catch(err => console.error('Gagal mengambil cart count:', err));
}
updateCartCount();

function currentPathWithQuery(){
    try {
        return window.location.pathname + window.location.search;
    } catch (e) {
        return window.location.href;
    }
}

(function(){
    const loginMenu = document.getElementById('loginMenu');
    if (!loginMenu) return;
    loginMenu.addEventListener('click', function(e){
        const returnTo = encodeURIComponent(currentPathWithQuery());
        const base = 'auth/login.php';
        loginMenu.href = base + '?return=' + returnTo;
    });
})();

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;

    const productId = btn.dataset.id;
    if (!productId) return;

    const logged = btn.getAttribute('data-logged') === '1';

    if (!logged) {
        const returnTo = encodeURIComponent(currentPathWithQuery());
        window.location.href = 'auth/login.php?return=' + returnTo;
        return;
    }

    // kalau disabled (stok habis), jangan lanjut
    if (btn.disabled) {
        return;
    }

    btn.disabled = true;
    btn.style.opacity = '0.6';

    const form = new URLSearchParams();
    form.append('product_id', productId);

    fetch('cart/cart_add.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: form.toString(),
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 401) {
            const returnTo = encodeURIComponent(currentPathWithQuery());
            window.location.href = 'auth/login.php?return=' + returnTo;
            throw new Error('Unauthorized');
        }
        if (!response.ok) {
            throw new Error('Server error');
        }
        return response.json();
    })
    .then(json => {
        if (json && json.success) {
            showToast('Berhasil menambahkan ' + json.added + ' item ke keranjang');
        } else {
            showToast('Gagal: ' + (json.message || 'Terjadi kesalahan'));
        }
        updateCartCount();
    })
    .catch(err => {
        if (err.message !== 'Unauthorized') {
            console.error('Gagal menambah keranjang:', err);
            showToast('Gagal menambahkan ke keranjang');
        }
    })
    .finally(() => {
        setTimeout(() => {
            btn.disabled = false;
            btn.style.opacity = '';
        }, 600);
    });
});

(function(){
    const navSearchWrap = document.getElementById('navSearch');
    const searchInput = navSearchWrap ? navSearchWrap.querySelector('.search-input') : null;

    if (searchInput && navSearchWrap) {
        searchInput.addEventListener('focus', () => navSearchWrap.classList.add('expanded'));
        searchInput.addEventListener('blur', () => {
            setTimeout(() => navSearchWrap.classList.remove('expanded'), 120);
        });

        document.addEventListener('click', (e) => {
            if (!navSearchWrap.contains(e.target)) {
                navSearchWrap.classList.remove('expanded');
            }
        });
    }
})();

(function(){
    const profileBtn = document.getElementById('profileBtn');
    const dropdown = document.getElementById('profileDropdown');

    if (!profileBtn || !dropdown) return;

    function openDropdown() {
        dropdown.classList.add('show');
        profileBtn.setAttribute('aria-expanded', 'true');
        const first = dropdown.querySelector('[role="menuitem"]');
        if (first) first.focus();
    }
    function closeDropdown() {
        dropdown.classList.remove('show');
        profileBtn.setAttribute('aria-expanded', 'false');
        profileBtn.focus();
    }
    function toggleDropdown() {
        if (dropdown.classList.contains('show')) closeDropdown();
        else openDropdown();
    }

    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && e.target !== profileBtn) {
            if (dropdown.classList.contains('show')) closeDropdown();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (dropdown.classList.contains('show')) closeDropdown();
        }
    });

    dropdown.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const target = document.activeElement;
            if (target && target.getAttribute('role') === 'menuitem') {
                target.click();
            }
        }
    });
})();

document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        const active = document.activeElement;
        if (active && active.closest && active.closest('.product-card')) {
            const link = active.closest('.product-card').querySelector('a[href^="detail_produk.php"]');
            if (link) link.click();
        }
    }
});

(function(){
    const input = document.getElementById("searchInput");
    const clearBtn = document.getElementById("clearSearch");

    if (!input || !clearBtn) return;

    function toggleClear() {
        clearBtn.style.display = input.value.length > 0 ? "block" : "none";
    }

    input.addEventListener("input", toggleClear);

    // Saran 2: clear search hanya hapus parameter q, kategori tetap
    clearBtn.addEventListener("click", function(e){
        e.preventDefault();
        input.value = "";
        toggleClear();

        try {
            const url = new URL(window.location.href);
            let changed = false;

            if (url.searchParams.has('q')) {
                url.searchParams.delete('q');
                changed = true;
            }

            if (changed) {
                const cleaned = url.pathname + (url.search ? url.search : '');
                window.location.assign(cleaned);
                return;
            }
        } catch (err) {
            try { window.location.href = window.location.pathname; return; } catch (e) {}
        }
        input.focus();
    });

    clearBtn.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            clearBtn.click();
        }
    });

    toggleClear();
})();

(function(){
    const navSearchWrap = document.getElementById('navSearch');
    const searchInput = navSearchWrap ? navSearchWrap.querySelector('.search-input') : null;
    const categoryLinks = document.querySelectorAll('.categories a');

    if (categoryLinks && categoryLinks.length) {
        categoryLinks.forEach(link => {
            link.addEventListener('click', () => {
                try {
                    if (searchInput) searchInput.blur();
                    if (navSearchWrap) navSearchWrap.classList.remove('expanded');
                } catch (e) {}
            });
        });
    }

    document.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && document.activeElement) {
            const a = document.activeElement.closest && document.activeElement.closest('.categories a');
            if (a) {
                if (searchInput) searchInput.blur();
                if (navSearchWrap) navSearchWrap.classList.remove('expanded');
            }
        }
    });
})();

(function(){
    const productContainer = document.querySelector('.product');

    if (!productContainer) return;

    function updateSingleClass() {
        const cards = productContainer.querySelectorAll('.product-card');
        const count = cards.length;

        productContainer.classList.remove('single', 'count-2', 'count-3');

        if (count === 1) {
            productContainer.classList.add('single');
        } else if (count === 2) {
            productContainer.classList.add('count-2');
        } else if (count === 3) {
            productContainer.classList.add('count-3');
        }
    }

    updateSingleClass();

    try {
        const mo = new MutationObserver((mutations) => {
            clearTimeout(productContainer._singleTimer);
            productContainer._singleTimer = setTimeout(updateSingleClass, 40);
        });
        mo.observe(productContainer, { childList: true, subtree: false });
    } catch (e) {
        window.addEventListener('resize', updateSingleClass);
    }
})();

(function () {
    const track = document.getElementById('heroTrack');
    if (!track) return;

    const slides = Array.from(track.querySelectorAll('.hero-slide'));
    const prevBtn = document.querySelector('.hero-prev');
    const nextBtn = document.querySelector('.hero-next');
    const dotsWrap = document.getElementById('heroDots');

    const total = slides.length;
    if (total === 0) return;

    let current = 0;
    let autoTimer = null;
    const AUTO_INTERVAL = 6000;

    const dots = [];
    if (dotsWrap && total > 1) {
        for (let i = 0; i < total; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'hero-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Pergi ke slide ' + (i + 1));
            dot.addEventListener('click', () => goTo(i));
            dotsWrap.appendChild(dot);
            dots.push(dot);
        }
    }

    function updateView() {
        const offset = -current * 100;
        track.style.transform = 'translateX(' + offset + '%)';
        dots.forEach((d, i) => {
            if (i === current) d.classList.add('active');
            else d.classList.remove('active');
        });
    }

    function goTo(idx) {
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        current = idx;
        updateView();
        restartAuto();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    if (nextBtn) nextBtn.addEventListener('click', next);
    if (prevBtn) prevBtn.addEventListener('click', prev);

    function startAuto() {
        if (total <= 1) return;
        stopAuto();
        autoTimer = setInterval(next, AUTO_INTERVAL);
    }
    function stopAuto() {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    }
    function restartAuto() {
        stopAuto();
        startAuto();
    }

    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);

    let startX = null;
    let dragging = false;

    function onTouchStart(e) {
        const touch = e.touches ? e.touches[0] : e;
        startX = touch.clientX;
        dragging = true;
    }
    function onTouchEnd(e) {
        if (!dragging || startX === null) return;
        const endX = (e.changedTouches ? e.changedTouches[0].clientX : e.clientX);
        const diff = endX - startX;
        const threshold = 50;

        if (Math.abs(diff) > threshold) {
            if (diff < 0) next();
            else prev();
        }

        dragging = false;
        startX = null;
    }

    track.addEventListener('touchstart', onTouchStart, { passive: true });
    track.addEventListener('touchend', onTouchEnd);
    track.addEventListener('mousedown', (e) => { e.preventDefault(); onTouchStart(e); });
    track.addEventListener('mouseup', onTouchEnd);
    track.addEventListener('mouseleave', () => { dragging = false; startX = null; });

    updateView();
    startAuto();
})();

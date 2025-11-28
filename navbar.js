// Cart count updater
function updateCartCount() {
    var url = window.location.origin + '/kel%202%20proweb/cart/cart_count.php';
    fetch(url)
        .then(function (response) { return response.text(); })
        .then(function (data) {
            var el = document.getElementById('cart-count');
            if (el) el.innerText = data.trim();
        })
        .catch(function (err) {
            console.error('Gagal mengambil cart count:', err);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    
    // Profile dropdown toggle
    var profileBtn = document.getElementById('profileBtn');
    var profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
            profileBtn.setAttribute('aria-expanded', profileDropdown.classList.contains('show'));
        });
        document.addEventListener('click', function () {
            profileDropdown.classList.remove('show');
            profileBtn.setAttribute('aria-expanded', 'false');
        });
    }

    // Clear search button
    var clearBtn = document.getElementById('clearSearch');
    var searchInput = document.getElementById('searchInput');
    if (clearBtn && searchInput) {
        searchInput.addEventListener('input', function () {
            clearBtn.style.display = searchInput.value ? 'block' : 'none';
        });
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
        });
    }
});

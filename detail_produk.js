function updateCartCount() {
    fetch("cart/cart_count.php")
        .then(response => response.text())
        .then(data => {
            const el = document.getElementById("cart-count");
            if (el) el.innerText = data;
        })
        .catch(err => {
            console.error('Gagal mengambil cart count:', err);
        });
}

updateCartCount();

(function(){
    const stock = STOCK_DATA;
    const qtyInput = document.getElementById('qty-input');
    const btnInc = document.getElementById('qty-increase');
    const btnDec = document.getElementById('qty-decrease');
    const addBtn = document.getElementById('add-to-cart-btn');

    if (qtyInput) {
        qtyInput.setAttribute('max', stock > 0 ? stock : 1);
        if (stock === 0) qtyInput.value = 0;
        else if (parseInt(qtyInput.value) < 1) qtyInput.value = 1;
        else if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;
    }

    if (stock === 0 && addBtn) {
        addBtn.disabled = true;
    }

    function clampQty(val) {
        val = parseInt(val) || 0;
        if (val < 1) val = 1;
        if (stock > 0 && val > stock) val = stock;
        return val;
    }

    btnInc && btnInc.addEventListener('click', function(){
        let v = clampQty(qtyInput.value) + 1;
        if (stock > 0 && v > stock) v = stock;
        qtyInput.value = v;
    });

    btnDec && btnDec.addEventListener('click', function(){
        let v = clampQty(qtyInput.value) - 1;
        if (v < 1) v = 1;
        qtyInput.value = v;
    });

    qtyInput && qtyInput.addEventListener('input', function(){
        let v = qtyInput.value.replace(/[^\d]/g,'');
        qtyInput.value = v;
    });

    qtyInput && qtyInput.addEventListener('change', function(){
        let v = clampQty(qtyInput.value);
        qtyInput.value = v;
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('.add-to-cart')) {
            e.stopPropagation();

            const productId = e.target.dataset.id;
            let qty = parseInt(qtyInput.value) || 1;

            if (stock === 0) {
                alert('Stok habis.');
                return;
            }
            if (qty < 1) qty = 1;
            if (qty > stock) qty = stock;

            const yakin = confirm("Yakin ingin menambahkan " + qty + " item ke keranjang?");
            if (!yakin) return;

            e.target.disabled = true;

            fetch("cart/cart_add.php?id=" + encodeURIComponent(productId) + "&qty=" + encodeURIComponent(qty))
                .then(response => response.json())
                .then(json => {
                    if (json && json.success) {
                        alert("Berhasil menambahkan " + json.added + " item ke keranjang!");
                    } else {
                        alert("Gagal: " + (json.message || "Unknown error"));
                    }
                    updateCartCount();
                })
                .catch(err => {
                    console.error('Gagal menambah keranjang:', err);
                    alert('Terjadi kesalahan saat menambahkan ke keranjang.');
                })
                .finally(() => {
                    if (!e.target.hasAttribute('data-perm')) e.target.disabled = false;
                });
        }
    });
})();

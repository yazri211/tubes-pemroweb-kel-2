function updateTotal() {
    let subtotal = Number(document.getElementById('baseTotal').value) || 0;
    let adminFee = 5000;
    let pengirimanEl = document.getElementById('pengiriman');
    let shippingCost = 0;

    if (pengirimanEl && pengirimanEl.value) {
        let parts = pengirimanEl.value.split("|");
        if (parts.length > 1) {
            shippingCost = Number(parts[1]) || 0;
        }
    }

    let finalTotal = subtotal + shippingCost + adminFee;

    const format = (n) => n.toLocaleString('id-ID');

    const subEl = document.getElementById('subtotalDisplay');
    if (subEl) subEl.innerText = "Rp " + format(subtotal);

    const ongkirDisplay = document.getElementById('ongkirDisplay');
    if (ongkirDisplay) ongkirDisplay.innerText = "Rp " + format(shippingCost);

    const adminDisplay = document.getElementById('adminDisplay');
    if (adminDisplay) adminDisplay.innerText = "Rp " + format(adminFee);

    const finalTotalEl = document.getElementById('finalTotal');
    if (finalTotalEl) finalTotalEl.innerText = "Rp " + format(finalTotal);

    const hidden = document.getElementById('total_final_input');
    if (hidden) hidden.value = finalTotal;
}

document.addEventListener('DOMContentLoaded', function(){
    updateTotal();
});

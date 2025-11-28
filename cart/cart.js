function formatRupiah(num) {
    return "Rp " + num.toLocaleString("id-ID");
}

function updateSelectedItemsAndTotal() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    const totalDisplayDesktop = document.getElementById("totalDisplayDesktop");
    const totalDisplayMobile = document.getElementById("totalDisplayMobile");
    const selectedIdsField = document.getElementById("selectedIds");
    const selectAllDesktop = document.getElementById("selectAllDesktop");
    const selectAllMobile  = document.getElementById("selectAllMobile");

    let total = 0;
    let ids = [];
    let checkedCount = 0;

    checkboxes.forEach(ch => {
        if (ch.checked) {
            checkedCount++;
            const row = ch.closest("tr");
            if (!row) return;

            const hargaCell = row.querySelector('td[data-label="Harga"] .price-text');
            const qtyInput = row.querySelector('input[name="quantity"]');

            if (!hargaCell || !qtyInput) return;

            const priceText = hargaCell.textContent.replace(/[Rp.\s]/g, '');
            const price = parseInt(priceText || "0", 10);
            const qty = parseInt(qtyInput.value || "0", 10);

            if (!isNaN(price) && !isNaN(qty)) {
                total += price * qty;
                ids.push(ch.value);
            }
        }
    });

    if (totalDisplayDesktop) {
        totalDisplayDesktop.textContent = formatRupiah(total);
    }
    if (totalDisplayMobile) {
        totalDisplayMobile.textContent = formatRupiah(total);
    }

    if (selectedIdsField) {
        selectedIdsField.value = ids.join(",");
    }

    // sinkron state "pilih semua" desktop & mobile
    const allChecked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
    if (selectAllDesktop) selectAllDesktop.checked = allChecked;
    if (selectAllMobile)  selectAllMobile.checked  = allChecked;
}

// event change item / qty
document.addEventListener("change", function(e){
    if (e.target.matches('input[name="selected[]"]') || e.target.matches('input[name="quantity"]')) {
        updateSelectedItemsAndTotal();
    }
});

// validasi submit
const checkoutForm = document.getElementById("checkoutForm");
if (checkoutForm) {
    checkoutForm.addEventListener("submit", function(e){
        const selectedIdsField = document.getElementById("selectedIds");
        if (!selectedIdsField || selectedIdsField.value.trim() === "") {
            e.preventDefault();
            alert("Silakan pilih minimal 1 produk terlebih dahulu.");
        }
    });
}

// handler pilih semua (desktop & mobile)
function handleSelectAllChange(checked) {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = checked);
    updateSelectedItemsAndTotal();
}

const selectAllDesktop = document.getElementById("selectAllDesktop");
if (selectAllDesktop) {
    selectAllDesktop.addEventListener("change", function() {
        handleSelectAllChange(this.checked);
    });
}

const selectAllMobile = document.getElementById("selectAllMobile");
if (selectAllMobile) {
    selectAllMobile.addEventListener("change", function() {
        handleSelectAllChange(this.checked);
    });
}

updateSelectedItemsAndTotal();

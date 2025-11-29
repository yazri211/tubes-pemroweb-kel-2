function toggleEdit(id) {
    const form = document.getElementById('edit-' + id);
    if (!form) return;
    form.classList.toggle('show');
}
// Toggle stok form 
function toggleStock(id) {
    var el = document.getElementById('stock-' + id);
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}

if (typeof toggleEdit !== 'function') {
    function toggleEdit(id) {
        var el = document.getElementById('edit-' + id);
        if (!el) return;
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }
}

function toggleEdit(id) {
    const form = document.getElementById('edit-' + id);
    if (!form) return;
    form.classList.toggle('show');
}

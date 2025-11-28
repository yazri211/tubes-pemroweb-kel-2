const searchInput = document.getElementById('searchInput');
const clearSearch = document.getElementById('clearSearch');
const table = document.getElementById('userTable').getElementsByTagName('tbody')[0];

function filterTable() {
    const filter = searchInput.value.toLowerCase();
    const rows = table.getElementsByTagName('tr');
    let hasFilter = filter.trim() !== '';

    clearSearch.style.display = hasFilter ? 'block' : 'none';

    for (let i = 0; i < rows.length; i++) {
        const usernameCell = rows[i].getElementsByTagName('td')[1];
        if (!usernameCell) continue;

        const text = usernameCell.textContent || usernameCell.innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterTable);
}

if (clearSearch) {
    clearSearch.addEventListener('click', function () {
        searchInput.value = '';
        filterTable();
        searchInput.focus();
    });
}

// Auto hide alert setelah beberapa detik
const alertBox = document.getElementById('alertBox');
if (alertBox) {
    setTimeout(() => {
        if (alertBox) alertBox.style.display = 'none';
    }, 4000);
}

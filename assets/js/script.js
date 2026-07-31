document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length) {
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    }

    const toastElList = document.querySelectorAll('.toast');
    if (toastElList.length) {
        [...toastElList].map(el => new bootstrap.Toast(el));
    }
});

function confirmDelete(event, message) {
    if (!confirm(message || 'Yakin ingin menghapus data ini?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

function formatRupiah(value) {
    return 'Rp ' + Number(value).toLocaleString('id-ID');
}

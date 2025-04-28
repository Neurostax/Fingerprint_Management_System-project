$(document).ready(function() {
    // Initialize DataTables if present
    if ($('.table').length) {
        $('.table').DataTable();
    }
    // Bootstrap tooltips (optional, if used)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}); 
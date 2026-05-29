/**
 * pages/lead-index.js — Controller cho Lead index (kanban + listing)
 * Logic kanban, filter, quick-search nếu cần.
 */

// Placeholder — thêm logic khi index page cần Alpine controller
// hoặc JS phức tạp ngoài Tabulator/DataTables đã có trong core.

// Ví dụ: filter panel
document.addEventListener('alpine:init', () => {
    Alpine.data('leadFilter', () => ({
        open: false,
        toggle() { this.open = !this.open; },
    }));
});

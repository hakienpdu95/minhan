/**
 * pages/organization-form.js
 * Controller cho create.blade.php và edit.blade.php của module Organization.
 *
 * Form Organization không dùng Alpine — dùng data-org-form + initFormValidation.
 * initFormValidation và initJoditAll là globals từ core bundle (app.js + jodit.js).
 */

document.addEventListener('DOMContentLoaded', () => {
    // Chỉ chạy khi đang ở trang có form tổ chức
    if (!document.querySelector('[data-org-form]')) return;

    initFormValidation('[data-org-form]');

    // initJoditAll chỉ available khi jodit.js đã load (blade push trước file này)
    if (document.querySelector('.jodit-editor') && typeof initJoditAll === 'function') {
        initJoditAll('.jodit-editor');
    }
});

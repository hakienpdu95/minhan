/**
 * pages/branch-form.js
 * JS cho form tạo/sửa chi nhánh (create.blade.php + edit.blade.php).
 *
 * Xử lý: Province → Ward cascade cho form địa chỉ.
 * Globals: window.TomSelect (từ tom-select.js bundle)
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Province → Ward cascade (create form) ────────────────────────────────
    const provinceEl = document.getElementById('form-province');
    const wardEl     = document.getElementById('form-ward');

    if (provinceEl && wardEl) {
        initProvinceWardCascade(provinceEl, wardEl);
    }

    // ── Province → Ward cascade (edit form) ──────────────────────────────────
    const editProvinceEl = document.getElementById('edit-form-province');
    const editWardEl     = document.getElementById('edit-form-ward');

    if (editProvinceEl && editWardEl) {
        const currentWard = editWardEl.getAttribute('data-selected') || editWardEl.querySelector('option[selected]')?.value || '';
        initProvinceWardCascade(editProvinceEl, editWardEl, currentWard);
    }
});

/**
 * Khởi tạo cascade province → ward với TomSelect.
 * Nếu TomSelect chưa load (form nhẹ không cần), dùng native <select> onChange.
 */
function initProvinceWardCascade(provinceEl, wardEl, initialWard) {
    const wardsCache = {};

    // Lấy current selected province (restore từ old() hoặc model binding)
    const currentProvince = provinceEl.value;
    if (currentProvince) {
        loadFormWards(currentProvince, wardEl, initialWard || wardEl.value, wardsCache);
    }

    provinceEl.addEventListener('change', function () {
        const code = this.value;
        // Reset ward
        wardEl.innerHTML = '<option value="">Chọn phường/xã...</option>';
        wardEl.value = '';

        if (!code) {
            wardEl.disabled = true;
            wardEl.innerHTML = '<option value="">Chọn tỉnh trước...</option>';
            return;
        }

        loadFormWards(code, wardEl, null, wardsCache);
    });
}

function loadFormWards(code, wardEl, pendingWard, cache) {
    if (cache[code]) {
        applyFormWards(cache[code], wardEl, pendingWard);
        return;
    }

    wardEl.disabled = true;
    wardEl.innerHTML = '<option value="">Đang tải...</option>';

    const apiBase = document.querySelector('meta[name=wards-api]')?.content
        || window.location.origin + '/api/provinces';

    fetch(apiBase + '/' + encodeURIComponent(code) + '/wards', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            cache[code] = data;
            applyFormWards(data, wardEl, pendingWard);
        })
        .catch(() => {
            wardEl.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        });
}

function applyFormWards(wards, wardEl, pendingWard) {
    wardEl.innerHTML = '<option value="">Chọn phường/xã...</option>';
    wards.forEach(w => {
        const opt = document.createElement('option');
        opt.value = w.ward_code;
        opt.textContent = w.name;
        if (pendingWard && w.ward_code === pendingWard) opt.selected = true;
        wardEl.appendChild(opt);
    });
    wardEl.disabled = false;
}

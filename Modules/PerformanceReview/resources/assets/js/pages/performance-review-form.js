/**
 * Modules/PerformanceReview/resources/assets/js/pages/performance-review-form.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *   2. Tab-aware submit guard — phát hiện required field trống ở tab ẩn
 *   3. Template TomSelect — khởi tạo thủ công, kết nối onChange → Alpine selectTemplate()
 *   4. Static TomSelect — auto-init mọi select.ts-init còn lại
 *   5. Flatpickr — khởi tạo date picker cho period_start / period_end
 *
 * Requires globals (core): initFormValidation, window.Alpine, window.Toast
 * Requires globals (lazy): window.TomSelect (tom-select.js), initDatePicker (flatpickr.js)
 */

import { createTs, initAllTomSelects } from '@shared/tom-select-factory.js';

const FORM_SEL     = '[data-performance-review-form]';
const RE_TAB_XSHOW = /tab\s*===\s*['"](\w+)['"]/;  // compile 1 lần

// ── Entry point ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
    _setupTabGuard(form);
    initAllTomSelects(form);
    _initTemplateTomSelect(form);
    _initFlatpickr(form);
    _initJodit(form);
});

// ── Template TomSelect (manual — cần onChange → Alpine) ───────────────────────

function _initTemplateTomSelect(form) {
    const el = form.querySelector('[name="template_id"]');
    if (!el || el.tomselect) return;

    let alpineRoot = null;

    createTs(el, {
        placeholder: '— Chọn mẫu đánh giá —',
        onChange(val) {
            try {
                alpineRoot ??= form.closest('[x-data]') ?? document.querySelector('[x-data]');
                const data = window.Alpine?.$data(alpineRoot);
                data?.selectTemplate?.(val ?? '');
            } catch { /* Alpine not ready */ }
        },
    });
}

// ── Jodit rich-text editors ───────────────────────────────────────────────────

function _initJodit(form) {
    if (form.querySelector('.jodit-editor') && typeof initJoditAll === 'function') {
        initJoditAll('.jodit-editor');
    }
}

// ── Flatpickr date pickers ─────────────────────────────────────────────────────

function _initFlatpickr(form) {
    if (typeof initDatePicker !== 'function') return;
    const opts = { altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d' };
    const start = form.querySelector('#fp-period-start');
    const end   = form.querySelector('#fp-period-end');
    if (start) initDatePicker(start, opts);
    if (end)   initDatePicker(end, opts);
}

// ── Tab-aware submit guard ─────────────────────────────────────────────────────

function _setupTabGuard(form) {
    let wrapper = null;

    form.addEventListener('submit', (e) => {
        const errors = _collectHiddenErrors(form);
        if (!errors.size) return;

        e.preventDefault();
        wrapper ??= form.closest('[x-data]') ?? document.querySelector('[x-data]');
        _switchAlpineTab(wrapper, errors.keys().next().value);
        _toastHiddenErrors(errors);
    }, /* capture */ true);
}

function _collectHiddenErrors(form) {
    const map = new Map();

    for (const field of form.querySelectorAll('[data-req]')) {
        if (field.value.trim()) continue;

        const panel = field.closest('[x-show]');
        if (!panel || panel.style.display !== 'none') continue;

        const tabKey = RE_TAB_XSHOW.exec(panel.getAttribute('x-show') ?? '')?.[1];
        if (!tabKey) continue;

        if (!map.has(tabKey)) {
            map.set(tabKey, { label: panel.dataset.tabLabel ?? tabKey, fields: [] });
        }
        map.get(tabKey).fields.push(_resolveFieldLabel(field));
    }

    return map;
}

function _resolveFieldLabel(field) {
    const labelText = field.closest('.form-control')
        ?.querySelector('.label-text')
        ?.textContent.replace(/\s*\*\s*$/, '').trim();
    return labelText || field.placeholder || field.name || 'Trường bắt buộc';
}

function _switchAlpineTab(wrapper, tabKey) {
    if (!wrapper) return;
    try {
        const data = window.Alpine?.$data(wrapper);
        if (data?.tab !== undefined) data.tab = tabKey;
    } catch { /* Alpine not ready */ }
}

function _toastHiddenErrors(errors) {
    if (!window.Toast) return;
    const lines = Array.from(errors.values(), ({ label, fields }) =>
        `${label}: ${fields.join(', ')}`
    );
    Toast.warning(`Còn thiếu thông tin bắt buộc:\n${lines.join('\n')}`, { duration: 5000 });
}

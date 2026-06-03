/**
 * Modules/Project/resources/assets/js/pages/project-form.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *   2. Tab-aware submit guard — phát hiện required field trống ở tab ẩn
 *   3. TomSelect — auto-init mọi select.ts-init trong form (bao gồm sidebar)
 *   4. Flatpickr — khởi tạo date picker cho start_date / end_date
 *
 * Requires globals (core): initFormValidation, window.Alpine, window.Toast
 * Requires globals (lazy): window.TomSelect (tom-select.js), initDatePicker (flatpickr.js)
 */

import { initAllTomSelects } from '@shared/tom-select-factory.js';

const FORM_SEL     = '[data-project-form]';
const RE_TAB_XSHOW = /tab\s*===\s*['"](\w+)['"]/;  // compile 1 lần

// ── Entry point ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
    _setupTabGuard(form);
    initAllTomSelects(form);
    _initFlatpickr(form);
});

// ── Flatpickr date pickers ─────────────────────────────────────────────────────

function _initFlatpickr(form) {
    if (typeof initDatePicker !== 'function') return;
    const opts = { altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d' };
    const start = form.querySelector('#fp-start-date');
    const end   = form.querySelector('#fp-end-date');
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

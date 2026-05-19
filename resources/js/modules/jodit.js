/**
 * resources/js/modules/jodit.js
 * Jodit rich-text editor v4 wrapper (~500KB — tải lazy per-page)
 *
 * Blade:  @vite(['resources/js/modules/jodit.js'])
 * Use:    initJodit('#myTextarea', { ...options })
 */
import { Jodit } from 'jodit';
import 'jodit/es2021/jodit.min.css';

const DEFAULTS = {
    language: 'vi',
    theme:    'default',
    height:   400,
    toolbar:  true,
    buttons: [
        'bold','italic','underline','strikethrough','|',
        'ul','ol','|',
        'font','fontsize','paragraph','|',
        'image','link','|',
        'align','|',
        'undo','redo','|',
        'hr','fullsize','source',
    ],
    removeButtons: ['about','classSpan'],
    showCharsCounter: false,
    showWordsCounter: false,
    showXPathInStatusbar: false,
    spellcheck: false,
    uploader: { insertImageAsBase64URI: true },
};

const JoditInstances = new Map();
window.JoditInstances = JoditInstances;

function initJodit(selector, options = {}) {
    const el = typeof selector === 'string'
        ? document.querySelector(selector)
        : selector;
    if (!el) { console.warn('[Jodit] Element not found:', selector); return null; }

    const editor = Jodit.make(el, { ...DEFAULTS, ...options });
    JoditInstances.set(typeof selector === 'string' ? selector : el.id, editor);
    return editor;
}

/**
 * Client-side required-field validation for forms using DaisyUI styling.
 *
 * Mark fields with data-req="Error message to show".
 * Add novalidate + data-org-form (or any selector) to the <form>.
 *
 * Behaviour:
 *  - blur  → validate the single field that lost focus
 *  - input → clear error for that field once it has a value
 *  - submit → validate all, block submission if any fail
 */
function initOrgFormValidation(formSelector) {
    const form = typeof formSelector === 'string'
        ? document.querySelector(formSelector)
        : formSelector;
    if (!form) return;

    const fields = () => form.querySelectorAll('[data-req]');

    fields().forEach(f => {
        f.addEventListener('blur',  () => _validate(f));
        f.addEventListener('input', () => _clear(f));
        f.addEventListener('change', () => { if (f.value.trim()) _clear(f); });
    });

    form.addEventListener('submit', e => {
        // Sync Jodit editors before validation
        if (window.JoditInstances) {
            JoditInstances.forEach(ed => ed.synchronizeValues?.());
        }

        let invalid = 0;
        fields().forEach(f => { if (!_validate(f)) invalid++; });

        if (invalid) {
            e.preventDefault();
            // Scroll to first error
            const first = form.querySelector('.org-req-msg');
            first?.closest('.form-control')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    function _validate(field) {
        if (field.value.trim()) { _clear(field); return true; }
        _showError(field, field.dataset.req || 'Trường này là bắt buộc');
        return false;
    }

    function _showError(field, msg) {
        _clear(field);
        field.classList.add('input-error');
        const p = document.createElement('p');
        p.className = 'org-req-msg mt-1 text-xs text-error';
        p.textContent = msg;
        field.closest('.form-control').appendChild(p);
    }

    function _clear(field) {
        field.classList.remove('input-error');
        field.closest('.form-control')?.querySelector('.org-req-msg')?.remove();
    }
}

window.initJodit              = initJodit;
window.initOrgFormValidation  = initOrgFormValidation;
window.Jodit                  = Jodit;
export { initJodit, initOrgFormValidation, JoditInstances };
export default Jodit;

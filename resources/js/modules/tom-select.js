/**
 * resources/js/modules/tom-select.js
 * Tom Select v2 wrapper — searchable select/autocomplete
 *
 * Blade:  @vite(['resources/js/modules/tom-select.js'])
 * Use:    initTomSelect('#mySelect', { ...options })
 *         initTagsInput('#myInput', options)
 */
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

const DEFAULTS = {
    create:       false,
    sortField:    { field: 'text', direction: 'asc' },
    placeholder:  'Chọn...',
    searchField:  ['text', 'value'],
    maxOptions:   200,
    render: {
        no_results: () => `<div class="no-results" style="padding:8px 12px;font-size:13px;color:#94a3b8">Không tìm thấy kết quả</div>`,
    },
};

const TomSelectInstances = new Map();
window.TomSelectInstances = TomSelectInstances;

function initTomSelect(selector, options = {}) {
    const el = typeof selector === 'string'
        ? document.querySelector(selector)
        : selector;
    if (!el) { console.warn('[TomSelect] Element not found:', selector); return null; }

    const ts = new TomSelect(el, { ...DEFAULTS, ...options });
    TomSelectInstances.set(typeof selector === 'string' ? selector : el.id, ts);
    return ts;
}

/** Tags mode: nhập text → tạo tag */
function initTagsInput(selector, options = {}) {
    return initTomSelect(selector, {
        create:      true,
        createOnBlur:true,
        delimiter:   ',',
        persist:     false,
        placeholder: 'Nhập và Enter để thêm tag...',
        ...options,
    });
}

window.initTomSelect  = initTomSelect;
window.initTagsInput  = initTagsInput;
window.TomSelect      = TomSelect;
export { initTomSelect, initTagsInput, TomSelectInstances };
export default TomSelect;

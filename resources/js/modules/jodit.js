/**
 * resources/js/modules/jodit.js
 * Jodit rich-text editor v4 wrapper (~500KB — tải lazy per-page)
 *
 * Blade:  @vite(['resources/js/modules/jodit.js'], 'build/backend')
 *
 * Quick start (class-based, nhiều field):
 *   <textarea class="jodit-editor" name="description" data-jodit-preset="compact"></textarea>
 *   <script>
 *     document.addEventListener('DOMContentLoaded', () => {
 *       initJoditAll('.jodit-editor');
 *       initFormValidation('[data-my-form]'); // global từ app.js, không cần import
 *     });
 *   </script>
 *
 * Single field (tuỳ chỉnh sâu):
 *   initJodit('#my-textarea', { height: 350 });
 *
 * ── Presets ────────────────────────────────────────────────────────────
 *   compact   — toolbar nhỏ, height 220  (field phụ: ghi chú, mô tả ngắn)
 *   standard  — toolbar vừa, height 300  (default)
 *   full      — tất cả nút,  height 400  (nội dung chính, SOP, Proposal…)
 *
 * ── Per-field override qua data attribute ─────────────────────────────
 *   data-jodit-preset="compact"   — chọn preset
 *   data-jodit-height="220"       — ghi đè height (px)
 */
import { Jodit } from 'jodit';
import 'jodit/es2021/jodit.min.css';

const BASE = {
    language: 'vi',
    theme: 'default',
    toolbar: true,
    showCharsCounter: false,
    showWordsCounter: false,
    showXPathInStatusbar: false,
    spellcheck: false,
    uploader: { insertImageAsBase64URI: true },
    removeButtons: ['about', 'classSpan'],
};

const PRESETS = {
    compact: {
        height: 220,
        toolbarButtonSize: 'small',
        buttons: [
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'ul', 'ol', '|',
            'paragraph', 'link', '|',
            'undo', 'redo', '|',
            'source',
        ],
        removeButtons: ['about', 'classSpan', 'image'],
    },

    standard: {
        height: 300,
        buttons: [
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'ul', 'ol', '|',
            'font', 'fontsize', 'paragraph', '|',
            'image', 'link', '|',
            'align', '|',
            'undo', 'redo', '|',
            'hr', 'fullsize', 'source',
        ],
        removeButtons: ['about', 'classSpan'],
    },

    // full: không khai báo buttons → Jodit dùng bộ mặc định đầy đủ
    // (image, file, video, table, align, indent, superscript, print, v.v.)
    full: {
        height: 400,
        removeButtons: ['about', 'classSpan'],
    },
};

const JoditInstances = new Map();
window.JoditInstances = JoditInstances;

function _buildOptions(el, overrides) {
    const preset = PRESETS[el.dataset.joditPreset] ?? PRESETS.standard;
    const opts   = { ...BASE, ...preset, ...overrides };
    if (el.dataset.joditHeight) opts.height = Number(el.dataset.joditHeight);
    return opts;
}

function initJodit(selector, options = {}) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) { console.warn('[Jodit] Element not found:', selector); return null; }

    const editor = Jodit.make(el, _buildOptions(el, options));
    JoditInstances.set(el.id || el.name || String(JoditInstances.size), editor);
    return editor;
}

/**
 * Khởi tạo tất cả editor theo class selector — dùng khi form có nhiều field editor.
 * Mỗi element tuỳ chỉnh riêng qua data-jodit-preset và data-jodit-height.
 *
 * @param {string} selector   CSS class selector, mặc định '.jodit-editor'
 * @param {object} shared     Options áp dụng cho tất cả (ưu tiên thấp hơn data attrs)
 */
function initJoditAll(selector = '.jodit-editor', shared = {}) {
    document.querySelectorAll(selector).forEach(el => initJodit(el, shared));
}

window.initJodit    = initJodit;
window.initJoditAll = initJoditAll;
window.Jodit        = Jodit;

export { initJodit, initJoditAll, JoditInstances };
export default Jodit;

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

window.initJodit = initJodit;
window.Jodit     = Jodit;
export { initJodit, JoditInstances };
export default Jodit;

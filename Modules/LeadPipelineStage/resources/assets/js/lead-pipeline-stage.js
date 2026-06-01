/**
 * lead-pipeline-stage.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *   2. Color picker sync — color input ↔ hex text input
 *
 * Requires globals (core bundle): initFormValidation
 */

// ── Constants ──────────────────────────────────────────────────────────────

const FORM_SEL = '[data-pipeline-stage-form]';

// ── Entry point ────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
    _syncColorPicker(form);
});

// ── Color picker sync ──────────────────────────────────────────────────────

function _syncColorPicker(form) {
    const picker = form.querySelector('#colorPicker');
    const text   = form.querySelector('#colorText');
    if (!picker || !text) return;

    // Picker drives text
    picker.addEventListener('input',  () => { text.value = picker.value; }, { passive: true });
    picker.addEventListener('change', () => { text.value = picker.value; }, { passive: true });

    // Text drives picker (only when valid hex)
    text.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value;
    }, { passive: true });
}

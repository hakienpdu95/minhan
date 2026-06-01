/**
 * survey.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *
 * Requires globals (core bundle): initFormValidation
 */

// ── Constants ──────────────────────────────────────────────────────────────

const FORM_SEL = '[data-survey-form]';

// ── Entry point ────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
});

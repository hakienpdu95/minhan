/**
 * Modules/OrgChart/resources/assets/js/pages/org-chart-form.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *   2. TomSelect — auto-init mọi select.ts-init trong form + sidebar
 *
 * Requires globals (core): initFormValidation, window.Toast
 * Requires globals (lazy): window.TomSelect (tom-select.js)
 */

import { initAllTomSelects } from '@shared/tom-select-factory.js';

const FORM_SEL = '[data-org-chart-form]';

// ── Entry point ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
    initAllTomSelects(form);
});

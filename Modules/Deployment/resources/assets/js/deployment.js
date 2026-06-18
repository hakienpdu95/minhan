/**
 * deployment.js
 *
 * Responsibilities:
 *   1. Alpine component `targetCreate` — MST lookup + org auto-fill
 *   2. initFormValidation — client-side required check
 *   3. TomSelect — auto-init all select.ts-init
 *   4. Flatpickr — auto-init all input.fp-init
 *   5. Code input — auto-uppercase (project form)
 */

import { initAllTomSelects } from '@shared/tom-select-factory.js';

// ── Constants ──────────────────────────────────────────────────────────────

const PROJECT_FORM_SEL  = '[data-project-form]';
const TARGET_FORM_SEL   = '[data-target-form]';
const PROGRESS_FORM_SEL = '[data-progress-form]';

// ── Alpine component ────────────────────────────────────────────────────────

document.addEventListener('alpine:init', () => {
    Alpine.data('targetCreate', (lookupUrl, initialTaxCode = '') => ({
        taxCode:     initialTaxCode,
        foundOrg:    null,
        useExisting: false,
        searching:   false,

        async lookup() {
            this.foundOrg    = null;
            this.useExisting = false;
            if (!this.taxCode || this.taxCode.length < 8) return;
            this.searching = true;
            try {
                const res  = await fetch(
                    lookupUrl + '?tax_code=' + encodeURIComponent(this.taxCode),
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const json = await res.json();
                this.foundOrg = json.found ? json.org : null;
            } finally {
                this.searching = false;
            }
        },

        applyOrg() {
            this.useExisting = true;
            this.$refs.orgName.value    = this.foundOrg.name         || '';
            this.$refs.orgPhone.value   = this.foundOrg.phone        || '';
            this.$refs.orgEmail.value   = this.foundOrg.email        || '';
            this.$refs.orgAddress.value = this.foundOrg.full_address || '';
        },
    }));
});

// ── Entry point ────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const projectForm = document.querySelector(PROJECT_FORM_SEL);
    if (projectForm) {
        initFormValidation(PROJECT_FORM_SEL);
        initAllTomSelects(projectForm);
        window.initAllDatePickers?.(projectForm);
        _setupCodeUppercase(projectForm);
    }

    const targetForm = document.querySelector(TARGET_FORM_SEL);
    if (targetForm) {
        initFormValidation(TARGET_FORM_SEL);
        initAllTomSelects(targetForm);
    }

    const progressForm = document.querySelector(PROGRESS_FORM_SEL);
    if (progressForm) {
        initFormValidation(PROGRESS_FORM_SEL);
        initAllTomSelects(progressForm);
        _setupTargetNav(progressForm);
    }
});

// ── Helpers ────────────────────────────────────────────────────────────────

function _setupCodeUppercase(form) {
    const el = form.querySelector('[name="code"]');
    if (!el) return;
    el.addEventListener('input', () => {
        const pos = el.selectionStart;
        el.value = el.value.toUpperCase();
        el.setSelectionRange(pos, pos);
    }, { passive: true });
}

// When target select changes, refresh the log list by updating ?target_id param.
function _setupTargetNav(form) {
    const sel = form.querySelector('[data-target-nav]');
    if (!sel) return;

    // TomSelect is initialized; hook into its change event via the original <select> change.
    sel.addEventListener('change', () => {
        if (!sel.value) return;
        const url = new URL(window.location.href);
        url.searchParams.set('target_id', sel.value);
        window.location.href = url.toString();
    });
}

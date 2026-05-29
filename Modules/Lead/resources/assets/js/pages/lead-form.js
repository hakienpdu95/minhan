/**
 * pages/lead-form.js — Alpine controller cho Lead create/edit wizard
 *
 * Dùng: @shared/form-controller + @shared/wizard-controller
 * Widgets: TomSelect (stage, source, assignee), tag checkboxes
 */

import { makeFormController }   from '@shared/form-controller.js';
import { makeWizardController } from '@shared/wizard-controller.js';
import { createTs, createTsAssignee } from '@shared/tom-select-factory.js';

// ── Validation rules ────────────────────────────────────────────────
const RULES = {
    contact_name:  v => !String(v).trim()          ? 'Họ và tên là bắt buộc' : null,
    contact_email: v => v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
                          ? 'Email không đúng định dạng' : null,
    stage_id:      v => !v                         ? 'Vui lòng chọn tình trạng' : null,
};
const REQUIRED = ['contact_name', 'stage_id'];

// ── Wizard step validators ───────────────────────────────────────────
function validateStep1() {
    const nameEl = document.getElementById('wz-contact-name');
    if (!nameEl?.value.trim()) {
        nameEl?.classList.add('input-error');
        nameEl?.focus();
        nameEl?.addEventListener('input', () => nameEl.classList.remove('input-error'), { once: true });
        return false;
    }
    return true;
}

function validateStep2() {
    return !!document.querySelector('[name="stage_id"]')?.value;
}

// ── Alpine component ─────────────────────────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.data('leadWizard', (serverData = {}) => ({

        ...makeFormController(serverData, { rules: RULES, requiredFields: REQUIRED }),

        ...makeWizardController({
            steps:       ['Khách hàng', 'Cơ hội', 'Tags & Ghi chú'],
            validators:  [validateStep1, validateStep2, null],
            onStepChange: (step) => { if (step === 3) _updateSummary(); },
            initialStep: serverData.errorStep ?? 1,
        }),

        // ── State ────────────────────────────────────────────────
        contactName: serverData.contactName ?? '',

        // ── Lifecycle ────────────────────────────────────────────
        init() {
            this.$nextTick(() => _initWidgets());
        },
    }));
});

// ── Widget init (tách ra ngoài Alpine để không làm nặng component) ───
function _initWidgets() {
    // Stage + Source TomSelect
    createTs('#wz-stage',  { placeholder: '— Chọn tình trạng —' });
    createTs('#wz-source', { placeholder: '— Chọn nguồn —' });

    // Assignee (remote search)
    const assignedEl = document.getElementById('wz-assigned');
    if (assignedEl?.dataset.assignableUrl) {
        createTsAssignee(assignedEl, assignedEl.dataset.assignableUrl);
    }

    // Tag checkboxes (màu từ data-color)
    document.querySelectorAll('.tag-item input[type="checkbox"]').forEach(cb => {
        const badge = cb.nextElementSibling;
        const color = cb.dataset.color || '#6b7280';
        const apply = () => {
            badge.style.backgroundColor = cb.checked ? color : '';
            badge.style.borderColor     = color;
            badge.style.color           = cb.checked ? '#fff' : color;
        };
        apply();
        cb.addEventListener('change', apply);
    });
}

// ── Summary box (wizard step 3) ──────────────────────────────────────
function _updateSummary() {
    const val = id => document.querySelector(`[name="${id}"]`)?.value || '—';
    const sel = id => {
        const el = document.querySelector(`[name="${id}"]`);
        return el?.options?.[el.selectedIndex]?.text || '—';
    };
    const fmt = (v, cur) => {
        const n = parseFloat(v);
        if (!v || isNaN(n)) return '—';
        return n.toLocaleString('vi-VN') + ' ' + (cur || 'VND');
    };

    const set = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    set('sum-name',    val('contact_name'));
    set('sum-phone',   val('contact_phone'));
    set('sum-email',   val('contact_email'));
    set('sum-company', val('contact_company'));
    set('sum-stage',   sel('stage_id'));
    set('sum-source',  sel('source_id'));
    set('sum-value',   fmt(val('expected_value'), val('currency')));
}

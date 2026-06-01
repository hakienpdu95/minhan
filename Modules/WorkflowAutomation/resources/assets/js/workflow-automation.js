/**
 * workflow-automation.js
 *
 * Tầng 4 — Module asset, lazy per-page.
 *
 * Covers two pages:
 *  · index.blade.php  → wfListPage  (Tabulator listing)
 *  · create/edit      → wfBuilderPage (Wizard: Cơ bản → Trigger → Điều kiện → Hành động → Cài đặt)
 *
 * Globals từ core (không cần import):
 *   window.Alpine, window.initFormValidation, window.Toast, window.Tabulator
 */

import { makeWizardController } from '@shared/wizard-controller.js';

// ── Constants ──────────────────────────────────────────────────────────────
const RE_TAB_XSHOW = /currentStep\s*===\s*(\d+)/;  // compile 1 lần

// ── Index page: DOM data island ────────────────────────────────────────────
const LIST_CFG = JSON.parse(
    document.getElementById('wf-list-data')?.textContent ?? 'null'
);

// ── HTML escape (Tabulator formatters) ────────────────────────────────────
function esc(v) {
    if (v == null) return '';
    return String(v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ── Index page: module-scoped state ───────────────────────────────────────
let wfTableInst     = null;
let pendingDeleteId = null;

// ── Index page: global helpers (Tabulator row formatters) ─────────────────
window.wfToggle = async function (id, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/dashboard/workflows/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': LIST_CFG?.csrf ?? '', Accept: 'application/json' },
        });
        if (res.ok && wfTableInst) wfTableInst.replaceData();
    } catch (e) { console.error(e); }
    finally { btn.disabled = false; }
};

window.wfDeleteConfirm = function (id, name) {
    pendingDeleteId = id;
    const nameEl = document.getElementById('wfDeleteName');
    if (nameEl) nameEl.textContent = `"${name}"`;
    document.getElementById('wfDeleteModal')?.showModal();
};

// ── Index page: delete confirm (DOM ready) ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('wfConfirmDelete');
    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', async function () {
        if (!pendingDeleteId) return;
        const btn = this;
        btn.disabled    = true;
        btn.textContent = 'Đang xóa...';
        try {
            const res = await fetch(`/dashboard/workflows/${pendingDeleteId}`, {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN':     LIST_CFG?.csrf ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept:             'application/json',
                    'Content-Type':     'application/x-www-form-urlencoded',
                },
                body: '_method=DELETE',
            });
            if (res.ok) {
                document.getElementById('wfDeleteModal')?.close();
                wfTableInst?.replaceData();
            } else {
                const d = await res.json().catch(() => ({}));
                window.Toast?.error(d.message || 'Xóa thất bại.') ?? alert(d.message || 'Xóa thất bại.');
            }
        } catch { window.Toast?.error('Lỗi kết nối.') ?? alert('Lỗi kết nối.'); }
        finally {
            btn.disabled    = false;
            btn.textContent = 'Xóa';
            pendingDeleteId = null;
        }
    });
});

// ── Index page: Tabulator columns ─────────────────────────────────────────
const WF_COLUMNS = [
    {
        title: 'Tên workflow', field: 'name', minWidth: 220, sorter: 'string',
        formatter(cell) {
            const d     = cell.getRow().getData();
            const badge = d.is_active
                ? '<span class="badge badge-xs badge-success ml-1.5">Bật</span>'
                : '<span class="badge badge-xs badge-ghost ml-1.5">Tắt</span>';
            return `<div>
                <a href="/dashboard/workflows/${d.id}" class="font-semibold text-sm hover:text-primary">${esc(d.name)}</a>
                ${badge}
                <p class="text-xs text-base-content/40 mt-0.5">${esc(d.trigger_type)}</p>
            </div>`;
        },
    },
    {
        title: 'Lần chạy', field: 'run_count', width: 110, hozAlign: 'center', sorter: 'number',
        formatter(cell) {
            return `<span class="badge badge-ghost badge-sm">${esc(cell.getValue() || 0)}</span>`;
        },
    },
    {
        title: 'Trạng thái gần nhất', field: 'last_run_badge', width: 170, hozAlign: 'center', headerSort: false,
        formatter(cell) {
            const d = cell.getRow().getData();
            if (!d.last_run_badge) return '<span class="text-base-content/25 text-xs">Chưa chạy</span>';
            return `<span class="badge badge-sm ${esc(d.last_run_badge)}">${esc(d.last_run_label)}</span>`;
        },
    },
    {
        title: 'Lần chạy gần nhất', field: 'last_run_at', width: 160, hozAlign: 'center', sorter: 'string',
        formatter(cell) {
            const v = cell.getValue();
            if (!v) return '<span class="text-base-content/25 text-xs">—</span>';
            return `<span class="text-xs">${esc(new Date(v).toLocaleString('vi-VN'))}</span>`;
        },
    },
    {
        title: 'Ưu tiên', field: 'priority', width: 90, hozAlign: 'center', sorter: 'number',
        formatter(cell) {
            return `<span class="text-sm font-mono">${esc(cell.getValue())}</span>`;
        },
    },
    {
        title: 'Thao tác', field: 'id', width: 130, hozAlign: 'center', headerSort: false,
        formatter(cell) {
            const d   = cell.getRow().getData();
            const cfg = LIST_CFG ?? {};
            let   html = '<div class="flex items-center justify-center gap-1">';
            html += `<a href="/dashboard/workflows/${d.id}" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-info" title="Xem">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>`;
            if (cfg.canEdit) {
                html += `<a href="/dashboard/workflows/${d.id}/edit" class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-warning" title="Sửa">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>`;
                const toggleIcon = d.is_active
                    ? '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
                    : '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                html += `<button class="btn btn-ghost btn-xs btn-square text-base-content/40 hover:text-base-content" title="Bật/Tắt" onclick="wfToggle(${d.id}, this)">${toggleIcon}</button>`;
            }
            if (cfg.canDelete) {
                const safeName = esc(d.name).replace(/'/g, "\\'");
                html += `<button class="btn btn-ghost btn-xs btn-square text-error/30 hover:text-error" title="Xóa" onclick="wfDeleteConfirm(${d.id}, '${safeName}')">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>`;
            }
            html += '</div>';
            return html;
        },
    },
];

// ── Alpine components ──────────────────────────────────────────────────────
document.addEventListener('alpine:init', () => {

    // ── wfListPage: index listing ─────────────────────────────────────────
    Alpine.data('wfListPage', () => ({
        filters: { search: '', is_active: '' },

        get hasFilters() { return !!(this.filters.search || this.filters.is_active !== ''); },

        init() { this.$nextTick(() => this._setup()); },

        _setup() {
            const self = this;
            wfTableInst = new window.Tabulator('#wf-table', {
                ajaxURL:    LIST_CFG?.apiUrl ?? '',
                ajaxConfig: { headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                ajaxParams() {
                    const p = {}, f = self.filters;
                    if (f.search)           p.search    = f.search;
                    if (f.is_active !== '') p.is_active = f.is_active;
                    return p;
                },
                ajaxResponse(_u, _p, res) { return res; },

                pagination:             true,
                paginationMode:         'remote',
                paginationSize:         20,
                paginationSizeSelector: [10, 20, 50],
                paginationCounter:      'rows',
                sortMode:               'remote',
                initialSort:            [{ column: 'created_at', dir: 'desc' }],
                layout:                 'fitColumns',
                height:                 '65vh',
                locale:                 'vi-VN',
                langs: { 'vi-VN': { pagination: {
                    page_size: 'Dòng/trang', first: '«', last: '»', prev: '‹', next: '›',
                    counter: { showing: '', of: 'trong', rows: 'dòng', pages: 'trang' },
                }}},
                columns: WF_COLUMNS,
                placeholder: '<div class="py-16 text-center opacity-40"><p class="text-sm">Chưa có workflow nào</p></div>',
            });
        },

        refresh()      { wfTableInst?.replaceData(); },
        resetFilters() { this.filters = { search: '', is_active: '' }; this.refresh(); },
    }));

    // ── wfBuilderPage: Wizard create/edit form ────────────────────────────
    Alpine.data('wfBuilderPage', (sd) => {
        const metaUrl  = sd.metaUrl   ?? '';
        const initData = sd.initData  ?? {};
        const initStep = sd.initialStep ?? 1;

        return {
            // ── Wizard controller (makeWizardController mixin) ────────────
            ...makeWizardController({
                steps: ['Cơ bản', 'Trigger', 'Điều kiện', 'Hành động', 'Cài đặt'],
                validators: [
                    function () { return this._validateStep1(); },
                    function () { return this._validateStep2(); },
                    null, null, null,
                ],
                initialStep: initStep,
            }),

            // ── Form state ────────────────────────────────────────────────
            meta:        { trigger_groups: {}, action_groups: {}, operators: [], cooldown_types: [], subjects: {} },
            metaLoading: true,
            metaError:   false,
            loading:     false,
            triggerConfig: {},
            form: Object.assign({
                name: '', description: '', trigger_type: '', trigger_params: [],
                condition_match: 3, cooldown_type: 0, is_active: false, priority: 5,
                conditions: [], steps: [],
            }, initData),

            // ── Computed ──────────────────────────────────────────────────
            get currentTrigger() {
                if (!this.form.trigger_type) return null;
                for (const [, triggers] of Object.entries(this.meta.trigger_groups ?? {})) {
                    const found = triggers.find(t => t.type === this.form.trigger_type);
                    if (found) return found;
                }
                return null;
            },

            // ── Init ──────────────────────────────────────────────────────
            init() {
                if (!this.form.steps)      this.form.steps      = [];
                if (!this.form.conditions) this.form.conditions = [];

                for (const p of this.form.trigger_params ?? []) {
                    this.triggerConfig[p.param_key] = p.param_value;
                }
                for (const s of this.form.steps) {
                    if (!s.headers) s.headers = [];
                }

                this._fetchMeta();
            },

            // ── Meta fetch ────────────────────────────────────────────────
            async _fetchMeta() {
                this.metaLoading = true;
                try {
                    const r = await fetch(metaUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    this.meta        = await r.json();
                    this.metaLoading = false;
                } catch {
                    this.metaError   = true;
                    this.metaLoading = false;
                }
            },

            // ── Step validators ───────────────────────────────────────────
            _validateStep1() {
                if (!this.form.name.trim()) {
                    window.Toast?.warning('Vui lòng nhập tên workflow trước khi tiếp tục', { duration: 3000 });
                    this.$nextTick(() => document.querySelector('[name="name"]')?.focus());
                    return false;
                }
                return true;
            },

            _validateStep2() {
                if (!this.form.trigger_type) {
                    window.Toast?.warning('Vui lòng chọn loại trigger trước khi tiếp tục', { duration: 3000 });
                    this.$nextTick(() => document.querySelector('[name="trigger_type"]')?.focus());
                    return false;
                }
                return true;
            },

            // ── Trigger handling ──────────────────────────────────────────
            onTriggerChange() {
                this.triggerConfig       = {};
                this.form.trigger_params = [];
                this.form.conditions     = [];
            },

            syncTriggerParams() {
                const params = [];
                const fields = this.currentTrigger?.config_fields ?? [];
                for (const f of fields) {
                    const val = this.triggerConfig[f.key];
                    if (val !== undefined && val !== '') {
                        params.push({ param_key: f.key, param_value: String(val), param_type: f.type === 'number' ? 2 : 1 });
                    }
                }
                this.form.trigger_params = params;
            },

            // ── Conditions ────────────────────────────────────────────────
            addCondition()     { this.form.conditions.push({ field: '', operator: '=', value: '', value_type: 1 }); },
            removeCondition(i) { this.form.conditions.splice(i, 1); },

            // ── Steps ─────────────────────────────────────────────────────
            addStep() {
                this.form.steps.push({
                    action_type: '', delay_minutes: 0, headers: [],
                    email_to: '', email_subject: '', email_template: '',
                    notif_title: '', notif_body: '', notif_target: '',
                    webhook_url: '', webhook_method: 2, webhook_secret: '',
                    update_model: '', update_field: '', update_value: '',
                    lead_source: '', lead_status: '',
                });
            },
            removeStep(i) { this.form.steps.splice(i, 1); },

            resetStepConfig(step) {
                const keys = ['email_to','email_subject','email_template','notif_title','notif_body',
                              'notif_target','webhook_url','webhook_secret','update_model','update_field',
                              'update_value','lead_source','lead_status'];
                for (const k of keys) step[k] = '';
                step.headers        = [];
                step.webhook_method = 2;
            },

            addHeader(step) { step.headers.push({ header_key: '', header_value: '' }); },

            stepConfigFields(actionType) {
                for (const [, actions] of Object.entries(this.meta.action_groups ?? {})) {
                    const action = actions.find(a => a.type === actionType);
                    if (action?.config_fields) return action.config_fields;
                }
                return [];
            },

            // ── Form submit ───────────────────────────────────────────────
            submitForm() {
                this.syncTriggerParams();
                this.loading = true;
                this.$nextTick(() => document.getElementById('wf-form')?.submit());
            },
        };
    });

});

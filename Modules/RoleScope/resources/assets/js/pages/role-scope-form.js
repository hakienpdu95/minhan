/**
 * Modules/RoleScope/resources/assets/js/pages/role-scope-form.js
 *
 * Responsibilities:
 *   1. Inline validation — delegate to global initFormValidation
 *   2. Flatpickr — datetime picker cho trường expires_at
 *   3. Alpine component grantRoleScopeForm — TomSelect manual init + cascade branch → dept
 *
 * Requires globals (core): initFormValidation, window.Alpine
 * Requires globals (lazy): window.TomSelect (tom-select.js), initDateTimePicker (flatpickr.js)
 */

import { createTs } from '@shared/tom-select-factory.js';

const FORM_SEL = '[data-role-scope-form]';

// ── Entry point ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(FORM_SEL);
    if (!form) return;

    initFormValidation(FORM_SEL);
    _initFlatpickr(form);
});

// ── Flatpickr datetime picker ──────────────────────────────────────────────────

function _initFlatpickr(form) {
    if (typeof initDateTimePicker !== 'function') return;
    const opts = { altInput: true, altFormat: 'd/m/Y H:i', dateFormat: 'Y-m-d H:i' };
    const el = form.querySelector('#fp-expires-at');
    if (el) initDateTimePicker(el, opts);
}

// ── Alpine component: Grant Role Scope (create form) ──────────────────────────

document.addEventListener('alpine:init', () => {

    Alpine.data('grantRoleScopeForm', (cfg) => {
        const ALL_DEPTS = cfg.departments;

        // Module-level TomSelect instances — shared across methods
        let _userTs   = null;
        let _roleTs   = null;
        let _branchTs = null;
        let _deptTs   = null;

        const NO_RESULTS = () => '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>';

        return {
            selectedBranchId: cfg.oldBranchId ? parseInt(cfg.oldBranchId) : null,

            init() {
                this.$nextTick(() => this._initSelects());
            },

            _initSelects() {
                _userTs = createTs(document.querySelector('#select-user'), {
                    dropdownParent: 'body',
                    placeholder:    'Chọn user...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    options:        cfg.users,
                    items:          cfg.oldUserId ? [String(cfg.oldUserId)] : [],
                    render: { no_results: NO_RESULTS },
                });

                _roleTs = createTs(document.querySelector('#select-role'), {
                    dropdownParent: 'body',
                    placeholder:    'Chọn role...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    options:        cfg.roles,
                    items:          cfg.oldRoleId ? [String(cfg.oldRoleId)] : [],
                    render: { no_results: NO_RESULTS },
                });

                const self = this;

                _branchTs = createTs(document.querySelector('#select-branch'), {
                    dropdownParent: 'body',
                    placeholder:    '— Toàn tổ chức (không giới hạn chi nhánh) —',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    options:        cfg.branches,
                    items:          self.selectedBranchId ? [String(self.selectedBranchId)] : [],
                    onChange(val) {
                        self.selectedBranchId = val ? parseInt(val) : null;
                        self._refreshDepts();
                        if (_deptTs) _deptTs.clear(true);
                    },
                    render: { no_results: NO_RESULTS },
                });

                _deptTs = createTs(document.querySelector('#select-dept'), {
                    dropdownParent: 'body',
                    placeholder:    '— Toàn chi nhánh (không giới hạn phòng ban) —',
                    maxOptions:     null,
                    searchField:    ['text'],
                    plugins:        ['clear_button'],
                    options:        [],
                    items:          cfg.oldDeptId ? [String(cfg.oldDeptId)] : [],
                    render: { no_results: NO_RESULTS },
                });

                if (self.selectedBranchId) self._refreshDepts(cfg.oldDeptId);
            },

            _refreshDepts(pendingDept) {
                if (!_deptTs) return;
                _deptTs.clearOptions();
                if (!this.selectedBranchId) return;

                const filtered = ALL_DEPTS.filter(d =>
                    d.branch_id === null || d.branch_id === this.selectedBranchId
                );
                for (const d of filtered) {
                    _deptTs.addOption({ value: d.value, text: d.text });
                }
                _deptTs.refreshOptions(false);
                if (pendingDept) _deptTs.setValue(String(pendingDept), true);
            },
        };
    });

});

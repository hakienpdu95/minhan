@extends('layouts.backend')
@section('title', 'Cấp quyền phạm vi mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.role-scopes.index') }}">Phân quyền phạm vi</a>
    <span class="sep">›</span>
    <span class="current">Cấp quyền mới</span>
</nav>
@endsection

@section('content')
<div x-data="grantRoleScopeForm({{ Js::from([
    'users'       => $users->map(fn($u) => ['value' => $u->id, 'text' => $u->name . ' (' . $u->email . ')'])->values()->all(),
    'roles'       => $roles->map(fn($r) => ['value' => $r->id, 'text' => $r->name])->values()->all(),
    'branches'    => $branches->map(fn($b) => ['value' => $b->id, 'text' => $b->name . ' [' . $b->code . ']'])->values()->all(),
    'departments' => $departments->map(fn($d) => ['value' => $d->id, 'text' => $d->name . ' [' . $d->code . ']', 'branch_id' => $d->branch_id])->values()->all(),
]) }})">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Cấp quyền phạm vi mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Gán role cho user với phạm vi chi nhánh hoặc phòng ban cụ thể</p>
    </div>
    <a href="{{ route('backend.role-scopes.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-semibold">Có {{ $errors->count() }} lỗi cần kiểm tra:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('backend.role-scopes.store') }}" novalidate>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

        {{-- ── Card chính ───────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-5">

                {{-- User --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">User <span class="text-error">*</span></span>
                        <span class="label-text-alt text-xs opacity-50">User trong tổ chức</span>
                    </label>
                    <select id="select-user" name="user_id" class="@error('user_id') border-error @enderror"></select>
                    @error('user_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Role --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                        <span class="label-text-alt text-xs opacity-50">Role Spatie RBAC</span>
                    </label>
                    <select id="select-role" name="role_id" class="@error('role_id') border-error @enderror"></select>
                    @error('role_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Scope section --}}
                <div class="divider text-xs text-base-content/40 my-0">Phạm vi áp dụng</div>

                <div class="alert alert-info py-2.5 px-4 text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Logic phạm vi:</p>
                        <ul class="list-disc list-inside text-xs mt-1 space-y-0.5 opacity-80">
                            <li>Không chọn gì → Toàn tổ chức</li>
                            <li>Chọn chi nhánh, không chọn phòng ban → Toàn chi nhánh đó</li>
                            <li>Chọn cả chi nhánh + phòng ban → Chỉ phòng ban đó trong chi nhánh</li>
                        </ul>
                    </div>
                </div>

                {{-- Scope branch --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Chi nhánh áp dụng</span>
                        <span class="label-text-alt text-xs opacity-50">Để trống = toàn tổ chức</span>
                    </label>
                    <select id="select-branch" name="scope_branch_id" class="@error('scope_branch_id') border-error @enderror"></select>
                    @error('scope_branch_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Scope dept --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Phòng ban áp dụng</span>
                        <span class="label-text-alt text-xs opacity-50">Cần chọn chi nhánh trước</span>
                    </label>
                    <select id="select-dept" name="scope_dept_id"
                            :disabled="!selectedBranchId"
                            class="@error('scope_dept_id') border-error @enderror"></select>
                    @error('scope_dept_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    <p x-show="!selectedBranchId" class="text-xs text-base-content/40 mt-1">
                        Chọn chi nhánh trước để lọc phòng ban
                    </p>
                </div>

            </div>
        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <h3 class="font-semibold text-sm">Thời hạn & Ghi chú</h3>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-sm">Hết hạn vào</span>
                            <span class="label-text-alt text-xs opacity-50">Để trống = vĩnh viễn</span>
                        </label>
                        <input type="datetime-local" name="expires_at"
                               value="{{ old('expires_at') }}"
                               class="input input-sm input-bordered @error('expires_at') input-error @enderror"/>
                        @error('expires_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-sm">Lý do cấp</span>
                        </label>
                        <textarea name="note" rows="3"
                                  class="textarea textarea-sm textarea-bordered @error('note') textarea-error @enderror"
                                  placeholder="Ghi chú lý do cấp quyền này...">{{ old('note') }}</textarea>
                        @error('note')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Cấp quyền
                        </button>
                        <a href="{{ route('backend.role-scopes.index') }}" class="btn btn-ghost w-full mt-2">Hủy</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>
</div>
@endsection

@push('styles')
<style>
.ts-wrapper.single .ts-control { background:oklch(var(--b1)); border-color:oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; min-height:2.25rem; padding:.3rem .5rem; font-size:.875rem; }
.ts-wrapper.single.focus .ts-control { border-color:oklch(var(--p)); outline:none; box-shadow:0 0 0 2px oklch(var(--p)/.2); }
.ts-dropdown { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); border-radius:.5rem; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:9999; }
.ts-dropdown .ts-option { color:oklch(var(--bc)); padding:.4rem .75rem; font-size:.875rem; }
.ts-dropdown .ts-option:hover, .ts-dropdown .ts-option.active { background:oklch(var(--b2)); }
.ts-control input { color:oklch(var(--bc)) !important; }
.ts-wrapper .clear-button { color:oklch(var(--bc)/.4); }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('grantRoleScopeForm', function (cfg) {
        var ALL_DEPTS = cfg.departments;
        return {
            selectedBranchId: {{ old('scope_branch_id') ? old('scope_branch_id') : 'null' }},
            userTs: null, roleTs: null, branchTs: null, deptTs: null,

            init: function () {
                var self = this;
                document.addEventListener('DOMContentLoaded', function () {
                    self._initSelects(cfg);
                }, { once: true });
            },

            _initSelects: function (cfg) {
                var self = this;

                self.userTs = new window.TomSelect('#select-user', {
                    dropdownParent: 'body', placeholder: 'Chọn user...', maxOptions: null,
                    searchField: ['text'], plugins: ['clear_button'],
                    options: cfg.users,
                    items: {{ old('user_id') ? '[' . old('user_id') . ']' : '[]' }},
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                self.roleTs = new window.TomSelect('#select-role', {
                    dropdownParent: 'body', placeholder: 'Chọn role...', maxOptions: null,
                    searchField: ['text'], plugins: ['clear_button'],
                    options: cfg.roles,
                    items: {{ old('role_id') ? '[' . old('role_id') . ']' : '[]' }},
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                self.branchTs = new window.TomSelect('#select-branch', {
                    dropdownParent: 'body', placeholder: 'Để trống = toàn tổ chức...', maxOptions: null,
                    searchField: ['text'], plugins: ['clear_button'],
                    options: cfg.branches,
                    items: self.selectedBranchId ? [String(self.selectedBranchId)] : [],
                    onChange: function (val) {
                        self.selectedBranchId = val ? parseInt(val) : null;
                        self._refreshDepts();
                        if (self.deptTs) self.deptTs.clear(true);
                    },
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                self.deptTs = new window.TomSelect('#select-dept', {
                    dropdownParent: 'body', placeholder: 'Để trống = toàn chi nhánh...', maxOptions: null,
                    searchField: ['text'], plugins: ['clear_button'],
                    options: [],
                    items: {{ old('scope_dept_id') ? '[' . old('scope_dept_id') . ']' : '[]' }},
                    render: { no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                if (self.selectedBranchId) self._refreshDepts({{ old('scope_dept_id') }});
            },

            _refreshDepts: function (pendingDept) {
                var self = this;
                if (!self.deptTs) return;
                self.deptTs.clearOptions();
                if (!self.selectedBranchId) {
                    self.deptTs.enable && self.deptTs.enable();
                    return;
                }
                var filtered = ALL_DEPTS.filter(function (d) {
                    return d.branch_id === null || d.branch_id === self.selectedBranchId;
                });
                filtered.forEach(function (d) { self.deptTs.addOption({ value: d.value, text: d.text }); });
                self.deptTs.refreshOptions(false);
                if (pendingDept) self.deptTs.setValue(String(pendingDept), true);
            },
        };
    });
});
</script>
@endpush

@extends('layouts.backend')
@section('title', 'Sửa tài khoản')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.users.index') }}">Tài khoản</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<div x-data="editUserPage">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Sửa tài khoản</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ $user->email }}</p>
    </div>
    <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.users.update', $user) }}" id="edit-user-form">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

        {{-- ── Left column: Basic info + Password ────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Basic info --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Thông tin tài khoản
                    </h2>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="input input-bordered input-sm @error('name') input-error @enderror"
                                   placeholder="Nguyễn Văn A" required>
                            @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="input input-bordered input-sm @error('email') input-error @enderror"
                                   placeholder="email@company.com" required>
                            @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Phòng ban</span>
                            </label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}"
                                   class="input input-bordered input-sm @error('department') input-error @enderror"
                                   placeholder="VD: Kinh doanh, IT, HR...">
                            @error('department')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3 py-0">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       class="checkbox checkbox-sm checkbox-primary"
                                       {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <div>
                                    <span class="label-text font-medium">Tài khoản đang hoạt động</span>
                                    <p class="text-xs text-base-content/50 mt-0.5">Bỏ chọn để vô hiệu hoá tạm thời</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Đổi mật khẩu
                    </h2>

                    <div class="alert alert-info py-2 px-3 mb-4">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm">Để trống nếu không muốn đổi mật khẩu.</span>
                        <button type="button" @click="generatePassword()"
                                class="btn btn-xs btn-info ml-auto shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Tạo ngẫu nhiên
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Mật khẩu mới</span>
                                <span class="label-text-alt text-base-content/40">Để trống = không đổi</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password" id="password-input"
                                       x-model="password"
                                       class="input input-bordered input-sm w-full pr-10 @error('password') input-error @enderror"
                                       placeholder="Tối thiểu 8 ký tự">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror

                            {{-- Strength indicator --}}
                            <div x-show="password.length > 0" class="mt-2 space-y-1">
                                <progress class="progress w-full h-1.5"
                                          :class="strength.cls"
                                          :value="strength.pct" max="100"></progress>
                                <div class="flex items-center justify-between">
                                    <div class="flex gap-2">
                                        <span class="text-xs" :class="pwChecks.length ? 'text-success' : 'text-base-content/30'">
                                            <span x-text="pwChecks.length ? '✓' : '○'"></span> ≥8 ký tự
                                        </span>
                                        <span class="text-xs" :class="pwChecks.upper ? 'text-success' : 'text-base-content/30'">
                                            <span x-text="pwChecks.upper ? '✓' : '○'"></span> HOA
                                        </span>
                                        <span class="text-xs" :class="pwChecks.number ? 'text-success' : 'text-base-content/30'">
                                            <span x-text="pwChecks.number ? '✓' : '○'"></span> Số
                                        </span>
                                        <span class="text-xs" :class="pwChecks.special ? 'text-success' : 'text-base-content/30'">
                                            <span x-text="pwChecks.special ? '✓' : '○'"></span> Đặc biệt
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium" :class="strength.cls.replace('progress-','text-')" x-text="strength.label"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Xác nhận mật khẩu</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password_confirmation" id="password-confirm-input"
                                       x-model="passwordConfirm"
                                       class="input input-bordered input-sm w-full pr-10"
                                       placeholder="Nhập lại mật khẩu mới">
                                <span x-show="passwordConfirm.length > 0 && password.length > 0"
                                      class="absolute right-2 top-1/2 -translate-y-1/2 text-xs"
                                      :class="passwordConfirm === password ? 'text-success' : 'text-error'">
                                    <span x-text="passwordConfirm === password ? '✓' : '✗'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Right column: Org & Role + Permission matrix ───────────────── --}}
        <div class="xl:col-span-3 space-y-5">

            {{-- Org & Role --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-base mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Tổ chức & Vai trò
                    </h2>

                    <div class="form-control mb-4">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tổ chức <span class="text-error">*</span></span>
                        </label>
                        <select id="org-select" name="organization_id"
                                class="select select-bordered select-sm @error('organization_id') select-error @enderror"></select>
                        @error('organization_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Role presets --}}
                    <div class="mb-3">
                        <p class="text-xs font-medium text-base-content/50 uppercase mb-2 tracking-wide">Chọn nhanh vai trò</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                            <template x-for="preset in rolePresets" :key="preset.role">
                                <button type="button"
                                        @click="selectPreset(preset.role)"
                                        :class="selectedRole === preset.role
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'border-base-300 hover:border-primary/50 hover:bg-base-200'"
                                        class="flex items-center gap-1.5 p-2 rounded-lg border text-xs font-medium transition-all text-left">
                                    <span x-text="preset.icon" class="text-base leading-none"></span>
                                    <span x-text="preset.label" class="leading-tight"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Vai trò hệ thống <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs text-base-content/40">Quyết định quyền truy cập</span>
                        </label>
                        <select id="role-select" name="system_role"
                                class="select select-bordered select-sm @error('system_role') select-error @enderror"></select>
                        @error('system_role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Permission matrix --}}
            <div class="card bg-base-100 shadow-sm border border-base-200" x-show="selectedRole" x-transition>
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Quyền hạn theo vai trò
                        </h3>
                        <span class="badge badge-primary badge-sm" x-text="selectedRoleLabel"></span>
                    </div>

                    <div class="mb-3 p-2.5 bg-base-200/60 rounded-lg" x-show="sidebarModules.length > 0">
                        <p class="text-xs text-base-content/50 font-medium mb-1.5">Hiển thị trong sidebar:</p>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="mod in sidebarModules" :key="mod">
                                <span class="badge badge-xs badge-ghost border border-base-300 font-normal" x-text="mod"></span>
                            </template>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="text-xs uppercase text-base-content/40">
                                    <th>Module</th>
                                    <th>Mức quyền</th>
                                    <th class="hidden sm:table-cell">Mô tả ngắn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in currentMatrix" :key="row.module">
                                    <tr>
                                        <td class="font-medium text-sm py-1.5" x-text="row.module"></td>
                                        <td class="py-1.5">
                                            <span :class="row.badgeClass" x-text="row.level"></span>
                                        </td>
                                        <td class="text-xs text-base-content/50 hidden sm:table-cell py-1.5" x-text="row.desc"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Empty state --}}
            <div class="card bg-base-100 shadow-sm border border-base-200 border-dashed" x-show="!selectedRole">
                <div class="card-body py-10 text-center text-base-content/25">
                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <p class="font-medium text-sm">Chọn vai trò để xem quyền hạn</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Submit --}}
    <div class="flex gap-3 pt-4 mt-2 border-t border-base-200">
        <button type="submit" class="btn btn-primary btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Lưu thay đổi
        </button>
        <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>

</form>
</div>
@endsection

@push('styles')
<style>
.ts-wrapper.single .ts-control { background:oklch(var(--b1)); border-color:oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; min-height:2.25rem; padding:.3rem .6rem; font-size:.875rem; }
.ts-wrapper.single.focus .ts-control { border-color:oklch(var(--p)); outline:none; box-shadow:0 0 0 2px oklch(var(--p)/.2); }
.ts-dropdown { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); border-radius:.5rem; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:9999; }
.ts-dropdown .ts-option { color:oklch(var(--bc)); padding:.45rem .75rem; font-size:.875rem; }
.ts-dropdown .ts-option:hover, .ts-dropdown .ts-option.active { background:oklch(var(--b2)); }
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-control input { color:oklch(var(--bc)) !important; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('alpine:init', function () {
    var ORGANIZATIONS = @json($organizations->values());
    var ROLES         = @json($roles);
    var OLD_ORG       = '{{ old('organization_id', $user->organization_id) }}';
    var OLD_ROLE      = '{{ old('system_role', $currentRole) }}';

    var LEVEL_META = {
        'Full':          { cls: 'badge badge-xs badge-success',   desc: 'Toàn quyền CRUD + gán' },
        'Assigned':      { cls: 'badge badge-xs badge-info',      desc: 'Chỉ record được gán' },
        'Full team':     { cls: 'badge badge-xs badge-success',   desc: 'Toàn quyền cả team' },
        'Limited':       { cls: 'badge badge-xs badge-warning',   desc: 'Xem, không sửa/xóa' },
        'Source view':   { cls: 'badge badge-xs badge-primary',   desc: 'Xem nguồn, ẩn thông tin cá nhân' },
        'Config':        { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình module' },
        'Config prompt': { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Chỉnh prompt AI' },
        'Admin config':  { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình admin' },
        'Full config':   { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Toàn quyền cấu hình' },
        'AI config':     { cls: 'badge badge-xs badge-ghost border border-base-300', desc: 'Cấu hình AI' },
        'Use':           { cls: 'badge badge-xs badge-accent',    desc: 'Dùng AI output, không config' },
        'Monitor':       { cls: 'badge badge-xs badge-warning',   desc: 'Xem trạng thái, không sửa' },
        'Monitor/Edit':  { cls: 'badge badge-xs badge-warning',   desc: 'Xem + sửa workflow' },
        'Approve/View':  { cls: 'badge badge-xs badge-secondary', desc: 'Xem + phê duyệt' },
        'View':          { cls: 'badge badge-xs badge-ghost',     desc: 'Chỉ xem' },
        'View related':  { cls: 'badge badge-xs badge-ghost',     desc: 'Xem tài liệu liên quan' },
        'View summary':  { cls: 'badge badge-xs badge-ghost',     desc: 'Xem tóm tắt' },
        'View ltd':      { cls: 'badge badge-xs badge-ghost',     desc: 'Xem giới hạn' },
        'HR tasks':      { cls: 'badge badge-xs badge-info',      desc: 'Chỉ task bộ phận HR' },
        'Create/Edit':   { cls: 'badge badge-xs badge-success',   desc: 'Tạo và sửa' },
        'Create HR SOP': { cls: 'badge badge-xs badge-info',      desc: 'Tạo SOP HR' },
        'Personal/team': { cls: 'badge badge-xs badge-info',      desc: 'Báo cáo cá nhân/team' },
        'Operations':    { cls: 'badge badge-xs badge-info',      desc: 'Báo cáo vận hành' },
        'Marketing':     { cls: 'badge badge-xs badge-info',      desc: 'Báo cáo marketing' },
        'HR':            { cls: 'badge badge-xs badge-info',      desc: 'Báo cáo nhân sự' },
        'AI usage':      { cls: 'badge badge-xs badge-info',      desc: 'Báo cáo dùng AI' },
        'Shared only':   { cls: 'badge badge-xs badge-ghost',     desc: 'Chỉ dữ liệu được chia sẻ' },
    };

    var MATRIX = @json($matrix);

    var SIDEBAR = {
        'ceo':          ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Reports'],
        'sales':        ['CRM','Sales AI','Tasks','SOP','Reports'],
        'ops':          ['CEO Dashboard','CRM','Tasks','SOP','Workflow','AI Logs','Reports'],
        'marketing':    ['CRM','Sales AI','Tasks','SOP','Reports'],
        'hr':           ['Tasks','SOP','Users','Reports'],
        'ai_operator':  ['CEO Dashboard','CRM','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Reports'],
        'system_admin': ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Roles/Perms','Reports'],
        'viewer':       ['CEO Dashboard','Tasks','SOP','Reports'],
    };

    var ROLE_PRESETS = [
        { role: 'ceo',          label: 'CEO / Điều hành', icon: '👔' },
        { role: 'sales',        label: 'Kinh doanh',       icon: '💼' },
        { role: 'ops',          label: 'Vận hành',         icon: '⚙️' },
        { role: 'marketing',    label: 'Marketing',        icon: '📢' },
        { role: 'hr',           label: 'Nhân sự (HR)',     icon: '👥' },
        { role: 'ai_operator',  label: 'AI Operator',      icon: '🤖' },
        { role: 'system_admin', label: 'Quản trị HT',      icon: '🛡️' },
        { role: 'viewer',       label: 'Xem giới hạn',     icon: '👁️' },
    ];

    var allowedRoleValues = ROLES.map(function (r) { return r.value; });
    var filteredPresets   = ROLE_PRESETS.filter(function (p) { return allowedRoleValues.includes(p.role); });

    function genPassword() {
        var upper   = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        var lower   = 'abcdefghjkmnpqrstuvwxyz';
        var digits  = '23456789';
        var special = '!@#$%&*';
        var all     = upper + lower + digits + special;
        var pwd     = upper[Math.floor(Math.random() * upper.length)]
                    + lower[Math.floor(Math.random() * lower.length)]
                    + digits[Math.floor(Math.random() * digits.length)]
                    + special[Math.floor(Math.random() * special.length)];
        for (var i = pwd.length; i < 12; i++) pwd += all[Math.floor(Math.random() * all.length)];
        return pwd.split('').sort(function () { return 0.5 - Math.random(); }).join('');
    }

    var roleTsInst = null;

    Alpine.data('editUserPage', function () {
        return {
            // ── Password ──────────────────────────────────────────────────
            password:        '',
            passwordConfirm: '',
            showPassword:    false,

            // ── Role ──────────────────────────────────────────────────────
            selectedRole:      OLD_ROLE,
            selectedRoleLabel: '',
            rolePresets:       filteredPresets,

            // ── Password computed ─────────────────────────────────────────
            get pwChecks() {
                var p = this.password;
                return {
                    length:  p.length >= 8,
                    upper:   /[A-Z]/.test(p),
                    lower:   /[a-z]/.test(p),
                    number:  /[0-9]/.test(p),
                    special: /[^A-Za-z0-9]/.test(p),
                };
            },

            get strength() {
                var c     = this.pwChecks;
                var score = [c.length, c.upper && c.lower, c.number, c.special, this.password.length >= 12]
                    .filter(Boolean).length;
                var levels = [
                    { label: '',           cls: 'progress-base-300', pct: 0 },
                    { label: 'Yếu',        cls: 'progress-error',    pct: 20 },
                    { label: 'Trung bình', cls: 'progress-warning',  pct: 40 },
                    { label: 'Tốt',        cls: 'progress-info',     pct: 65 },
                    { label: 'Mạnh',       cls: 'progress-success',  pct: 85 },
                    { label: 'Rất mạnh',   cls: 'progress-success',  pct: 100 },
                ];
                return levels[Math.min(score, 5)];
            },

            // ── Permission matrix computed ────────────────────────────────
            get currentMatrix() {
                var role = this.selectedRole;
                if (!role) return [];
                return Object.keys(MATRIX)
                    .filter(function (m) { return MATRIX[m][role]; })
                    .map(function (m) {
                        var level = MATRIX[m][role];
                        var meta  = LEVEL_META[level] || { cls: 'badge badge-xs badge-ghost', desc: '' };
                        return { module: m, level: level, badgeClass: meta.cls, desc: meta.desc };
                    });
            },

            get sidebarModules() { return SIDEBAR[this.selectedRole] || []; },

            // ── Actions ───────────────────────────────────────────────────
            generatePassword: function () {
                var pwd = genPassword();
                this.password        = pwd;
                this.passwordConfirm = pwd;
                this.showPassword    = true;
                var el = document.getElementById('password-input');
                if (el) el.value = pwd;
                var el2 = document.getElementById('password-confirm-input');
                if (el2) el2.value = pwd;
            },

            selectPreset: function (role) {
                this.selectedRole = role;
                var found = ROLES.find(function (r) { return r.value === role; });
                this.selectedRoleLabel = found ? found.label : '';
                if (roleTsInst) roleTsInst.setValue(role, false);
            },

            // ── Lifecycle ─────────────────────────────────────────────────
            init: function () {
                var self = this;
                if (OLD_ROLE) {
                    var found = ROLES.find(function (r) { return r.value === OLD_ROLE; });
                    if (found) self.selectedRoleLabel = found.label;
                }
                document.addEventListener('DOMContentLoaded', function () { self._setup(); }, { once: true });
            },

            _setup: function () {
                var self = this;

                new window.TomSelect('#org-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn tổ chức...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    options:        ORGANIZATIONS.map(function (o) { return { value: String(o.id), text: o.name }; }),
                    items:          OLD_ORG ? [String(OLD_ORG)] : [],
                    render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                });

                roleTsInst = new window.TomSelect('#role-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn vai trò...',
                    searchField:    ['text'],
                    options:        ROLES.map(function (r) { return { value: r.value, text: r.label }; }),
                    items:          OLD_ROLE ? [OLD_ROLE] : [],
                    render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
                    onChange: function (val) {
                        self.selectedRole = val || '';
                        var found = ROLES.find(function (r) { return r.value === val; });
                        self.selectedRoleLabel = found ? found.label : '';
                    },
                });
            },
        };
    });
});
</script>
@endpush

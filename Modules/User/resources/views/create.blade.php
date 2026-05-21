@extends('layouts.backend')
@section('title', 'Thêm tài khoản')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.users.index') }}">Tài khoản</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div x-data="createUserPage">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Thêm tài khoản mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tạo tài khoản và phân quyền theo ma trận vai trò</p>
    </div>
    <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.users.store') }}" class="space-y-5">
    @csrf

    {{-- ── Thông tin tài khoản ─────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Thông tin tài khoản
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="input input-bordered input-sm @error('name') input-error @enderror"
                           placeholder="Nguyễn Văn A" required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="input input-bordered input-sm @error('email') input-error @enderror"
                           placeholder="email@company.com" required>
                    @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Phòng ban</span>
                    </label>
                    <input type="text" name="department" value="{{ old('department') }}"
                           class="input input-bordered input-sm @error('department') input-error @enderror"
                           placeholder="VD: Kinh doanh, IT, HR...">
                    @error('department')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mật khẩu <span class="text-error">*</span></span>
                    </label>
                    <input type="password" name="password"
                           class="input input-bordered input-sm @error('password') input-error @enderror"
                           placeholder="Tối thiểu 8 ký tự" required>
                    @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Xác nhận mật khẩu <span class="text-error">*</span></span>
                    </label>
                    <input type="password" name="password_confirmation"
                           class="input input-bordered input-sm"
                           placeholder="Nhập lại mật khẩu" required>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tổ chức & Vai trò ───────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Tổ chức & Vai trò
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Organization --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tổ chức <span class="text-error">*</span></span>
                    </label>
                    <select id="org-select" name="organization_id"
                            class="select select-bordered select-sm @error('organization_id') select-error @enderror"></select>
                    @error('organization_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- System role --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Vai trò hệ thống <span class="text-error">*</span></span>
                        <span class="label-text-alt text-xs text-base-content/40">Quyết định quyền truy cập</span>
                    </label>
                    <select id="role-select" name="system_role"
                            class="select select-bordered select-sm @error('system_role') select-error @enderror"></select>
                    @error('system_role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- is_active --}}
                <div class="form-control md:col-span-2">
                    <label class="label cursor-pointer justify-start gap-3 py-0">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <span class="label-text font-medium">Kích hoạt tài khoản ngay</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Ma trận phân quyền (dynamic) ──────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200" x-show="selectedRole" x-transition>
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h2 class="card-title text-base">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Quyền hạn sẽ được cấp
                </h2>
                <span class="badge badge-primary badge-sm" x-text="selectedRoleLabel"></span>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-2 mb-4 p-3 bg-base-200/50 rounded-lg">
                <span class="text-xs text-base-content/50 self-center font-medium">Mức quyền:</span>
                <span class="badge badge-sm badge-success gap-1">Full</span>
                <span class="badge badge-sm badge-info gap-1">Assigned</span>
                <span class="badge badge-sm badge-warning gap-1">Limited</span>
                <span class="badge badge-sm" style="background:oklch(var(--p)/.15);color:oklch(var(--p))">Source view</span>
                <span class="badge badge-sm badge-ghost border border-base-300">Config</span>
                <span class="badge badge-sm" style="background:oklch(0.7 0.15 200/.2);color:oklch(0.4 0.15 200)">Use</span>
                <span class="badge badge-sm" style="background:oklch(0.7 0.1 40/.2);color:oklch(0.5 0.12 40)">Monitor</span>
                <span class="badge badge-sm badge-secondary">Approve/View</span>
            </div>

            {{-- Permission table --}}
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra w-full">
                    <thead>
                        <tr class="text-xs uppercase text-base-content/50">
                            <th class="w-44">Module</th>
                            <th>Mức truy cập</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in currentMatrix" :key="row.module">
                            <tr>
                                <td class="font-medium text-sm" x-text="row.module"></td>
                                <td>
                                    <span :class="row.badgeClass" x-text="row.level"></span>
                                </td>
                                <td class="text-xs text-base-content/60" x-text="row.desc"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Sidebar visibility note --}}
            <div class="mt-3 p-3 bg-base-200/50 rounded-lg" x-show="sidebarModules.length > 0">
                <p class="text-xs text-base-content/50 font-medium mb-1.5">Hiển thị trong sidebar:</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="mod in sidebarModules" :key="mod">
                        <span class="badge badge-sm badge-ghost border border-base-300" x-text="mod"></span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty state when no role selected --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 border-dashed" x-show="!selectedRole">
        <div class="card-body py-8 text-center text-base-content/30">
            <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-sm">Chọn vai trò để xem quyền hạn sẽ được cấp</p>
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit" class="btn btn-primary btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo tài khoản
        </button>
        <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
</div>
@endsection

@push('styles')
<style>
/* ── TomSelect — DaisyUI theme ──────────────────────────────────────────── */
.ts-wrapper.single .ts-control { background:oklch(var(--b1)); border-color:oklch(var(--b3)); color:oklch(var(--bc)); border-radius:.375rem; min-height:2.25rem; padding:.3rem .6rem; font-size:.875rem; }
.ts-wrapper.single.focus .ts-control { border-color:oklch(var(--p)); outline:none; box-shadow:0 0 0 2px oklch(var(--p)/.2); }
.ts-dropdown { background:oklch(var(--b1)); border:1px solid oklch(var(--b3)); border-radius:.5rem; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:9999; }
.ts-dropdown .ts-option { color:oklch(var(--bc)); padding:.45rem .75rem; font-size:.875rem; }
.ts-dropdown .ts-option:hover, .ts-dropdown .ts-option.active { background:oklch(var(--b2)); }
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-wrapper .clear-button { color:oklch(var(--bc)/.4); }
.ts-wrapper .clear-button:hover { color:oklch(var(--bc)); }
.ts-control input { color:oklch(var(--bc)) !important; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('alpine:init', function () {
    var ORGANIZATIONS = @json($organizations->values());
    var ROLES         = @json($roles);
    var OLD_ORG       = '{{ old('organization_id') }}';
    var OLD_ROLE      = '{{ old('system_role') }}';

    // ── Permission matrix data (per per1.png / per2.png) ────────────────
    var LEVEL_META = {
        'Full':          { cls: 'badge badge-sm badge-success',   desc: 'Toàn quyền: xem + tạo + sửa + xóa + gán' },
        'Assigned':      { cls: 'badge badge-sm badge-info',      desc: 'Chỉ xem và sửa record được gán cho bản thân' },
        'Full team':     { cls: 'badge badge-sm badge-success',   desc: 'Toàn quyền trên toàn bộ team' },
        'Limited':       { cls: 'badge badge-sm badge-warning',   desc: 'Chỉ xem, không tạo/sửa/xóa' },
        'Source view':   { cls: 'badge badge-sm badge-primary',   desc: 'Xem nguồn lead, ẩn thông tin cá nhân' },
        'Config':        { cls: 'badge badge-sm badge-ghost border border-base-300', desc: 'Cấu hình module, không xem dữ liệu nghiệp vụ' },
        'Config prompt': { cls: 'badge badge-sm badge-ghost border border-base-300', desc: 'Chỉnh sửa prompt AI trong module' },
        'Admin config':  { cls: 'badge badge-sm badge-ghost border border-base-300', desc: 'Cấu hình cấp quản trị' },
        'Full config':   { cls: 'badge badge-sm badge-ghost border border-base-300', desc: 'Toàn quyền cấu hình' },
        'AI config':     { cls: 'badge badge-sm badge-ghost border border-base-300', desc: 'Cấu hình AI trong module' },
        'Use':           { cls: 'badge badge-sm',                 desc: 'Sử dụng output AI, không cấu hình prompt', style: 'background:oklch(0.7 0.15 200/.2);color:oklch(0.4 0.15 200)' },
        'Monitor':       { cls: 'badge badge-sm',                 desc: 'Theo dõi trạng thái, không chỉnh sửa', style: 'background:oklch(0.7 0.1 40/.2);color:oklch(0.5 0.12 40)' },
        'Monitor/Edit':  { cls: 'badge badge-sm',                 desc: 'Theo dõi và chỉnh sửa workflow', style: 'background:oklch(0.7 0.1 40/.2);color:oklch(0.5 0.12 40)' },
        'Approve/View':  { cls: 'badge badge-sm badge-secondary', desc: 'Xem toàn bộ và phê duyệt/từ chối' },
        'View':          { cls: 'badge badge-sm badge-ghost',     desc: 'Chỉ xem, không thao tác' },
        'View related':  { cls: 'badge badge-sm badge-ghost',     desc: 'Xem tài liệu liên quan đến phòng ban' },
        'View summary':  { cls: 'badge badge-sm badge-ghost',     desc: 'Xem bản tóm tắt, không xem chi tiết' },
        'View ltd':      { cls: 'badge badge-sm badge-ghost',     desc: 'Xem giới hạn, chỉ dữ liệu được chia sẻ' },
        'HR tasks':      { cls: 'badge badge-sm badge-info',      desc: 'Chỉ xem task của bộ phận HR' },
        'Create/Edit':   { cls: 'badge badge-sm badge-success',   desc: 'Tạo và chỉnh sửa nội dung' },
        'Create HR SOP': { cls: 'badge badge-sm badge-info',      desc: 'Chỉ tạo SOP cho bộ phận HR' },
        'Personal/team': { cls: 'badge badge-sm badge-info',      desc: 'Báo cáo cá nhân và đội nhóm' },
        'Operations':    { cls: 'badge badge-sm badge-info',      desc: 'Báo cáo phạm vi vận hành' },
        'Marketing':     { cls: 'badge badge-sm badge-info',      desc: 'Báo cáo phạm vi marketing' },
        'HR':            { cls: 'badge badge-sm badge-info',      desc: 'Báo cáo phạm vi nhân sự' },
        'AI usage':      { cls: 'badge badge-sm badge-info',      desc: 'Báo cáo sử dụng AI' },
        'Shared only':   { cls: 'badge badge-sm badge-ghost',     desc: 'Chỉ xem báo cáo được chia sẻ' },
    };

    var MATRIX = @json($permissionMatrix);

    // Sidebar modules per role (from RoleEnum::visibleModules())
    var SIDEBAR = {
        'ceo':          ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Reports'],
        'sales':        ['CRM','Sales AI','Tasks','SOP','Reports'],
        'ops':          ['CEO Dashboard','CRM','Tasks','SOP','Workflow','AI Logs','Reports'],
        'marketing':    ['CRM','Sales AI','Tasks','SOP','Reports'],
        'hr':           ['Tasks','SOP','Users','Reports'],
        'ai_operator':  ['CEO Dashboard','CRM','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Reports'],
        'system_admin': ['CEO Dashboard','CRM','Sales AI','Tasks','SOP','Workflow','Prompt Mgmt','AI Logs','Users','Roles/Perms','Reports','Integrations'],
        'viewer':       ['CEO Dashboard','Tasks','SOP','Reports'],
    };

    Alpine.data('createUserPage', function () {
        return {
            selectedRole:      OLD_ROLE,
            selectedRoleLabel: '',

            get currentMatrix() {
                var role = this.selectedRole;
                if (!role) return [];
                var rows = [];
                Object.keys(MATRIX).forEach(function (module) {
                    var level = MATRIX[module][role];
                    if (level) {
                        var meta = LEVEL_META[level] || { cls: 'badge badge-sm badge-ghost', desc: '' };
                        rows.push({
                            module:     module,
                            level:      level,
                            badgeClass: meta.cls,
                            desc:       meta.desc,
                        });
                    }
                });
                return rows;
            },

            get sidebarModules() {
                return SIDEBAR[this.selectedRole] || [];
            },

            init: function () {
                var self = this;
                if (OLD_ROLE) {
                    var found = ROLES.find(function (r) { return r.value === OLD_ROLE; });
                    if (found) self.selectedRoleLabel = found.label;
                }
                document.addEventListener('DOMContentLoaded', function () {
                    self._setup();
                }, { once: true });
            },

            _setup: function () {
                var self = this;

                // ── Organization TomSelect ───────────────────────────────
                new window.TomSelect('#org-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn tổ chức...',
                    maxOptions:     null,
                    searchField:    ['text'],
                    options:        ORGANIZATIONS.map(function (o) { return { value: String(o.id), text: o.name }; }),
                    items:          OLD_ORG ? [String(OLD_ORG)] : [],
                    render: {
                        no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; },
                    },
                });

                // ── Role TomSelect ───────────────────────────────────────
                new window.TomSelect('#role-select', {
                    dropdownParent: 'body',
                    placeholder:    'Chọn vai trò...',
                    searchField:    ['text'],
                    options:        ROLES.map(function (r) { return { value: r.value, text: r.label }; }),
                    items:          OLD_ROLE ? [OLD_ROLE] : [],
                    render: {
                        option: function (data, escape) {
                            return '<div class="flex items-center gap-2 py-0.5">'
                                + '<span class="font-medium">' + escape(data.text) + '</span>'
                                + '</div>';
                        },
                        no_results: function () { return '<div class="no-results p-3 text-sm opacity-50">Không tìm thấy</div>'; },
                    },
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

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
    'oldUserId'   => old('user_id'),
    'oldRoleId'   => old('role_id'),
    'oldBranchId' => old('scope_branch_id'),
    'oldDeptId'   => old('scope_dept_id'),
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

{{-- Error banner --}}
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

<form method="POST" action="{{ route('backend.role-scopes.store') }}" novalidate data-role-scope-form>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_268px] gap-6 items-start">

        {{-- ── Card chính ───────────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">

                <h2 class="card-title text-base mb-5">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Phân quyền
                </h2>

                <div class="space-y-4">

                    {{-- User --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">User <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs text-base-content/40">User trong tổ chức</span>
                        </label>
                        <select id="select-user" name="user_id"
                                class="select select-bordered select-sm w-full @error('user_id') select-error @enderror"
                                data-req="Vui lòng chọn user"></select>
                        @error('user_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Role --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs text-base-content/40">Role Spatie RBAC</span>
                        </label>
                        <select id="select-role" name="role_id"
                                class="select select-bordered select-sm w-full @error('role_id') select-error @enderror"
                                data-req="Vui lòng chọn role"></select>
                        @error('role_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="divider my-2 text-xs text-base-content/30">Phạm vi áp dụng</div>

                    {{-- Scope info --}}
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

                    {{-- Branch --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Chi nhánh áp dụng</span>
                            <span class="label-text-alt text-xs text-base-content/40">Để trống = toàn tổ chức</span>
                        </label>
                        <select id="select-branch" name="scope_branch_id"
                                class="select select-bordered select-sm w-full @error('scope_branch_id') select-error @enderror"></select>
                        @error('scope_branch_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Department (cascade) --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Phòng ban áp dụng</span>
                            <span class="label-text-alt text-xs text-base-content/40">Cần chọn chi nhánh trước</span>
                        </label>
                        <select id="select-dept" name="scope_dept_id"
                                :disabled="!selectedBranchId"
                                class="select select-bordered select-sm w-full @error('scope_dept_id') select-error @enderror"></select>
                        @error('scope_dept_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        <p x-show="!selectedBranchId" class="mt-1 text-xs text-base-content/40">
                            Chọn chi nhánh trước để lọc phòng ban
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">

                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">
                        Thời hạn & Ghi chú
                    </p>

                    <div class="form-control mb-3">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Hết hạn vào</span>
                            <span class="label-text-alt text-xs text-base-content/40">Để trống = vĩnh viễn</span>
                        </label>
                        <input type="text" name="expires_at" id="fp-expires-at"
                               value="{{ old('expires_at') }}"
                               class="input input-bordered input-sm w-full @error('expires_at') input-error @enderror"
                               placeholder="DD/MM/YYYY HH:MM" readonly>
                        @error('expires_at')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control mb-4">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Lý do cấp</span>
                        </label>
                        <textarea name="note" rows="3"
                                  class="textarea textarea-bordered textarea-sm w-full @error('note') textarea-error @enderror"
                                  placeholder="Ghi chú lý do cấp quyền này...">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('backend.role-scopes.index') }}"
                           class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Cấp quyền
                        </button>
                    </div>

                    <p class="text-center text-xs text-base-content/30 mt-2.5">
                        <span class="text-error">*</span> là trường bắt buộc
                    </p>

                </div>
            </div>

        </div>{{-- /sidebar --}}

    </div>{{-- /grid --}}
</form>
</div>
@endsection

@push('styles')
    @vite(['Modules/RoleScope/resources/assets/sass/role-scope.scss'], 'build/backend')
@endpush

@push('scripts')
    @vite([
        'resources/js/modules/toastify.js',
        'resources/js/modules/tom-select.js',
        'resources/js/modules/flatpickr.js',
        'Modules/RoleScope/resources/assets/js/role-scope.js',
    ], 'build/backend')
@endpush

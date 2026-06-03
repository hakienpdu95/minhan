@extends('layouts.backend')
@section('title', 'Chỉnh sửa phân quyền')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.role-scopes.index') }}">Phân quyền phạm vi</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.role-scopes.show', $roleScope) }}">Chi tiết #{{ $roleScope->id }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa phân quyền</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Chỉ có thể thay đổi thời hạn và ghi chú. Để thay đổi phạm vi, cần thu hồi và cấp mới.</p>
    </div>
    <a href="{{ route('backend.role-scopes.show', $roleScope) }}" class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST" action="{{ route('backend.role-scopes.update', $roleScope) }}" novalidate data-role-scope-form>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_268px] gap-6 items-start">

        {{-- ── Card chính ───────────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">

                {{-- Readonly info block --}}
                <h2 class="card-title text-base mb-5">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Phân quyền
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-base-200/50 rounded-xl mb-4">
                    <div>
                        <p class="text-xs text-base-content/50 mb-1">User</p>
                        <p class="font-medium text-sm">{{ $roleScope->user?->name ?? '—' }}</p>
                        <p class="text-xs text-base-content/40">{{ $roleScope->user?->email ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/50 mb-1">Role</p>
                        <span class="badge badge-outline badge-sm">{{ $roleScope->role?->name ?? '—' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-base-content/50 mb-1">Phạm vi</p>
                        <p class="text-sm">{{ $roleScope->scope_label }}</p>
                    </div>
                </div>

                <div class="divider my-2 text-xs text-base-content/30">Chỉnh sửa được</div>

                <div class="space-y-4">

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Hết hạn vào</span>
                            <span class="label-text-alt text-xs text-base-content/40">Để trống = vĩnh viễn</span>
                        </label>
                        <input type="text" name="expires_at" id="fp-expires-at"
                               value="{{ old('expires_at', $roleScope->expires_at?->format('Y-m-d H:i') ?? '') }}"
                               class="input input-bordered input-sm w-full @error('expires_at') input-error @enderror"
                               placeholder="DD/MM/YYYY HH:MM" readonly>
                        @error('expires_at')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Lý do / Ghi chú</span>
                            <span class="label-text-alt text-xs text-base-content/40">Không bắt buộc</span>
                        </label>
                        <textarea name="note" rows="3"
                                  class="textarea textarea-bordered textarea-sm w-full @error('note') textarea-error @enderror"
                                  placeholder="Ghi chú...">{{ old('note', $roleScope->note) }}</textarea>
                        @error('note')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">

                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">
                        Xuất bản
                    </p>

                    <div class="flex justify-between text-xs text-base-content/40 mb-4 px-0.5">
                        <span>Tạo {{ $roleScope->created_at->format('d/m/Y') }}</span>
                        <span>Sửa {{ $roleScope->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('backend.role-scopes.show', $roleScope) }}"
                           class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Lưu lại
                        </button>
                    </div>

                </div>
            </div>

        </div>{{-- /sidebar --}}

    </div>{{-- /grid --}}
</form>

{{-- Danger zone — nằm ngoài form update để tránh nested form --}}
@can('delete', $roleScope)
<div class="mt-6 border border-error/30 rounded-xl p-4">
    <p class="text-sm font-semibold text-error mb-1">Vùng nguy hiểm</p>
    <p class="text-xs text-base-content/50 mb-3">Thu hồi quyền này sẽ xóa vĩnh viễn phân quyền. Thao tác không thể hoàn tác.</p>
    <form method="POST" action="{{ route('backend.role-scopes.destroy', $roleScope) }}"
          onsubmit="return confirm('Thu hồi quyền này không thể hoàn tác. Xác nhận?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-error btn-outline btn-sm gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            Thu hồi quyền
        </button>
    </form>
</div>
@endcan

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

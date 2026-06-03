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
<div>

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

<form method="POST" action="{{ route('backend.role-scopes.update', $roleScope) }}" novalidate>
    @csrf @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

        {{-- ── Readonly info ─────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-4">
                <h3 class="font-semibold text-sm text-base-content/60">Thông tin cố định (chỉ đọc)</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-base-200/50 rounded-xl">
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

                <div class="divider my-0 text-xs text-base-content/40">Chỉnh sửa được</div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Hết hạn vào</span>
                        <span class="label-text-alt text-xs opacity-50">Để trống = vĩnh viễn</span>
                    </label>
                    <input type="datetime-local" name="expires_at"
                           value="{{ old('expires_at', $roleScope->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="input input-bordered @error('expires_at') input-error @enderror"/>
                    @error('expires_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Lý do / Ghi chú</span>
                    </label>
                    <textarea name="note" rows="3"
                              class="textarea textarea-bordered @error('note') textarea-error @enderror"
                              placeholder="Ghi chú...">{{ old('note', $roleScope->note) }}</textarea>
                    @error('note')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ── Actions ───────────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-3">
                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lưu thay đổi
                </button>
                <a href="{{ route('backend.role-scopes.show', $roleScope) }}" class="btn btn-ghost w-full">Hủy</a>

                @can('delete', $roleScope)
                <div class="divider my-0 text-xs text-base-content/40">Vùng nguy hiểm</div>
                <form method="POST" action="{{ route('backend.role-scopes.destroy', $roleScope) }}"
                      onsubmit="return confirm('Thu hồi quyền này không thể hoàn tác. Xác nhận?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-error btn-outline w-full btn-sm">
                        Thu hồi quyền
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</form>
</div>
@endsection

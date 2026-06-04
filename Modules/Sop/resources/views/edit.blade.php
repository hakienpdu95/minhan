@extends('layouts.backend')
@section('title', 'Sửa SOP — ' . $sop->code)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.sop.index') }}">Quy trình SOP</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.sop.show', $sop) }}">{{ $sop->code }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')
<div>

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-0.5">
            <span class="font-mono text-sm font-semibold text-primary">{{ $sop->code }}</span>
            <span class="badge badge-sm {{ $sop->status?->badgeClass() }}">{{ $sop->status?->label() }}</span>
        </div>
        <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa quy trình</h1>
    </div>
    <a href="{{ route('backend.sop.show', $sop) }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($sop->status?->value === 'approved')
<div class="alert alert-warning py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <p>SOP này đã được duyệt. Thay đổi sẽ yêu cầu gửi duyệt lại.</p>
</div>
@endif

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

<form method="POST" action="{{ route('backend.sop.update', $sop) }}" novalidate>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

        {{-- ── Main card ───────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Identity --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <h2 class="text-sm font-semibold text-base-content/70 mb-4">Thông tin cơ bản</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">

                        {{-- Code (readonly) --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Mã SOP</span></label>
                            <input type="text" value="{{ $sop->code }}" readonly
                                   class="input input-bordered input-sm font-mono bg-base-200 cursor-not-allowed opacity-60"/>
                            <p class="text-xs text-base-content/40 mt-1">Mã SOP không thể thay đổi sau khi tạo</p>
                        </div>

                        {{-- Title --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Tên quy trình <span class="text-error">*</span></span></label>
                            <input type="text" name="title" value="{{ old('title', $sop->title) }}"
                                   class="input input-bordered input-sm @error('title') input-error @enderror"
                                   placeholder="Nhập tên quy trình..."/>
                            @error('title')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                    </div>

                    {{-- Description --}}
                    <div class="form-control mt-4">
                        <label class="label pb-1"><span class="label-text text-xs font-medium">Mô tả tổng quan</span></label>
                        <textarea name="description" rows="3"
                                  class="textarea textarea-bordered text-sm @error('description') textarea-error @enderror"
                                  placeholder="Mô tả mục tiêu, phạm vi áp dụng của quy trình...">{{ old('description', $sop->description) }}</textarea>
                        @error('description')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Scope --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <h2 class="text-sm font-semibold text-base-content/70 mb-4">Phạm vi & Phân công</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Type --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Loại SOP <span class="text-error">*</span></span></label>
                            <select id="field-type" name="type"
                                    class="select select-bordered select-sm @error('type') select-error @enderror">
                                @foreach($types as $t)
                                <option value="{{ $t['value'] }}" {{ old('type', $sop->type?->value) === $t['value'] ? 'selected' : '' }}>{{ $t['text'] }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Owner --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Người phụ trách <span class="text-error">*</span></span></label>
                            <select id="field-owner" name="owner_id"
                                    class="@error('owner_id') border-error @enderror">
                                <option value="">Chọn người phụ trách...</option>
                                @foreach($owners as $u)
                                <option value="{{ $u->id }}" {{ old('owner_id', $sop->owner_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('owner_id')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Department --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Phòng ban áp dụng</span></label>
                            <select id="field-department" name="department_id">
                                <option value="">Tất cả phòng ban...</option>
                                @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id', $sop->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Branch --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Chi nhánh áp dụng</span></label>
                            <select id="field-branch" name="branch_id">
                                <option value="">Tất cả chi nhánh...</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $sop->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Validity --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <h2 class="text-sm font-semibold text-base-content/70 mb-4">Thời hạn hiệu lực</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Ngày hiệu lực</span></label>
                            <input type="text" id="field-effective-date" name="effective_date"
                                   value="{{ old('effective_date', $sop->effective_date?->format('Y-m-d')) }}"
                                   class="input input-bordered input-sm @error('effective_date') input-error @enderror"
                                   placeholder="dd/mm/yyyy" autocomplete="off"/>
                            @error('effective_date')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Ngày hết hạn</span></label>
                            <input type="text" id="field-expired-date" name="expired_date"
                                   value="{{ old('expired_date', $sop->expired_date?->format('Y-m-d')) }}"
                                   class="input input-bordered input-sm @error('expired_date') input-error @enderror"
                                   placeholder="dd/mm/yyyy" autocomplete="off"/>
                            @error('expired_date')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">Trạng thái hiện tại</p>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="badge badge-sm {{ $sop->status?->badgeClass() }}">{{ $sop->status?->label() }}</span>
                        @if($sop->version > 0)
                        <span class="badge badge-sm badge-outline">v{{ $sop->version }}</span>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-full">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Lưu thay đổi
                        </button>
                        <a href="{{ route('backend.sop.show', $sop) }}" class="btn btn-ghost btn-sm w-full">Hủy</a>
                    </div>
                </div>
            </div>

            @can('delete', $sop)
            <div class="card bg-base-100 shadow-sm border border-error/20">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-error/70 uppercase tracking-wider mb-3">Vùng nguy hiểm</p>
                    <form method="POST" action="{{ route('backend.sop.destroy', $sop) }}"
                          onsubmit="return confirm('Bạn có chắc muốn lưu trữ SOP {{ addslashes($sop->code) }}? Thao tác này sẽ ẩn SOP khỏi danh sách.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-outline btn-sm w-full">
                            Lưu trữ SOP
                        </button>
                    </form>
                </div>
            </div>
            @endcan
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
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-wrapper .clear-button { color:oklch(var(--bc)/.4); }
.ts-control input { color:oklch(var(--bc)) !important; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js', 'resources/js/modules/flatpickr.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function () {
    new window.TomSelect('#field-owner', {
        dropdownParent: 'body', maxOptions: null, plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });
    new window.TomSelect('#field-department', {
        dropdownParent: 'body', placeholder: 'Tất cả phòng ban...', maxOptions: null, plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });
    new window.TomSelect('#field-branch', {
        dropdownParent: 'body', placeholder: 'Tất cả chi nhánh...', maxOptions: null, plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });
    if (window.flatpickr) {
        window.flatpickr('#field-effective-date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true });
        window.flatpickr('#field-expired-date',   { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', allowInput: true });
    }
});
</script>
@endpush

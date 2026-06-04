@extends('layouts.backend')
@section('title', 'Tạo SOP mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.sop.index') }}">Quy trình SOP</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
<div x-data="{
    errs: {{ Js::from($errors->keys()) }},
}">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tạo SOP mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Định nghĩa quy trình vận hành chuẩn mới</p>
    </div>
    <a href="{{ route('backend.sop.index') }}" class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST" action="{{ route('backend.sop.store') }}" novalidate>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

        {{-- ── Main card ───────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Identity --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <h2 class="text-sm font-semibold text-base-content/70 mb-4">Thông tin cơ bản</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">

                        {{-- Code --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Mã SOP <span class="text-error">*</span></span></label>
                            <input type="text" name="code" value="{{ old('code') }}"
                                   class="input input-bordered input-sm font-mono uppercase @error('code') input-error @enderror"
                                   placeholder="vd: SOP-HR-001"
                                   oninput="this.value=this.value.toUpperCase()"/>
                            @error('code')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Title --}}
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Tên quy trình <span class="text-error">*</span></span></label>
                            <input type="text" name="title" value="{{ old('title') }}"
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
                                  placeholder="Mô tả mục tiêu, phạm vi áp dụng của quy trình...">{{ old('description') }}</textarea>
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
                                <option value="{{ $t['value'] }}" {{ old('type') === $t['value'] ? 'selected' : '' }}>{{ $t['text'] }}</option>
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
                                <option value="{{ $u->id }}" {{ old('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
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
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
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
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
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
                                   value="{{ old('effective_date') }}"
                                   class="input input-bordered input-sm @error('effective_date') input-error @enderror"
                                   placeholder="dd/mm/yyyy" autocomplete="off"/>
                            @error('effective_date')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs font-medium">Ngày hết hạn</span></label>
                            <input type="text" id="field-expired-date" name="expired_date"
                                   value="{{ old('expired_date') }}"
                                   class="input input-bordered input-sm @error('expired_date') input-error @enderror"
                                   placeholder="dd/mm/yyyy" autocomplete="off"/>
                            @error('expired_date')<p class="text-xs text-error mt-1">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ── Sidebar actions ─────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-3">Trạng thái</p>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="badge badge-ghost badge-sm">Nháp</span>
                        <span class="text-xs text-base-content/40">Mới tạo</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-full">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Lưu SOP
                        </button>
                        <a href="{{ route('backend.sop.index') }}" class="btn btn-ghost btn-sm w-full">Hủy</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-2">Quy ước mã SOP</p>
                    <div class="text-xs text-base-content/60 space-y-1">
                        <p><span class="font-mono font-semibold">SOP-HR-001</span> — HR, số thứ tự</p>
                        <p><span class="font-mono font-semibold">SOP-OPS-012</span> — Vận hành</p>
                        <p><span class="font-mono font-semibold">SOP-SALE-003</span> — Kinh doanh</p>
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
.ts-dropdown .ts-option.selected { background:oklch(var(--p)/.15); color:oklch(var(--p)); }
.ts-wrapper .clear-button { color:oklch(var(--bc)/.4); }
.ts-control input { color:oklch(var(--bc)) !important; }
.flatpickr-input { background:oklch(var(--b1)); }
</style>
@endpush

@push('scripts')
@vite(['resources/js/modules/tom-select.js', 'resources/js/modules/flatpickr.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // TomSelect — owner
    new window.TomSelect('#field-owner', {
        dropdownParent: 'body', placeholder: 'Chọn người phụ trách...', maxOptions: null,
        plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });

    // TomSelect — department
    new window.TomSelect('#field-department', {
        dropdownParent: 'body', placeholder: 'Tất cả phòng ban...', maxOptions: null,
        plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });

    // TomSelect — branch
    new window.TomSelect('#field-branch', {
        dropdownParent: 'body', placeholder: 'Tất cả chi nhánh...', maxOptions: null,
        plugins: ['clear_button'],
        render: { no_results: function () { return '<div class="p-3 text-sm opacity-50">Không tìm thấy</div>'; } },
    });

    // Flatpickr — effective date
    if (window.flatpickr) {
        window.flatpickr('#field-effective-date', {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
            locale: 'vn', allowInput: true,
        });
        window.flatpickr('#field-expired-date', {
            dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
            locale: 'vn', allowInput: true,
        });
    }
});
</script>
@endpush

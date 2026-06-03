@extends('layouts.backend')
@section('title', 'Thêm phòng ban mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.departments.index') }}">Phòng ban</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div x-data="{
    tab: 'basic',
    tabFields: {
        basic:   ['name', 'code', 'status', 'function', 'parent_id', 'branch_id'],
        detail:  ['budget_code', 'headcount_limit', 'description', 'effective_from', 'effective_to'],
        contact: ['internal_phone', 'internal_email'],
    },
    errs: {{ Js::from($errors->keys()) }},
    errCount(t) {
        return this.tabFields[t].filter(f => this.errs.includes(f)).length;
    },
    init() {
        const order = ['basic','detail','contact'];
        for (const t of order) {
            if (this.errCount(t) > 0) { this.tab = t; break; }
        }
    }
}">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Thêm phòng ban mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Điền đầy đủ thông tin để tạo phòng ban</p>
    </div>
    <a href="{{ route('backend.departments.index') }}" class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST" action="{{ route('backend.departments.store') }}" novalidate>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_260px] gap-6 items-start">

        {{-- ── Card chính với tabs ──────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">

            {{-- Tab navigation --}}
            <div class="border-b border-base-200 px-6">
                <nav class="flex -mb-px" role="tablist">
                    @php
                    $tabs = [
                        ['key' => 'basic',   'label' => 'Thông tin cơ bản'],
                        ['key' => 'detail',  'label' => 'Chi tiết'],
                        ['key' => 'contact', 'label' => 'Liên hệ nội bộ'],
                    ];
                    @endphp

                    @foreach($tabs as $t)
                    <button type="button" role="tab"
                            @click="tab = '{{ $t['key'] }}'"
                            :aria-selected="tab === '{{ $t['key'] }}'"
                            class="flex items-center gap-1.5 px-1 py-4 mr-6 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === '{{ $t['key'] }}'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        {{ $t['label'] }}
                        <template x-if="errCount('{{ $t['key'] }}') > 0">
                            <span class="badge badge-error badge-xs" x-text="errCount('{{ $t['key'] }}')"></span>
                        </template>
                    </button>
                    @endforeach
                </nav>
            </div>

            <div class="card-body gap-5">

                {{-- ── Tab: Thông tin cơ bản ───────────── --}}
                <div x-show="tab === 'basic'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Tên phòng ban <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="input input-bordered @error('name') input-error @enderror"
                               placeholder="VD: Phòng Kinh doanh"/>
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mã phòng ban <span class="text-error">*</span></span></label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               class="input input-bordered font-mono @error('code') input-error @enderror"
                               placeholder="VD: KD01"/>
                        @error('code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span></label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror">
                            @foreach($statuses as $s)
                            <option value="{{ $s['value'] }}" {{ old('status', 'active') === $s['value'] ? 'selected' : '' }}>
                                {{ $s['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chức năng</span></label>
                        <select name="function" class="select select-bordered @error('function') select-error @enderror">
                            <option value="">— Không xác định —</option>
                            @foreach($functions as $f)
                            <option value="{{ $f['value'] }}" {{ old('function') === $f['value'] ? 'selected' : '' }}>
                                {{ $f['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('function')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Chi nhánh</span>
                            <span class="label-text-alt text-xs opacity-50">NULL = cấp tổ chức</span>
                        </label>
                        <select name="branch_id" class="select select-bordered @error('branch_id') select-error @enderror">
                            <option value="">— Toàn tổ chức —</option>
                            @foreach($branchOptions as $opt)
                            <option value="{{ $opt['value'] }}" {{ old('branch_id') == $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('branch_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phòng ban cha</span></label>
                        <select name="parent_id" class="select select-bordered @error('parent_id') select-error @enderror">
                            <option value="">— Không có (root) —</option>
                            @foreach($parentOptions as $opt)
                            <option value="{{ $opt['value'] }}" {{ old('parent_id') == $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- ── Tab: Chi tiết ────────────────────── --}}
                <div x-show="tab === 'detail'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Mã trung tâm chi phí</span>
                            <span class="label-text-alt text-xs opacity-50">Budget code</span>
                        </label>
                        <input type="text" name="budget_code" value="{{ old('budget_code') }}"
                               class="input input-bordered font-mono @error('budget_code') input-error @enderror"
                               placeholder="VD: CC-KD-001"/>
                        @error('budget_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Biên chế tối đa</span>
                            <span class="label-text-alt text-xs opacity-50">Headcount limit</span>
                        </label>
                        <input type="number" name="headcount_limit" value="{{ old('headcount_limit') }}"
                               min="1" class="input input-bordered @error('headcount_limit') input-error @enderror"
                               placeholder="VD: 20"/>
                        @error('headcount_limit')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày thành lập</span></label>
                        <input type="date" name="effective_from" value="{{ old('effective_from') }}"
                               class="input input-bordered @error('effective_from') input-error @enderror"/>
                        @error('effective_from')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày giải thể</span></label>
                        <input type="date" name="effective_to" value="{{ old('effective_to') }}"
                               class="input input-bordered @error('effective_to') input-error @enderror"/>
                        @error('effective_to')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Chức năng nhiệm vụ</span></label>
                        <textarea name="description" rows="4"
                                  class="textarea textarea-bordered @error('description') textarea-error @enderror"
                                  placeholder="Mô tả chức năng, nhiệm vụ của phòng ban...">{{ old('description') }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- ── Tab: Liên hệ nội bộ ─────────────── --}}
                <div x-show="tab === 'contact'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Điện thoại nội bộ</span></label>
                        <input type="text" name="internal_phone" value="{{ old('internal_phone') }}"
                               class="input input-bordered @error('internal_phone') input-error @enderror"
                               placeholder="VD: 101"/>
                        @error('internal_phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email nội bộ</span></label>
                        <input type="email" name="internal_email" value="{{ old('internal_email') }}"
                               class="input input-bordered @error('internal_email') input-error @enderror"
                               placeholder="VD: kinhdoanh@company.vn"/>
                        @error('internal_email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

            </div>
        </div>

        {{-- ── Sidebar: Actions ─────────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-3">
                    <h3 class="font-semibold text-sm">Thao tác</h3>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Lưu phòng ban
                    </button>
                    <a href="{{ route('backend.departments.index') }}" class="btn btn-ghost w-full">Hủy</a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-2">
                    <h3 class="font-semibold text-sm">Hướng dẫn</h3>
                    <ul class="text-xs text-base-content/60 space-y-1.5 list-disc list-inside">
                        <li>Mã phòng ban phải duy nhất trong org (VD: HR01, IT02)</li>
                        <li>Tối đa 3 cấp phân cấp</li>
                        <li>Chi nhánh = NULL → phòng ban cấp tổ chức (Kế toán, Pháp chế...)</li>
                        <li>Biên chế chỉ hiển thị cảnh báo, không chặn thêm nhân viên</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
    @vite([
        'Modules/Department/resources/assets/js/department.js',
    ], 'build/backend')
@endpush

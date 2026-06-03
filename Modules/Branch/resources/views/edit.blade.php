@extends('layouts.backend')
@section('title', 'Sửa chi nhánh: ' . $branch->name)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.branches.index') }}">Chi nhánh</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.branches.show', $branch) }}">{{ $branch->name }}</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<div x-data="{
    tab: 'basic',
    tabFields: {
        basic:   ['name', 'code', 'type', 'status', 'parent_id'],
        contact: ['phone', 'email', 'fax', 'tax_code'],
        address: ['province_code', 'ward_code', 'address', 'lat', 'lng'],
        settings:['timezone', 'currency', 'opened_at', 'closed_at'],
    },
    errs: {{ Js::from($errors->keys()) }},
    errCount(t) {
        return this.tabFields[t].filter(f => this.errs.includes(f)).length;
    },
    init() {
        const order = ['basic','contact','address','settings'];
        for (const t of order) {
            if (this.errCount(t) > 0) { this.tab = t; break; }
        }
    }
}">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Sửa chi nhánh</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ $branch->name }} <span class="font-mono opacity-60">({{ $branch->code }})</span></p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('backend.branches.show', $branch) }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Xem chi tiết
        </a>
    </div>
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

<form method="POST" action="{{ route('backend.branches.update', $branch) }}" novalidate>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_260px] gap-6 items-start">

        {{-- ── Card chính với tabs ──────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">

            {{-- Tab navigation --}}
            <div class="border-b border-base-200 px-6">
                <nav class="flex -mb-px" role="tablist">
                    @php
                    $tabs = [
                        ['key' => 'basic',    'label' => 'Thông tin cơ bản'],
                        ['key' => 'contact',  'label' => 'Liên hệ'],
                        ['key' => 'address',  'label' => 'Địa chỉ & Toạ độ'],
                        ['key' => 'settings', 'label' => 'Cài đặt'],
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
                        <label class="label"><span class="label-text font-medium">Tên chi nhánh <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name', $branch->name) }}"
                               class="input input-bordered @error('name') input-error @enderror"
                               placeholder="VD: Chi nhánh Hà Nội"/>
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mã chi nhánh <span class="text-error">*</span></span></label>
                        <input type="text" name="code" value="{{ old('code', $branch->code) }}"
                               class="input input-bordered font-mono @error('code') input-error @enderror"
                               placeholder="VD: HN01"/>
                        @error('code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Chi nhánh cha</span></label>
                        <select name="parent_id"
                                class="select select-bordered @error('parent_id') select-error @enderror">
                            <option value="">— Không có (root) —</option>
                            @foreach($parentOptions as $opt)
                            <option value="{{ $opt['value'] }}"
                                    {{ old('parent_id', $branch->parent_id) == $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Loại <span class="text-error">*</span></span></label>
                        <select name="type" class="select select-bordered @error('type') select-error @enderror">
                            @foreach($types as $t)
                            <option value="{{ $t['value'] }}"
                                    {{ old('type', $branch->type->value) === $t['value'] ? 'selected' : '' }}>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span></label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror">
                            @foreach($statuses as $s)
                            <option value="{{ $s['value'] }}"
                                    {{ old('status', $branch->status->value) === $s['value'] ? 'selected' : '' }}>
                                {{ $s['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- ── Tab: Liên hệ ─────────────────────── --}}
                <div x-show="tab === 'contact'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Số điện thoại</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $branch->phone) }}"
                               class="input input-bordered @error('phone') input-error @enderror"/>
                        @error('phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email</span></label>
                        <input type="email" name="email" value="{{ old('email', $branch->email) }}"
                               class="input input-bordered @error('email') input-error @enderror"/>
                        @error('email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Fax</span></label>
                        <input type="text" name="fax" value="{{ old('fax', $branch->fax) }}"
                               class="input input-bordered @error('fax') input-error @enderror"/>
                        @error('fax')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Mã số thuế chi nhánh</span>
                            <span class="label-text-alt text-xs opacity-50">Hóa đơn điện tử</span>
                        </label>
                        <input type="text" name="tax_code" value="{{ old('tax_code', $branch->tax_code) }}"
                               class="input input-bordered font-mono @error('tax_code') input-error @enderror"/>
                        @error('tax_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- ── Tab: Địa chỉ & Toạ độ ──────────── --}}
                <div x-show="tab === 'address'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Tỉnh / Thành phố</span></label>
                        <select id="edit-form-province" name="province_code"
                                class="select select-bordered @error('province_code') select-error @enderror">
                            <option value="">Chọn tỉnh/thành...</option>
                            @foreach($provinces as $prov)
                            <option value="{{ $prov->province_code }}"
                                    {{ old('province_code', $branch->province_code) === $prov->province_code ? 'selected' : '' }}>
                                {{ $prov->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('province_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phường / Xã</span></label>
                        <select id="edit-form-ward" name="ward_code"
                                class="select select-bordered @error('ward_code') select-error @enderror"
                                {{ !old('province_code', $branch->province_code) ? 'disabled' : '' }}>
                            <option value="">{{ old('province_code', $branch->province_code) ? 'Chọn phường/xã...' : 'Chọn tỉnh trước...' }}</option>
                        </select>
                        @error('ward_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Địa chỉ</span></label>
                        <input type="text" name="address" value="{{ old('address', $branch->address) }}"
                               class="input input-bordered @error('address') input-error @enderror"
                               placeholder="Số nhà, đường, phường/xã, quận/huyện"/>
                        @error('address')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Vĩ độ (Lat)</span></label>
                        <input type="number" name="lat" value="{{ old('lat', $branch->lat) }}" step="0.0000001"
                               class="input input-bordered font-mono @error('lat') input-error @enderror"/>
                        @error('lat')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Kinh độ (Lng)</span></label>
                        <input type="number" name="lng" value="{{ old('lng', $branch->lng) }}" step="0.0000001"
                               class="input input-bordered font-mono @error('lng') input-error @enderror"/>
                        @error('lng')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- ── Tab: Cài đặt ─────────────────────── --}}
                <div x-show="tab === 'settings'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Múi giờ</span>
                            <span class="label-text-alt text-xs opacity-50">NULL = kế thừa từ org</span>
                        </label>
                        <input type="text" name="timezone" value="{{ old('timezone', $branch->timezone) }}"
                               class="input input-bordered @error('timezone') input-error @enderror"
                               placeholder="Asia/Ho_Chi_Minh"/>
                        @error('timezone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Tiền tệ</span>
                            <span class="label-text-alt text-xs opacity-50">ISO 4217</span>
                        </label>
                        <input type="text" name="currency" value="{{ old('currency', $branch->currency) }}"
                               class="input input-bordered font-mono @error('currency') input-error @enderror"
                               placeholder="VND" maxlength="3"/>
                        @error('currency')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày khai trương</span></label>
                        <input type="date" name="opened_at"
                               value="{{ old('opened_at', $branch->opened_at?->format('Y-m-d')) }}"
                               class="input input-bordered @error('opened_at') input-error @enderror"/>
                        @error('opened_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Ngày đóng cửa</span></label>
                        <input type="date" name="closed_at"
                               value="{{ old('closed_at', $branch->closed_at?->format('Y-m-d')) }}"
                               class="input input-bordered @error('closed_at') input-error @enderror"/>
                        @error('closed_at')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

            </div>
        </div>

        {{-- ── Sidebar: Actions + Meta ─────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-3">
                    <h3 class="font-semibold text-sm">Thao tác</h3>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('backend.branches.show', $branch) }}" class="btn btn-ghost w-full">Hủy</a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-2 text-xs text-base-content/60">
                    <h3 class="font-semibold text-sm text-base-content">Thông tin</h3>
                    <div class="flex justify-between">
                        <span>Mã:</span>
                        <span class="font-mono font-medium text-base-content">{{ $branch->code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cấp:</span>
                        <span class="font-medium text-base-content">{{ $branch->depth }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tạo:</span>
                        <span>{{ $branch->created_at?->format('d/m/Y') }}</span>
                    </div>
                    @if($branch->createdBy)
                    <div class="flex justify-between">
                        <span>Người tạo:</span>
                        <span>{{ $branch->createdBy->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/Branch/resources/assets/js/branch.js',
    ], 'build/backend')
@endpush

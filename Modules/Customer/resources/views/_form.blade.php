@extends('layouts.backend')
@section('title', isset($customer) ? 'Sửa khách hàng' : 'Thêm khách hàng mới')

@section('content')
<div x-data="{
    type: {{ old('customer_type', $customer->customer_type->value ?? 1) }},
    tab: 'basic',
    tabFields: {
        basic:    ['customer_type', 'first_name', 'last_name', 'company_name'],
        contact:  ['primary_email', 'primary_phone'],
        classify: ['tag_ids', 'lifecycle_stage'],
    },
    errs: {{ Js::from($errors->keys()) }},
    errCount(t) { return this.tabFields[t].filter(f => this.errs.includes(f)).length; },
    init() {
        const order = ['basic', 'contact', 'classify'];
        for (const t of order) { if (this.errCount(t) > 0) { this.tab = t; break; } }
    }
}">

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">
            {{ isset($customer) ? 'Sửa: '.$customer->display_name : 'Thêm khách hàng mới' }}
        </h1>
        <p class="text-sm text-base-content/50 mt-0.5">
            {{ isset($customer) ? 'Cập nhật thông tin khách hàng' : 'Tạo hồ sơ khách hàng mới trong hệ thống' }}
        </p>
    </div>
    <a href="{{ isset($customer) ? route('customer.show', $customer) : route('customer.index') }}"
       class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST"
      action="{{ isset($customer) ? route('customer.update', $customer) : route('customer.store') }}"
      novalidate>
    @csrf
    @if(isset($customer)) @method('PUT') @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_268px] gap-6 items-start">

        {{-- ── Main card ─────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">

            {{-- Tab nav --}}
            <div class="border-b border-base-200 px-6">
                <nav class="flex -mb-px" role="tablist">

                    @php $navTabs = [
                        ['id'=>'basic',    'label'=>'Thông tin cơ bản', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['id'=>'contact',  'label'=>'Liên hệ & Địa chỉ', 'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['id'=>'classify', 'label'=>'Phân loại', 'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                    ]; @endphp

                    @foreach($navTabs as $nt)
                    <button type="button" role="tab"
                            :aria-selected="tab === '{{ $nt['id'] }}'"
                            @click="tab = '{{ $nt['id'] }}'"
                            class="flex items-center gap-1.5 px-1 py-4 mr-6 text-sm font-medium border-b-2 transition-colors"
                            :class="tab === '{{ $nt['id'] }}'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-base-content/50 hover:text-base-content hover:border-base-content/20'">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $nt['icon'] }}"/>
                        </svg>
                        {{ $nt['label'] }}
                        <span x-show="errCount('{{ $nt['id'] }}') > 0" x-text="errCount('{{ $nt['id'] }}')"
                              class="badge badge-error badge-xs"></span>
                    </button>
                    @endforeach

                </nav>
            </div>

            {{-- Tab panels --}}
            <div class="p-6">

                {{-- ── Panel: Thông tin cơ bản ─────────────────────── --}}
                <div x-show="tab === 'basic'" class="space-y-4">

                    {{-- Loại khách hàng --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Loại khách hàng <span class="text-error">*</span></span>
                        </label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="customer_type" value="1" class="radio radio-primary radio-sm"
                                       x-model.number="type"
                                       {{ old('customer_type', $customer->customer_type->value ?? 1) == 1 ? 'checked' : '' }}>
                                <span class="text-sm">Cá nhân</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="customer_type" value="2" class="radio radio-primary radio-sm"
                                       x-model.number="type"
                                       {{ old('customer_type', $customer->customer_type->value ?? 1) == 2 ? 'checked' : '' }}>
                                <span class="text-sm">Doanh nghiệp</span>
                            </label>
                        </div>
                        @error('customer_type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Individual fields --}}
                    <div x-show="type === 1" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Họ <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="first_name"
                                       value="{{ old('first_name', $customer->first_name ?? '') }}"
                                       class="input input-bordered input-sm w-full @error('first_name') input-error @enderror"
                                       placeholder="VD: Nguyễn">
                                @error('first_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Tên <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="last_name"
                                       value="{{ old('last_name', $customer->last_name ?? '') }}"
                                       class="input input-bordered input-sm w-full @error('last_name') input-error @enderror"
                                       placeholder="VD: Văn A">
                                @error('last_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Giới tính</span>
                                </label>
                                <select name="gender" class="select select-bordered select-sm w-full">
                                    <option value="">— Không chọn —</option>
                                    <option value="M" {{ old('gender', $customer->gender ?? '') === 'M' ? 'selected' : '' }}>Nam</option>
                                    <option value="F" {{ old('gender', $customer->gender ?? '') === 'F' ? 'selected' : '' }}>Nữ</option>
                                    <option value="O" {{ old('gender', $customer->gender ?? '') === 'O' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Ngày sinh</span>
                                </label>
                                <input type="text" name="date_of_birth" id="customer-dob"
                                       value="{{ old('date_of_birth', isset($customer) && $customer->date_of_birth ? $customer->date_of_birth->format('d/m/Y') : '') }}"
                                       class="input input-bordered input-sm w-full @error('date_of_birth') input-error @enderror"
                                       placeholder="DD/MM/YYYY" readonly>
                                @error('date_of_birth')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Business fields --}}
                    <div x-show="type === 2" class="space-y-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Tên doanh nghiệp <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="company_name"
                                   value="{{ old('company_name', $customer->company_name ?? '') }}"
                                   class="input input-bordered input-sm w-full @error('company_name') input-error @enderror"
                                   placeholder="VD: Công ty TNHH ABC">
                            @error('company_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Mã số thuế</span>
                                </label>
                                <input type="text" name="tax_code"
                                       value="{{ old('tax_code', $customer->tax_code ?? '') }}"
                                       class="input input-bordered input-sm w-full @error('tax_code') input-error @enderror"
                                       placeholder="VD: 0123456789">
                                @error('tax_code')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Quy mô công ty</span>
                                </label>
                                <select name="company_size" class="select select-bordered select-sm w-full">
                                    <option value="">— Chọn quy mô —</option>
                                    @foreach($sizes as $size)
                                    <option value="{{ $size['value'] }}"
                                            {{ old('company_size', $customer->company_size->value ?? '') == $size['value'] ? 'selected' : '' }}>
                                        {{ $size['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Ngành nghề</span>
                                </label>
                                <input type="text" name="industry"
                                       value="{{ old('industry', $customer->industry ?? '') }}"
                                       class="input input-bordered input-sm w-full"
                                       placeholder="VD: Công nghệ thông tin">
                            </div>
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium">Người đại diện</span>
                                </label>
                                <input type="text" name="representative_name"
                                       value="{{ old('representative_name', $customer->representative_name ?? '') }}"
                                       class="input input-bordered input-sm w-full"
                                       placeholder="VD: Nguyễn Văn B">
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Chức danh người đại diện</span>
                            </label>
                            <input type="text" name="representative_title"
                                   value="{{ old('representative_title', $customer->representative_title ?? '') }}"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="VD: Giám đốc điều hành">
                        </div>
                    </div>

                    {{-- Custom meta fields --}}
                    @if($fieldDefs->count())
                    <div class="divider my-1 text-xs text-base-content/30">Trường tùy chỉnh</div>
                    @foreach($fieldDefs as $def)
                    @if($def->applies_to === 0 || true){{-- applies_to filtered client-side via x-show --}}
                    @php
                        $metaVal = isset($customer) ? $customer->meta->firstWhere('definition_id', $def->id)?->getValue() : null;
                        $oldKey  = 'meta.'.$def->field_key;
                    @endphp
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">{{ $def->label }}{{ $def->is_required ? ' *' : '' }}</span>
                        </label>
                        @if($def->value_type->value === 4)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="meta[{{ $def->field_key }}]" value="1"
                                   class="checkbox checkbox-sm"
                                   {{ old($oldKey, $metaVal) ? 'checked' : '' }}>
                            <span class="text-sm text-base-content/60">{{ $def->label }}</span>
                        </label>
                        @elseif($def->value_type->value === 5)
                        <input type="text" name="meta[{{ $def->field_key }}]"
                               value="{{ old($oldKey, $metaVal ? \Carbon\Carbon::parse($metaVal)->format('d/m/Y') : '') }}"
                               class="input input-bordered input-sm w-full flatpickr-date"
                               placeholder="DD/MM/YYYY" readonly>
                        @elseif($def->value_type->value === 2 || $def->value_type->value === 3)
                        <input type="number" name="meta[{{ $def->field_key }}]"
                               value="{{ old($oldKey, $metaVal) }}"
                               step="{{ $def->value_type->value === 3 ? '0.01' : '1' }}"
                               class="input input-bordered input-sm w-full">
                        @else
                        <input type="text" name="meta[{{ $def->field_key }}]"
                               value="{{ old($oldKey, $metaVal) }}"
                               class="input input-bordered input-sm w-full">
                        @endif
                    </div>
                    @endif
                    @endforeach
                    @endif

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="tab = 'contact'" class="btn btn-ghost btn-sm gap-1.5">
                            Tiếp theo: Liên hệ
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- ── Panel: Liên hệ & Địa chỉ ────────────────────── --}}
                <div x-show="tab === 'contact'" class="space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Email chính</span>
                            </label>
                            <input type="email" name="primary_email"
                                   value="{{ old('primary_email', $customer->primary_email ?? '') }}"
                                   class="input input-bordered input-sm w-full @error('primary_email') input-error @enderror"
                                   placeholder="email@company.com">
                            @error('primary_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Email phụ</span>
                            </label>
                            <input type="email" name="secondary_email"
                                   value="{{ old('secondary_email', $customer->secondary_email ?? '') }}"
                                   class="input input-bordered input-sm w-full @error('secondary_email') input-error @enderror"
                                   placeholder="alt@company.com">
                            @error('secondary_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Điện thoại chính</span>
                            </label>
                            <input type="text" name="primary_phone"
                                   value="{{ old('primary_phone', $customer->primary_phone ?? '') }}"
                                   class="input input-bordered input-sm w-full @error('primary_phone') input-error @enderror"
                                   placeholder="0901 234 567">
                            @error('primary_phone')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Điện thoại phụ</span>
                            </label>
                            <input type="text" name="secondary_phone"
                                   value="{{ old('secondary_phone', $customer->secondary_phone ?? '') }}"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="0901 234 568">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Website</span>
                        </label>
                        <input type="url" name="website"
                               value="{{ old('website', $customer->website ?? '') }}"
                               class="input input-bordered input-sm w-full @error('website') input-error @enderror"
                               placeholder="https://company.vn">
                        @error('website')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="divider my-1 text-xs text-base-content/30">Địa chỉ</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Tỉnh / Thành phố</span>
                            </label>
                            <select id="ts-province" name="province_code"
                                    class="select select-bordered select-sm w-full @error('province_code') select-error @enderror"
                                    data-selected-province="{{ old('province_code', $customer->province_code ?? '') }}">
                                <option value="">Chọn tỉnh / thành phố...</option>
                                @foreach($provinces ?? [] as $p)
                                <option value="{{ $p->province_code }}"
                                        {{ old('province_code', $customer->province_code ?? '') === $p->province_code ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="province_name" id="customer-province-name"
                                   value="{{ old('province_name', $customer->province_name ?? '') }}">
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Phường / Xã</span>
                            </label>
                            <select id="ts-ward" name="ward_code"
                                    class="select select-bordered select-sm w-full @error('ward_code') select-error @enderror"
                                    data-ward-init="{{ old('ward_code', $customer->ward_code ?? '') }}"
                                    data-ward-name-init="{{ old('ward_name', $customer->ward_name ?? '') }}"
                                    {{ !old('province_code', $customer->province_code ?? null) ? 'disabled' : '' }}>
                                <option value="">Chọn phường / xã...</option>
                            </select>
                            <input type="hidden" name="ward_name" id="customer-ward-name"
                                   value="{{ old('ward_name', $customer->ward_name ?? '') }}">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Địa chỉ cụ thể</span>
                        </label>
                        <input type="text" name="address_line"
                               value="{{ old('address_line', $customer->address_line ?? '') }}"
                               class="input input-bordered input-sm w-full"
                               placeholder="VD: 123 Nguyễn Trãi, Phường Bến Thành">
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="tab = 'basic'" class="btn btn-ghost btn-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Thông tin cơ bản
                        </button>
                        <button type="button" @click="tab = 'classify'" class="btn btn-ghost btn-sm gap-1.5">
                            Tiếp theo: Phân loại
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </div>

                {{-- ── Panel: Phân loại ─────────────────────────────── --}}
                <div x-show="tab === 'classify'" class="space-y-4">

                    @if($tags->count())
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tags</span>
                        </label>
                        <select id="ts-tag-ids" name="tag_ids[]" multiple
                                class="select select-bordered select-sm w-full @error('tag_ids') select-error @enderror"
                                data-ts-placeholder="— Chọn tags —">
                            @foreach($tags as $tag)
                            <option value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tag_ids', isset($customer) ? $customer->tags->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('tag_ids')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    @endif

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Ghi chú nội bộ</span>
                        </label>
                        <textarea name="notes" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full"
                                  placeholder="Ghi chú riêng, không hiển thị cho khách hàng...">{{ old('notes', $customer->notes ?? '') }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="tab = 'contact'" class="btn btn-ghost btn-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Liên hệ & Địa chỉ
                        </button>
                        <span class="text-xs text-base-content/40">Nhấn <strong>Lưu</strong> ở bên phải khi xong</span>
                    </div>

                </div>

            </div>{{-- /tab panels --}}
        </div>{{-- /main card --}}

        {{-- ── Sidebar card ──────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">

                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">CRM</p>

                    {{-- Lifecycle stage --}}
                    <div class="form-control mb-3">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Tình trạng <span class="text-error">*</span></span>
                        </label>
                        <select name="lifecycle_stage"
                                class="select select-bordered select-sm w-full @error('lifecycle_stage') select-error @enderror">
                            @foreach($stages as $stage)
                            <option value="{{ $stage['value'] }}"
                                    {{ old('lifecycle_stage', $customer->lifecycle_stage->value ?? 1) == $stage['value'] ? 'selected' : '' }}>
                                {{ $stage['label'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('lifecycle_stage')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Source --}}
                    <div class="form-control mb-3">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Nguồn</span>
                        </label>
                        <select name="source_id"
                                class="select select-bordered select-sm w-full">
                            <option value="">— Chọn nguồn —</option>
                            @foreach($sources as $source)
                            <option value="{{ $source->id }}"
                                    {{ old('source_id', $customer->source_id ?? '') == $source->id ? 'selected' : '' }}>
                                {{ $source->label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Assigned to --}}
                    <div class="form-control mb-3">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs font-medium">Người phụ trách</span>
                        </label>
                        <select name="assigned_to" id="customer-assigned"
                                class="select select-bordered select-sm w-full">
                            <option value="">— Chưa phân công —</option>
                        </select>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="flex gap-2">
                        <a href="{{ isset($customer) ? route('customer.show', $customer) : route('customer.index') }}"
                           class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isset($customer) ? 'Cập nhật' : 'Tạo khách hàng' }}
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
</div>{{-- /x-data --}}
@endsection

@push('scripts')
@vite([
    'resources/js/modules/tom-select.js',
    'resources/js/modules/flatpickr.js',
], 'build/backend')
@endpush

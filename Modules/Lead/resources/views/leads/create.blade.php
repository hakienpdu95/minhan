@extends('layouts.backend')
@section('title', 'Thêm cơ hội mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Thêm cơ hội mới</h1>
    <a href="{{ route('lead.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('lead.store') }}" id="lead-wizard-form" novalidate>
    @csrf

    <div x-data="leadWizard()" x-init="init()">

        {{-- ── Step indicator ────────────────────────────────────────── --}}
        <div class="flex items-center gap-0 mb-8 max-w-2xl mx-auto">

            <template x-for="(label, idx) in steps" :key="idx">
                <div class="flex items-center flex-1 last:flex-none">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                             :class="{
                                'bg-primary text-primary-content': currentStep === idx + 1,
                                'bg-success text-success-content': currentStep > idx + 1,
                                'bg-base-300 text-base-content/50': currentStep < idx + 1
                             }">
                            <template x-if="currentStep > idx + 1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="currentStep <= idx + 1">
                                <span x-text="idx + 1"></span>
                            </template>
                        </div>
                        <span class="text-xs mt-1 whitespace-nowrap"
                              :class="currentStep === idx + 1 ? 'text-primary font-semibold' : 'text-base-content/50'"
                              x-text="label"></span>
                    </div>
                    <template x-if="idx < steps.length - 1">
                        <div class="flex-1 h-0.5 mx-2 mb-4 transition-colors"
                             :class="currentStep > idx + 1 ? 'bg-success' : 'bg-base-300'"></div>
                    </template>
                </div>
            </template>

        </div>

        {{-- ════════════════════════════════════════════════════════════
             BƯỚC 1 — Thông tin khách hàng
        ══════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 1" x-transition.opacity>
            <div class="card bg-base-100 shadow-sm border border-base-200 max-w-3xl mx-auto">
                <div class="card-body space-y-4">

                    <h2 class="text-base font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Thông tin khách hàng
                    </h2>

                    {{-- Họ tên --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="contact_name"
                               value="{{ old('contact_name') }}"
                               id="wz-contact-name"
                               class="input input-bordered input-sm @error('contact_name') input-error @enderror"
                               placeholder="VD: Nguyễn Văn A">
                        @error('contact_name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Phone + Phone Alt --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Điện thoại chính</span>
                            </label>
                            <input type="text" name="contact_phone"
                                   value="{{ old('contact_phone') }}"
                                   class="input input-bordered input-sm @error('contact_phone') input-error @enderror"
                                   placeholder="0901 234 567">
                            @error('contact_phone')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Điện thoại phụ</span>
                            </label>
                            <input type="text" name="contact_phone_alt"
                                   value="{{ old('contact_phone_alt') }}"
                                   class="input input-bordered input-sm"
                                   placeholder="0901 234 568">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input type="email" name="contact_email"
                               value="{{ old('contact_email') }}"
                               class="input input-bordered input-sm @error('contact_email') input-error @enderror"
                               placeholder="email@company.com">
                        @error('contact_email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Công ty + Chức danh --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Công ty</span>
                            </label>
                            <input type="text" name="contact_company"
                                   value="{{ old('contact_company') }}"
                                   class="input input-bordered input-sm"
                                   placeholder="Công ty TNHH ABC">
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Chức danh</span>
                            </label>
                            <input type="text" name="contact_job_title"
                                   value="{{ old('contact_job_title') }}"
                                   class="input input-bordered input-sm"
                                   placeholder="Giám đốc, Trưởng phòng...">
                        </div>
                    </div>

                    {{-- Website --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Website</span>
                        </label>
                        <input type="url" name="contact_website"
                               value="{{ old('contact_website') }}"
                               class="input input-bordered input-sm @error('contact_website') input-error @enderror"
                               placeholder="https://company.vn">
                        @error('contact_website')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- ── Địa chỉ ─────────────────────────────────────────── --}}
                    <div class="divider my-1 text-xs text-base-content/40">Địa chỉ</div>

                    {{-- Tỉnh / Thành + Phường / Xã --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Tỉnh / Thành phố</span>
                            </label>
                            <select id="wz-province" name="province_code">
                                <option value=""></option>
                                @foreach ($provinces as $p)
                                <option value="{{ $p->province_code }}"
                                        data-name="{{ $p->name }}"
                                        {{ old('province_code') === $p->province_code ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="province_name" id="wz-province-name"
                                   value="{{ old('province_name') }}">
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Phường / Xã</span>
                            </label>
                            <select id="wz-ward" name="ward_code">
                                <option value=""></option>
                            </select>
                            <input type="hidden" name="ward_name" id="wz-ward-name"
                                   value="{{ old('ward_name') }}">
                        </div>
                    </div>

                    {{-- Địa chỉ cụ thể --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Địa chỉ cụ thể</span>
                        </label>
                        <input type="text" name="contact_address"
                               value="{{ old('contact_address') }}"
                               class="input input-bordered input-sm"
                               placeholder="Số nhà, đường, phường/xã...">
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             BƯỚC 2 — Chi tiết cơ hội
        ══════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 2" x-transition.opacity>
            <div class="card bg-base-100 shadow-sm border border-base-200 max-w-3xl mx-auto">
                <div class="card-body space-y-4">

                    <h2 class="text-base font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Chi tiết cơ hội
                    </h2>

                    {{-- Tiêu đề cơ hội --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tiêu đề cơ hội</span>
                        </label>
                        <input type="text" name="title"
                               value="{{ old('title') }}"
                               class="input input-bordered input-sm"
                               placeholder="VD: Tư vấn giải pháp ERP — ABC Corp">
                    </div>

                    {{-- Tình trạng + Nguồn --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Tình trạng <span class="text-error">*</span></span>
                            </label>
                            <select name="stage_id" id="wz-stage"
                                    class="select select-bordered select-sm @error('stage_id') select-error @enderror">
                                <option value="">— Chọn tình trạng —</option>
                                @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->label }}{{ $stage->probability ? ' ('.$stage->probability.'%)' : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('stage_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Nguồn</span>
                            </label>
                            <select name="source_id" id="wz-source"
                                    class="select select-bordered select-sm">
                                <option value="">— Chọn nguồn —</option>
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id') == $source->id ? 'selected' : '' }}>
                                    {{ $source->label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Chi tiết nguồn --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Chi tiết nguồn</span>
                        </label>
                        <input type="text" name="source_detail"
                               value="{{ old('source_detail') }}"
                               class="input input-bordered input-sm"
                               placeholder="VD: Giới thiệu bởi anh Nguyễn...">
                    </div>

                    {{-- Giá trị + Currency --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Giá trị dự kiến</span>
                            </label>
                            <div class="join">
                                <input type="number" name="expected_value"
                                       value="{{ old('expected_value') }}"
                                       min="0" step="1000"
                                       class="input input-bordered input-sm join-item flex-1"
                                       placeholder="0">
                                <select name="currency" class="select select-bordered select-sm join-item w-24">
                                    <option value="VND" {{ old('currency', 'VND') === 'VND' ? 'selected' : '' }}>VND</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text font-medium">Ngày chốt dự kiến</span>
                            </label>
                            <input type="date" name="expected_close_date"
                                   value="{{ old('expected_close_date') }}"
                                   class="input input-bordered input-sm">
                        </div>
                    </div>

                    {{-- Người phụ trách --}}
                    @can('assign', \Modules\Lead\Models\Lead::class)
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Người phụ trách</span>
                        </label>
                        <select name="assigned_to" id="wz-assigned"
                                class="select select-bordered select-sm"
                                data-assignable-url="{{ route('api.api.lead.assignable-users') }}">
                            <option value="">— Chưa phân công —</option>
                        </select>
                    </div>
                    @endcan

                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             BƯỚC 3 — Tags & Mô tả
        ══════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 3" x-transition.opacity>
            <div class="card bg-base-100 shadow-sm border border-base-200 max-w-3xl mx-auto">
                <div class="card-body space-y-5">

                    <h2 class="text-base font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Tags & Mô tả
                    </h2>

                    {{-- Tags --}}
                    @if($tags->count())
                    <div class="form-control">
                        <label class="label py-0 pb-2">
                            <span class="label-text font-medium">Tags</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                            @php $color = $tag->color ?? '#6b7280'; @endphp
                            <label class="cursor-pointer">
                                <input type="checkbox" name="tag_ids[]"
                                       value="{{ $tag->id }}"
                                       class="sr-only wz-tag-cb"
                                       data-color="{{ $color }}"
                                       {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}>
                                <span class="badge badge-outline transition-all select-none px-3 py-3 text-xs"
                                      data-color="{{ $color }}">
                                    {{ $tag->name }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Mô tả --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Mô tả / Ghi chú</span>
                        </label>
                        <textarea name="description" rows="4"
                                  class="textarea textarea-bordered textarea-sm w-full"
                                  placeholder="Mô tả ngắn về cơ hội này...">{{ old('description') }}</textarea>
                    </div>

                    {{-- Tóm tắt review --}}
                    <div class="bg-base-200/60 rounded-lg p-4 space-y-2 text-sm">
                        <p class="font-semibold text-base-content/70 mb-2">Xác nhận thông tin</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                            <span class="text-base-content/50">Khách hàng</span>
                            <span class="font-medium" id="sum-name">—</span>
                            <span class="text-base-content/50">Điện thoại</span>
                            <span id="sum-phone">—</span>
                            <span class="text-base-content/50">Email</span>
                            <span id="sum-email">—</span>
                            <span class="text-base-content/50">Công ty</span>
                            <span id="sum-company">—</span>
                            <span class="text-base-content/50">Tình trạng</span>
                            <span id="sum-stage">—</span>
                            <span class="text-base-content/50">Nguồn</span>
                            <span id="sum-source">—</span>
                            <span class="text-base-content/50">Giá trị</span>
                            <span id="sum-value">—</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Navigation buttons ──────────────────────────────────── --}}
        <div class="flex justify-between items-center mt-6 max-w-3xl mx-auto">
            <button type="button"
                    x-show="currentStep > 1"
                    @click="prevStep()"
                    class="btn btn-ghost btn-sm">
                ← Quay lại
            </button>
            <div x-show="currentStep === 1"></div>

            <div class="flex gap-2">
                <button type="button"
                        x-show="currentStep < totalSteps"
                        @click="nextStep()"
                        class="btn btn-primary btn-sm">
                    Tiếp theo →
                </button>
                <button type="submit"
                        x-show="currentStep === totalSteps"
                        class="btn btn-primary btn-sm">
                    Tạo cơ hội
                </button>
            </div>
        </div>

    </div>{{-- /x-data --}}
</form>
@endsection

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Province / Ward cascade với name capture ─────────────────────
    const provEl     = document.getElementById('wz-province');
    const wardEl     = document.getElementById('wz-ward');
    const provNameEl = document.getElementById('wz-province-name');
    const wardNameEl = document.getElementById('wz-ward-name');

    let pendingWard = @json(old('ward_code', ''));
    let pendingWardName = @json(old('ward_name', ''));

    const wardTs = new window.TomSelect(wardEl, {
        placeholder: 'Chọn tỉnh / TP trước',
        create: false,
        onChange(val) {
            const item = wardTs.options[val];
            wardNameEl.value = item ? item.text : '';
        },
    });
    wardTs.disable();

    const provTs = new window.TomSelect(provEl, {
        placeholder: 'Tìm tỉnh / thành phố...',
        create: false,
        onChange: async function (code) {
            provNameEl.value = code ? (provTs.options[code]?.text ?? '') : '';
            wardNameEl.value = '';
            wardTs.clear(true);
            wardTs.clearOptions();
            wardTs.disable();

            if (!code) return;

            wardTs.settings.placeholder = 'Đang tải...';
            wardTs.control_input.placeholder = 'Đang tải...';

            try {
                const res   = await fetch('/api/provinces/' + code + '/wards');
                const wards = await res.json();
                wards.forEach(w => wardTs.addOption({ value: w.ward_code, text: w.name }));
                wardTs.settings.placeholder = 'Tìm phường / xã...';
                wardTs.control_input.placeholder = 'Tìm phường / xã...';
                wardTs.enable();

                if (pendingWard) {
                    wardTs.setValue(pendingWard, true);
                    if (pendingWardName) wardNameEl.value = pendingWardName;
                    pendingWard = null;
                    pendingWardName = null;
                }
            } catch (e) {
                wardTs.settings.placeholder = 'Lỗi tải dữ liệu';
                wardTs.enable();
            }
        },
    });

    // Pre-load if editing (old() populated)
    const initProv = @json(old('province_code', ''));
    if (initProv) provTs.setValue(initProv, true);

    // ── Stage + Source TomSelect ─────────────────────────────────────
    new window.TomSelect('#wz-stage',  { placeholder: '— Chọn tình trạng —', create: false });
    new window.TomSelect('#wz-source', { placeholder: '— Chọn nguồn —',       create: false });

    // ── Assigned user TomSelect (remote) ─────────────────────────────
    const assignedEl = document.getElementById('wz-assigned');
    if (assignedEl) {
        new window.TomSelect('#wz-assigned', {
            placeholder: '— Chưa phân công —',
            create: false,
            valueField: 'id',
            labelField: 'text',
            searchField: ['text', 'email'],
            load(query, callback) {
                const url = assignedEl.dataset.assignableUrl + '?q=' + encodeURIComponent(query);
                fetch(url).then(r => r.json()).then(callback).catch(() => callback());
            },
        });
    }

    // ── Tag checkbox toggle ──────────────────────────────────────────
    document.querySelectorAll('.wz-tag-cb').forEach(cb => {
        const span  = cb.nextElementSibling;
        const color = cb.dataset.color || '#6b7280';
        const apply = () => {
            if (cb.checked) {
                span.style.backgroundColor = color;
                span.style.borderColor     = color;
                span.style.color           = 'white';
            } else {
                span.style.backgroundColor = '';
                span.style.borderColor     = color;
                span.style.color           = color;
            }
        };
        apply();
        cb.addEventListener('change', apply);
    });

});

// ── Alpine.js wizard controller ──────────────────────────────────────────
function leadWizard() {
    return {
        currentStep: {{ $errors->any() ? 1 : 1 }},
        totalSteps: 3,
        steps: ['Khách hàng', 'Cơ hội', 'Tags & Mô tả'],

        init() {
            // If server returned validation errors, stay on step 1
            @if($errors->has('contact_name') || $errors->has('contact_email') || $errors->has('contact_phone'))
            this.currentStep = 1;
            @elseif($errors->has('stage_id') || $errors->has('expected_value'))
            this.currentStep = 2;
            @endif

            this.$watch('currentStep', (step) => {
                if (step === 3) this.updateSummary();
            });
        },

        nextStep() {
            if (this.currentStep === 1 && !this.validateStep1()) return;
            if (this.currentStep === 2 && !this.validateStep2()) return;
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) this.currentStep--;
        },

        validateStep1() {
            const name = document.getElementById('wz-contact-name');
            if (!name.value.trim()) {
                name.focus();
                name.classList.add('input-error');
                name.addEventListener('input', () => name.classList.remove('input-error'), { once: true });
                return false;
            }
            return true;
        },

        validateStep2() {
            const stage = document.getElementById('wz-stage');
            if (!stage.value) {
                stage.closest('.form-control')?.querySelector('.ts-wrapper')?.classList.add('ts-error');
                setTimeout(() => stage.closest('.form-control')?.querySelector('.ts-wrapper')?.classList.remove('ts-error'), 2000);
                return false;
            }
            return true;
        },

        updateSummary() {
            const v = (id) => document.querySelector(`[name="${id}"]`)?.value || '—';
            const sel = (id) => {
                const el = document.querySelector(`[name="${id}"]`);
                if (!el) return '—';
                const opt = el.options?.[el.selectedIndex];
                return opt?.text || '—';
            };

            const fmt = (val, cur) => {
                if (!val || val === '—') return '—';
                const n = parseFloat(val);
                if (isNaN(n)) return '—';
                return n.toLocaleString('vi-VN') + ' ' + (cur || 'VND');
            };

            document.getElementById('sum-name').textContent    = v('contact_name');
            document.getElementById('sum-phone').textContent   = v('contact_phone') !== '—' ? v('contact_phone') : '—';
            document.getElementById('sum-email').textContent   = v('contact_email') !== '—' ? v('contact_email') : '—';
            document.getElementById('sum-company').textContent = v('contact_company') !== '—' ? v('contact_company') : '—';
            document.getElementById('sum-stage').textContent   = sel('stage_id');
            document.getElementById('sum-source').textContent  = sel('source_id');
            document.getElementById('sum-value').textContent   = fmt(v('expected_value'), v('currency'));
        },
    };
}
</script>
@endpush

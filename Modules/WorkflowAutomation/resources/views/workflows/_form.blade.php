@php
$isEdit = $workflow !== null;

$initData = $isEdit ? [
    'name'           => $workflow->name,
    'description'    => $workflow->description,
    'trigger_type'   => $workflow->trigger_type,
    'trigger_params' => $workflow->triggerParams->map(fn($p) => [
        'param_key'   => $p->param_key,
        'param_value' => $p->param_value,
        'param_type'  => $p->param_type,
    ])->toArray(),
    'condition_match'=> $workflow->condition_match,
    'cooldown_type'  => $workflow->cooldown_type,
    'is_active'      => $workflow->is_active,
    'priority'       => $workflow->priority,
    'conditions'     => $workflow->conditions->map(fn($c) => [
        'field'      => $c->field,
        'operator'   => $c->operator,
        'value'      => $c->value,
        'value_type' => $c->value_type,
    ])->toArray(),
    'steps' => $workflow->steps->map(fn($s) => [
        'action_type'    => $s->action_type,
        'delay_minutes'  => $s->delay_minutes ?? 0,
        'email_to'       => $s->email_to,
        'email_subject'  => $s->email_subject,
        'email_template' => $s->email_template,
        'notif_title'    => $s->notif_title,
        'notif_body'     => $s->notif_body,
        'notif_target'   => $s->notif_target,
        'webhook_url'    => $s->webhook_url,
        'webhook_method' => $s->webhook_method,
        'webhook_secret' => $s->webhook_secret,
        'update_model'   => $s->update_model,
        'update_field'   => $s->update_field,
        'update_value'   => $s->update_value,
        'lead_source'    => $s->lead_source,
        'lead_status'    => $s->lead_status,
        'headers'        => $s->headers->map(fn($h) => [
            'header_key'   => $h->header_key,
            'header_value' => $h->header_value,
        ])->toArray(),
    ])->toArray(),
] : [
    'name' => '', 'description' => '', 'trigger_type' => '', 'trigger_params' => [],
    'condition_match' => 3, 'cooldown_type' => 0, 'is_active' => false, 'priority' => 5,
    'conditions' => [], 'steps' => [],
];

// Map server errors to wizard step (1-based)
$stepFields = [
    1 => ['name', 'description', 'priority', 'is_active'],
    2 => ['trigger_type', 'trigger_params'],
    3 => ['condition_match', 'conditions'],
    4 => ['steps'],
    5 => ['cooldown_type'],
];
$initialStep = 1;
foreach ($stepFields as $step => $fields) {
    foreach ($fields as $field) {
        if ($errors->has($field) || collect($errors->keys())->contains(fn($k) => str_starts_with($k, $field . '.'))) {
            $initialStep = $step;
            break 2;
        }
    }
}

$sd = [
    'metaUrl'      => route('backend.api.workflows.meta'),
    'initData'     => $initData,
    'initialStep'  => $initialStep,
];
@endphp

<div x-data="wfBuilderPage({{ Js::from($sd) }})">

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">
            {{ $isEdit ? 'Sửa workflow' : 'Tạo Workflow mới' }}
        </h1>
        <p class="text-sm text-base-content/50 mt-0.5">
            {{ $isEdit ? 'Cập nhật cấu hình, trigger và hành động' : 'Thiết lập trigger, điều kiện và hành động tự động' }}
        </p>
    </div>
    <a href="{{ $isEdit ? route('workflows.show', $workflow) : route('workflows.index') }}"
       class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

{{-- ── Error banner ────────────────────────────────────────────────────── --}}
@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="font-semibold">Có {{ $errors->count() }} lỗi cần kiểm tra:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<div x-show="metaError" class="alert alert-warning py-3 px-4 mb-5 text-sm" x-cloak>
    Không thể tải cấu hình workflow. Vui lòng tải lại trang.
</div>

<form id="wf-form" method="POST" action="{{ $formAction }}" novalidate data-wf-form>
    @csrf
    @if($method !== 'POST')<input type="hidden" name="_method" value="{{ $method }}">@endif

    {{-- ── Wizard step indicator ────────────────────────────────────────── --}}
    <div class="flex items-center gap-0 mb-6">
        <template x-for="(label, idx) in steps" :key="idx">
            <div class="flex items-center flex-1 last:flex-none">
                <div class="flex flex-col items-center gap-1 min-w-0">
                    <div class="wizard-step-dot" :class="stepDotClass(idx)">
                        <template x-if="currentStep > idx + 1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="currentStep <= idx + 1">
                            <span x-text="idx + 1"></span>
                        </template>
                    </div>
                    <span class="text-xs whitespace-nowrap hidden sm:block"
                          :class="currentStep === idx + 1 ? 'text-primary font-semibold' : 'text-base-content/40'"
                          x-text="label"></span>
                </div>
                <template x-if="idx < steps.length - 1">
                    <div class="wizard-step-line" :class="stepLineClass(idx)"></div>
                </template>
            </div>
        </template>
    </div>

    {{-- ── Wizard content card ──────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">

        {{-- ══════════════════════════════════════════════════════════════════
             BƯỚC 1 — Thông tin cơ bản
        ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 1" class="p-6 space-y-4" data-step-label="Thông tin cơ bản">

            <h2 class="card-title text-base mb-5">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Thông tin cơ bản
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Tên workflow --}}
                <div class="form-control sm:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên workflow <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" x-model="form.name"
                           data-req="Vui lòng nhập tên workflow"
                           class="input input-bordered input-sm w-full @error('name') input-error @enderror"
                           placeholder="VD: Gửi email khi có lead mới">
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Mô tả --}}
                <div class="form-control sm:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mô tả</span>
                        <span class="label-text-alt text-base-content/40 text-xs">Tuỳ chọn</span>
                    </label>
                    <textarea name="description" x-model="form.description" rows="2"
                              class="textarea textarea-bordered textarea-sm w-full @error('description') textarea-error @enderror"
                              placeholder="Mô tả ngắn về mục đích workflow...">{{ old('description', $workflow?->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Ưu tiên --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mức ưu tiên</span>
                        <span class="label-text-alt text-base-content/40 text-xs">1 (cao) – 10 (thấp)</span>
                    </label>
                    <input type="number" name="priority" x-model.number="form.priority" min="1" max="10"
                           class="input input-bordered input-sm w-full font-mono @error('priority') input-error @enderror"
                           placeholder="5">
                    <p class="mt-1 text-xs text-base-content/40">Workflow ưu tiên thấp hơn sẽ chạy trước.</p>
                    @error('priority')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Kích hoạt ngay --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Trạng thái</span>
                    </label>
                    <input type="hidden" name="is_active" :value="form.is_active ? '1' : '0'">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none group mt-1">
                        <input type="checkbox" x-model="form.is_active"
                               class="checkbox checkbox-sm checkbox-primary mt-0.5 shrink-0">
                        <div>
                            <span class="text-sm font-medium group-hover:text-primary transition-colors">Kích hoạt ngay</span>
                            <p class="text-xs text-base-content/50 mt-0.5">Workflow bắt đầu chạy sau khi lưu</p>
                        </div>
                    </label>
                </div>

            </div>

            {{-- Footer: Next --}}
            <div class="flex justify-end pt-2">
                <button type="button" @click="nextStep()" class="btn btn-ghost btn-sm gap-1.5">
                    Tiếp theo: Trigger
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             BƯỚC 2 — Trigger
        ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 2" class="p-6 space-y-4" data-step-label="Trigger">

            <h2 class="card-title text-base mb-5">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Sự kiện kích hoạt (Trigger)
            </h2>

            {{-- Trigger type --}}
            <div class="form-control">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Loại trigger <span class="text-error">*</span></span>
                </label>
                <div x-show="metaLoading" class="skeleton h-9 w-full rounded-lg"></div>
                <select x-show="!metaLoading" name="trigger_type" x-model="form.trigger_type"
                        @change="onTriggerChange()"
                        data-req="Vui lòng chọn loại trigger"
                        class="select select-bordered select-sm w-full @error('trigger_type') select-error @enderror">
                    <option value="">— Chọn trigger —</option>
                    <template x-for="[mod, triggers] in Object.entries(meta.trigger_groups ?? {})" :key="mod">
                        <optgroup :label="mod">
                            <template x-for="t in triggers" :key="t.type">
                                <option :value="t.type" x-text="t.label"></option>
                            </template>
                        </optgroup>
                    </template>
                </select>
                @error('trigger_type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Trigger config fields (nếu có) --}}
            <template x-if="currentTrigger?.config_fields?.length > 0">
                <div class="space-y-3 pt-1 border-t border-base-200">
                    <p class="text-xs text-base-content/50 font-medium uppercase tracking-wide pt-2">Cấu hình trigger</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="field in currentTrigger.config_fields" :key="field.key">
                            <div class="form-control">
                                <label class="label py-0 pb-1.5">
                                    <span class="label-text font-medium" x-text="field.label"></span>
                                    <span x-show="field.hint" class="label-text-alt text-base-content/40 text-xs" x-text="field.hint"></span>
                                </label>
                                <input :type="field.type === 'number' ? 'number' : 'text'"
                                       x-model="triggerConfig[field.key]"
                                       @change="syncTriggerParams()"
                                       :placeholder="field.hint ?? ''"
                                       class="input input-bordered input-sm w-full">
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Trigger context hint --}}
            <template x-if="currentTrigger">
                <div class="rounded-lg bg-base-200/60 px-4 py-3 text-xs text-base-content/60 space-y-1">
                    <p class="font-medium text-base-content/80" x-text="'Module: ' + currentTrigger.module"></p>
                    <p x-show="currentTrigger.available_fields?.length > 0">
                        Có thể dùng trong điều kiện:
                        <span class="font-mono" x-text="currentTrigger.available_fields.map(f => f.label).join(', ')"></span>
                    </p>
                </div>
            </template>

            {{-- Hidden trigger_params --}}
            <template x-for="(p, i) in form.trigger_params" :key="i">
                <div class="hidden">
                    <input type="hidden" :name="'trigger_params[' + i + '][param_key]'"   :value="p.param_key">
                    <input type="hidden" :name="'trigger_params[' + i + '][param_value]'" :value="p.param_value">
                    <input type="hidden" :name="'trigger_params[' + i + '][param_type]'"  :value="p.param_type">
                </div>
            </template>

            {{-- Footer: Prev / Next --}}
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Cơ bản
                </button>
                <button type="button" @click="nextStep()" class="btn btn-ghost btn-sm gap-1.5">
                    Tiếp theo: Điều kiện
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             BƯỚC 3 — Điều kiện
        ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 3" class="p-6 space-y-4" data-step-label="Điều kiện">

            <div class="flex items-center justify-between mb-5">
                <h2 class="card-title text-base">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Điều kiện lọc
                </h2>
                <button type="button" @click="addCondition()" :disabled="!form.trigger_type"
                        class="btn btn-ghost btn-xs gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Thêm điều kiện
                </button>
            </div>

            {{-- Khớp điều kiện --}}
            <div class="form-control max-w-xs">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Khớp điều kiện</span>
                </label>
                <select name="condition_match" x-model.number="form.condition_match"
                        class="select select-bordered select-sm w-full">
                    <option value="3">Không cần điều kiện (chạy với mọi trigger)</option>
                    <option value="1">TẤT CẢ điều kiện đúng (AND)</option>
                    <option value="2">ÍT NHẤT 1 điều kiện đúng (OR)</option>
                </select>
            </div>

            {{-- Empty state --}}
            <template x-if="form.conditions.length === 0">
                <div class="empty-state py-8">
                    <svg class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    <p class="empty-title">Không có điều kiện</p>
                    <p class="empty-desc">Workflow sẽ chạy với mọi trigger phù hợp. Thêm điều kiện để lọc.</p>
                </div>
            </template>

            {{-- Condition rows --}}
            <template x-for="(cond, ci) in form.conditions" :key="ci">
                <div class="flex flex-wrap gap-2 items-end p-3 bg-base-200/50 rounded-xl">

                    <div class="form-control flex-1 min-w-40">
                        <label class="label py-0 pb-1.5"><span class="label-text text-xs font-medium">Field</span></label>
                        <select :name="'conditions[' + ci + '][field]'" x-model="cond.field"
                                class="select select-bordered select-sm w-full">
                            <option value="">— Chọn field —</option>
                            <template x-for="f in currentTrigger?.available_fields ?? []" :key="f.key">
                                <option :value="f.key" x-text="f.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="form-control w-44">
                        <label class="label py-0 pb-1.5"><span class="label-text text-xs font-medium">Phép so sánh</span></label>
                        <select :name="'conditions[' + ci + '][operator]'" x-model="cond.operator"
                                class="select select-bordered select-sm w-full">
                            <template x-for="op in meta.operators ?? []" :key="op.value">
                                <option :value="op.value" x-text="op.label"></option>
                            </template>
                        </select>
                    </div>

                    <div class="form-control flex-1 min-w-32">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text text-xs font-medium">Giá trị</span>
                            <span x-show="cond.operator === 'in' || cond.operator === 'not_in'"
                                  class="label-text-alt text-xs text-base-content/40">Dùng | để phân cách</span>
                        </label>
                        <input :name="'conditions[' + ci + '][value]'" x-model="cond.value"
                               :placeholder="cond.operator === 'in' ? 'A|B|C' : 'Giá trị...'"
                               class="input input-bordered input-sm w-full">
                    </div>

                    <input type="hidden" :name="'conditions[' + ci + '][value_type]'" :value="cond.value_type ?? 1">

                    <button type="button" @click="removeCondition(ci)"
                            class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error mb-0.5" title="Xoá điều kiện">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                </div>
            </template>

            {{-- Footer: Prev / Next --}}
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Trigger
                </button>
                <button type="button" @click="nextStep()" class="btn btn-ghost btn-sm gap-1.5">
                    Tiếp theo: Hành động
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             BƯỚC 4 — Hành động (Steps)
        ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 4" class="p-6 space-y-4" data-step-label="Hành động">

            <div class="flex items-center justify-between mb-5">
                <h2 class="card-title text-base">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Hành động thực thi
                </h2>
                <button type="button" @click="addStep()" class="btn btn-ghost btn-xs gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Thêm bước
                </button>
            </div>

            {{-- Empty state --}}
            <template x-if="form.steps.length === 0">
                <div class="empty-state py-8">
                    <svg class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="empty-title">Chưa có bước nào</p>
                    <p class="empty-desc">Thêm ít nhất một hành động để workflow thực thi khi trigger kích hoạt.</p>
                </div>
            </template>

            {{-- Step cards --}}
            <template x-for="(step, si) in form.steps" :key="si">
                <div class="border border-base-200 rounded-xl p-4 space-y-3 bg-base-50">

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-base-content/50 uppercase tracking-wide"
                              x-text="'Bước ' + (si + 1)"></span>
                        <button type="button" @click="removeStep(si)"
                                class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error" title="Xoá bước">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        {{-- Action type --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text text-sm font-medium">Loại hành động <span class="text-error">*</span></span>
                            </label>
                            <select :name="'steps[' + si + '][action_type]'" x-model="step.action_type"
                                    @change="resetStepConfig(step)"
                                    class="select select-bordered select-sm w-full">
                                <option value="">— Chọn hành động —</option>
                                <template x-for="[mod, actions] in Object.entries(meta.action_groups ?? {})" :key="mod">
                                    <optgroup :label="mod">
                                        <template x-for="a in actions" :key="a.type">
                                            <option :value="a.type" x-text="a.label"></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        {{-- Delay --}}
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text text-sm font-medium">Delay</span>
                                <span class="label-text-alt text-xs text-base-content/40">phút, 0 = ngay lập tức</span>
                            </label>
                            <input type="number" :name="'steps[' + si + '][delay_minutes]'"
                                   x-model.number="step.delay_minutes" min="0"
                                   class="input input-bordered input-sm w-full font-mono" placeholder="0">
                        </div>

                    </div>

                    {{-- Dynamic config fields based on action type --}}
                    <template x-for="field in stepConfigFields(step.action_type)" :key="field.key">
                        <div class="form-control">
                            <label class="label py-0 pb-1.5">
                                <span class="label-text text-sm font-medium" x-text="field.label"></span>
                                <span x-show="field.hint" class="label-text-alt text-xs text-base-content/40" x-text="field.hint"></span>
                            </label>
                            <template x-if="field.type === 'select' && field.options">
                                <select :name="'steps[' + si + '][' + field.key + ']'" x-model="step[field.key]"
                                        class="select select-bordered select-sm w-full">
                                    <option value="">— Chọn —</option>
                                    <template x-for="opt in field.options" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="!(field.type === 'select' && field.options)">
                                <input :type="field.type === 'password' ? 'password' : (field.type === 'url' ? 'url' : 'text')"
                                       :name="'steps[' + si + '][' + field.key + ']'"
                                       x-model="step[field.key]"
                                       :placeholder="field.hint ?? ''"
                                       class="input input-bordered input-sm w-full">
                            </template>
                        </div>
                    </template>

                    {{-- Webhook headers (chỉ hiện khi action_type = webhook.call) --}}
                    <template x-if="step.action_type === 'webhook.call'">
                        <div class="space-y-2 pt-2 border-t border-base-200">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">HTTP Headers</p>
                                <button type="button" @click="addHeader(step)"
                                        class="btn btn-ghost btn-xs gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Header
                                </button>
                            </div>
                            <template x-for="(h, hi) in step.headers" :key="hi">
                                <div class="flex gap-2 items-center">
                                    <input type="text" :name="'steps[' + si + '][headers][' + hi + '][header_key]'"
                                           x-model="h.header_key" placeholder="Header name"
                                           class="input input-bordered input-xs flex-1 font-mono">
                                    <input type="text" :name="'steps[' + si + '][headers][' + hi + '][header_value]'"
                                           x-model="h.header_value" placeholder="Value"
                                           class="input input-bordered input-xs flex-1">
                                    <button type="button" @click="step.headers.splice(hi, 1)"
                                            class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                </div>
            </template>

            {{-- Footer: Prev / Next --}}
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Điều kiện
                </button>
                <button type="button" @click="nextStep()" class="btn btn-ghost btn-sm gap-1.5">
                    Tiếp theo: Cài đặt
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             BƯỚC 5 — Cài đặt (Cooldown)
        ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentStep === 5" class="p-6 space-y-4" data-step-label="Cài đặt">

            <h2 class="card-title text-base mb-5">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Cài đặt nâng cao
            </h2>

            {{-- Cooldown --}}
            <div class="form-control max-w-sm">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Giới hạn tần suất (Cooldown)</span>
                </label>
                <select name="cooldown_type" x-model.number="form.cooldown_type"
                        class="select select-bordered select-sm w-full @error('cooldown_type') select-error @enderror">
                    <template x-for="c in meta.cooldown_types ?? []" :key="c.value">
                        <option :value="c.value" x-text="c.label"></option>
                    </template>
                </select>
                <p class="mt-1 text-xs text-base-content/40">Tránh workflow chạy quá nhiều lần cho cùng một đối tượng.</p>
                @error('cooldown_type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Summary box --}}
            <div class="rounded-xl bg-base-200/60 p-4 space-y-2 text-sm">
                <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-2">Tóm tắt cấu hình</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                    <span class="text-base-content/50">Tên:</span>
                    <span class="font-medium truncate" x-text="form.name || '—'"></span>
                    <span class="text-base-content/50">Trigger:</span>
                    <span class="font-mono truncate" x-text="form.trigger_type || '—'"></span>
                    <span class="text-base-content/50">Điều kiện:</span>
                    <span x-text="form.conditions.length + ' điều kiện'"></span>
                    <span class="text-base-content/50">Hành động:</span>
                    <span x-text="form.steps.length + ' bước'"></span>
                    <span class="text-base-content/50">Trạng thái:</span>
                    <span :class="form.is_active ? 'text-success font-medium' : 'text-base-content/50'"
                          x-text="form.is_active ? 'Kích hoạt' : 'Tắt'"></span>
                </div>
            </div>

            {{-- Footer: Prev / Submit --}}
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn btn-ghost btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Hành động
                </button>
                <button type="button" @click="submitForm()" :disabled="loading"
                        class="btn btn-primary btn-sm gap-1.5">
                    <span x-show="loading" class="loading loading-spinner loading-xs"></span>
                    <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isEdit ? 'Lưu thay đổi' : 'Tạo Workflow' }}
                </button>
            </div>
        </div>

    </div>{{-- end card --}}

    <p class="text-center text-xs text-base-content/30 mt-3">
        <span class="text-error">*</span> là trường bắt buộc
    </p>

</form>
</div>

@push('styles')
    @vite(['Modules/WorkflowAutomation/resources/assets/sass/workflow-automation.scss'], 'build/backend')
@endpush

@push('scripts')
    @vite([
        'resources/js/modules/toastify.js',
        'Modules/WorkflowAutomation/resources/assets/js/workflow-automation.js',
    ], 'build/backend')
@endpush

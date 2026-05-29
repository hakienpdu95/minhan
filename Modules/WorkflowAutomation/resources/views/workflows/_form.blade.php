@php
$isEdit      = $workflow !== null;
$initData    = $isEdit ? [
    'name'           => $workflow->name,
    'description'    => $workflow->description,
    'trigger_type'   => $workflow->trigger_type,
    'trigger_params' => $workflow->triggerParams->map(fn($p) => ['param_key' => $p->param_key, 'param_value' => $p->param_value, 'param_type' => $p->param_type])->toArray(),
    'condition_match'=> $workflow->condition_match,
    'cooldown_type'  => $workflow->cooldown_type,
    'is_active'      => $workflow->is_active,
    'priority'       => $workflow->priority,
    'conditions'     => $workflow->conditions->map(fn($c) => ['field' => $c->field, 'operator' => $c->operator, 'value' => $c->value, 'value_type' => $c->value_type])->toArray(),
    'steps'          => $workflow->steps->map(fn($s) => [
        'action_type'    => $s->action_type,
        'delay_minutes'  => $s->delay_minutes ?? 0,
        'email_to'       => $s->email_to, 'email_subject' => $s->email_subject, 'email_template' => $s->email_template,
        'notif_title'    => $s->notif_title, 'notif_body' => $s->notif_body, 'notif_target' => $s->notif_target,
        'webhook_url'    => $s->webhook_url, 'webhook_method' => $s->webhook_method, 'webhook_secret' => $s->webhook_secret,
        'update_model'   => $s->update_model, 'update_field' => $s->update_field, 'update_value' => $s->update_value,
        'lead_source'    => $s->lead_source, 'lead_status' => $s->lead_status,
        'notif_target'   => $s->notif_target,
        'headers'        => $s->headers->map(fn($h) => ['header_key' => $h->header_key, 'header_value' => $h->header_value])->toArray(),
    ])->toArray(),
] : [
    'name' => '', 'description' => '', 'trigger_type' => '', 'trigger_params' => [],
    'condition_match' => 3, 'cooldown_type' => 0, 'is_active' => false, 'priority' => 5,
    'conditions' => [], 'steps' => [],
];
@endphp

<div x-data="wfBuilderPage" x-init="init({{ json_encode($initData) }})">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-base-content">
            {{ $isEdit ? 'Sửa Workflow' : 'Tạo Workflow mới' }}
        </h1>
        <div class="flex gap-2">
            <a href="{{ $isEdit ? route('workflows.show', $workflow) : route('workflows.index') }}"
               class="btn btn-ghost btn-sm">Hủy</a>
            <button @click="submitForm()" :disabled="loading"
                    class="btn btn-primary btn-sm gap-1.5">
                <span x-show="loading" class="loading loading-spinner loading-xs"></span>
                {{ $isEdit ? 'Lưu thay đổi' : 'Tạo Workflow' }}
            </button>
        </div>
    </div>

    {{-- ── Validation errors ────────────────────────────────────────────────── --}}
    @if($errors->any())
    <div class="alert alert-error text-sm mb-4">
        <ul class="list-disc ml-4">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div x-show="metaError" class="alert alert-warning text-sm mb-4">
        Không thể tải cấu hình workflow. Vui lòng tải lại trang.
    </div>

    <form id="wf-form" method="POST" action="{{ $formAction }}">
        @csrf
        @if($method !== 'POST')<input type="hidden" name="_method" value="{{ $method }}">@endif

        <div class="space-y-5">

            {{-- ── Phần 1: Thông tin cơ bản ─────────────────────────────────── --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">1. Thông tin cơ bản</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control md:col-span-2">
                            <label class="label py-0.5"><span class="label-text text-sm font-medium">Tên workflow <span class="text-error">*</span></span></label>
                            <input type="text" name="name" x-model="form.name" required
                                   class="input input-bordered input-sm" placeholder="VD: Gửi email khi có lead mới"/>
                        </div>
                        <div class="form-control md:col-span-2">
                            <label class="label py-0.5"><span class="label-text text-sm font-medium">Mô tả</span></label>
                            <textarea name="description" x-model="form.description" rows="2"
                                      class="textarea textarea-bordered textarea-sm" placeholder="Mô tả ngắn..."></textarea>
                        </div>
                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-sm font-medium">Ưu tiên (1–10)</span></label>
                            <input type="number" name="priority" x-model.number="form.priority" min="1" max="10"
                                   class="input input-bordered input-sm w-32"/>
                        </div>
                        <div class="form-control">
                            <label class="label py-0.5"><span class="label-text text-sm font-medium">Kích hoạt ngay</span></label>
                            <input type="hidden" name="is_active" :value="form.is_active ? '1' : '0'">
                            <input type="checkbox" x-model="form.is_active" class="toggle toggle-success toggle-sm mt-1"/>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Phần 2: Trigger ─────────────────────────────────────────── --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">2. Trigger — Sự kiện kích hoạt</h2>

                    <div class="form-control">
                        <label class="label py-0.5"><span class="label-text text-sm font-medium">Loại trigger <span class="text-error">*</span></span></label>
                        <select name="trigger_type" x-model="form.trigger_type" @change="onTriggerChange()"
                                class="select select-bordered select-sm max-w-md" :disabled="metaLoading">
                            <option value="">— Chọn trigger —</option>
                            <template x-for="[mod, triggers] in Object.entries(meta.trigger_groups ?? {})" :key="mod">
                                <optgroup :label="mod">
                                    <template x-for="t in triggers" :key="t.type">
                                        <option :value="t.type" x-text="t.label"></option>
                                    </template>
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    {{-- Trigger config fields (nếu có) --}}
                    <template x-if="currentTrigger?.config_fields?.length > 0">
                        <div class="space-y-3 pt-2 border-t border-base-200">
                            <p class="text-xs text-base-content/50 font-medium uppercase tracking-wide">Cấu hình trigger</p>
                            <template x-for="field in currentTrigger.config_fields" :key="field.key">
                                <div class="form-control max-w-sm">
                                    <label class="label py-0.5">
                                        <span class="label-text text-sm" x-text="field.label"></span>
                                        <span x-show="field.hint" class="label-text-alt text-xs text-base-content/40" x-text="field.hint"></span>
                                    </label>
                                    <input :type="field.type === 'number' ? 'number' : 'text'"
                                           x-model="triggerConfig[field.key]"
                                           @change="syncTriggerParams()"
                                           :placeholder="field.hint ?? ''"
                                           class="input input-bordered input-sm"/>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Hidden trigger_params inputs --}}
                    <template x-for="(p, i) in form.trigger_params" :key="i">
                        <div class="hidden">
                            <input type="hidden" :name="'trigger_params[' + i + '][param_key]'"   :value="p.param_key">
                            <input type="hidden" :name="'trigger_params[' + i + '][param_value]'" :value="p.param_value">
                            <input type="hidden" :name="'trigger_params[' + i + '][param_type]'"  :value="p.param_type">
                        </div>
                    </template>
                </div>
            </div>

            {{-- ── Phần 3: Conditions ──────────────────────────────────────── --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">3. Điều kiện</h2>
                        <button type="button" @click="addCondition()" :disabled="!form.trigger_type"
                                class="btn btn-ghost btn-xs gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Thêm điều kiện
                        </button>
                    </div>

                    <div class="form-control max-w-xs">
                        <label class="label py-0.5"><span class="label-text text-sm font-medium">Khớp điều kiện</span></label>
                        <select name="condition_match" x-model.number="form.condition_match" class="select select-bordered select-sm">
                            <option value="3">Không cần điều kiện</option>
                            <option value="1">TẤT CẢ điều kiện đúng (AND)</option>
                            <option value="2">ÍT NHẤT 1 điều kiện đúng (OR)</option>
                        </select>
                    </div>

                    <template x-if="form.conditions.length === 0">
                        <p class="text-sm text-base-content/40 italic">Chưa có điều kiện — workflow sẽ chạy với mọi trigger.</p>
                    </template>

                    <template x-for="(cond, ci) in form.conditions" :key="ci">
                        <div class="flex flex-wrap gap-2 items-end p-3 bg-base-200/50 rounded-lg">

                            <div class="form-control flex-1 min-w-40">
                                <label class="label py-0.5"><span class="label-text text-xs">Field</span></label>
                                <select :name="'conditions[' + ci + '][field]'" x-model="cond.field"
                                        class="select select-bordered select-sm">
                                    <option value="">— Chọn field —</option>
                                    <template x-for="f in currentTrigger?.available_fields ?? []" :key="f.key">
                                        <option :value="f.key" x-text="f.label"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="form-control w-40">
                                <label class="label py-0.5"><span class="label-text text-xs">Phép so sánh</span></label>
                                <select :name="'conditions[' + ci + '][operator]'" x-model="cond.operator"
                                        class="select select-bordered select-sm">
                                    <template x-for="op in meta.operators ?? []" :key="op.value">
                                        <option :value="op.value" x-text="op.label"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="form-control flex-1 min-w-32">
                                <label class="label py-0.5">
                                    <span class="label-text text-xs">Giá trị</span>
                                    <span x-show="cond.operator === 'in' || cond.operator === 'not_in'"
                                          class="label-text-alt text-xs text-base-content/40">Dùng | để phân cách</span>
                                </label>
                                <input :name="'conditions[' + ci + '][value]'" x-model="cond.value"
                                       :placeholder="cond.operator === 'in' ? 'A|B|C' : 'Giá trị...'"
                                       class="input input-bordered input-sm"/>
                            </div>

                            <input type="hidden" :name="'conditions[' + ci + '][value_type]'" :value="cond.value_type ?? 1">

                            <button type="button" @click="removeCondition(ci)"
                                    class="btn btn-ghost btn-xs btn-square text-error/50 hover:text-error mb-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                        </div>
                    </template>
                </div>
            </div>

            {{-- ── Phần 4: Steps ───────────────────────────────────────────── --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">4. Hành động (Steps)</h2>
                        <button type="button" @click="addStep()" class="btn btn-ghost btn-xs gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Thêm bước
                        </button>
                    </div>

                    <template x-if="form.steps.length === 0">
                        <p class="text-sm text-base-content/40 italic">Chưa có bước nào. Thêm ít nhất một hành động.</p>
                    </template>

                    <template x-for="(step, si) in form.steps" :key="si">
                        <div class="border border-base-200 rounded-xl p-4 space-y-3 bg-base-50">

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-base-content/50 uppercase tracking-wide"
                                      x-text="'Bước ' + (si + 1)"></span>
                                <button type="button" @click="removeStep(si)"
                                        class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-sm font-medium">Loại hành động <span class="text-error">*</span></span></label>
                                    <select :name="'steps[' + si + '][action_type]'" x-model="step.action_type"
                                            @change="resetStepConfig(step)"
                                            class="select select-bordered select-sm">
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

                                <div class="form-control">
                                    <label class="label py-0.5"><span class="label-text text-sm font-medium">Delay (phút)</span></label>
                                    <input type="number" :name="'steps[' + si + '][delay_minutes]'"
                                           x-model.number="step.delay_minutes" min="0"
                                           class="input input-bordered input-sm w-32"/>
                                </div>

                            </div>

                            {{-- Step-specific fields --}}
                            <template x-for="field in stepConfigFields(step.action_type)" :key="field.key">
                                <div class="form-control max-w-lg">
                                    <label class="label py-0.5">
                                        <span class="label-text text-sm" x-text="field.label"></span>
                                        <span x-show="field.hint" class="label-text-alt text-xs text-base-content/40" x-text="field.hint"></span>
                                    </label>
                                    <template x-if="field.type === 'select' && field.options">
                                        <select :name="'steps[' + si + '][' + field.key + ']'" x-model="step[field.key]"
                                                class="select select-bordered select-sm">
                                            <option value="">— Chọn —</option>
                                            <template x-for="opt in field.options" :key="opt.value">
                                                <option :value="opt.value" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="field.type !== 'select' || !field.options">
                                        <input :type="field.type === 'password' ? 'password' : (field.type === 'url' ? 'url' : 'text')"
                                               :name="'steps[' + si + '][' + field.key + ']'"
                                               x-model="step[field.key]"
                                               :placeholder="field.hint ?? ''"
                                               class="input input-bordered input-sm"/>
                                    </template>
                                </div>
                            </template>

                            {{-- Webhook headers --}}
                            <template x-if="step.action_type === 'webhook.call'">
                                <div class="space-y-2 pt-2 border-t border-base-200">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">HTTP Headers</p>
                                        <button type="button" @click="addHeader(step)"
                                                class="btn btn-ghost btn-xs gap-1">+ Header</button>
                                    </div>
                                    <template x-for="(h, hi) in step.headers" :key="hi">
                                        <div class="flex gap-2 items-center">
                                            <input type="text" :name="'steps[' + si + '][headers][' + hi + '][header_key]'"
                                                   x-model="h.header_key" placeholder="Header name"
                                                   class="input input-bordered input-xs flex-1"/>
                                            <input type="text" :name="'steps[' + si + '][headers][' + hi + '][header_value]'"
                                                   x-model="h.header_value" placeholder="Value"
                                                   class="input input-bordered input-xs flex-1"/>
                                            <button type="button" @click="step.headers.splice(hi, 1)"
                                                    class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error">✕</button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>
            </div>

            {{-- ── Phần 5: Cooldown ─────────────────────────────────────────── --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">5. Cooldown — Giới hạn tần suất</h2>
                    <div class="form-control max-w-xs">
                        <label class="label py-0.5"><span class="label-text text-sm font-medium">Chế độ cooldown</span></label>
                        <select name="cooldown_type" x-model.number="form.cooldown_type" class="select select-bordered select-sm">
                            <template x-for="c in meta.cooldown_types ?? []" :key="c.value">
                                <option :value="c.value" x-text="c.label"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

        </div>{{-- end space-y-5 --}}

        {{-- Submit (sticky bottom) --}}
        <div class="sticky bottom-4 flex justify-end gap-2 mt-6">
            <a href="{{ $isEdit ? route('workflows.show', $workflow) : route('workflows.index') }}"
               class="btn btn-ghost btn-sm">Hủy</a>
            <button type="submit" :disabled="loading" class="btn btn-primary btn-sm gap-1.5">
                <span x-show="loading" class="loading loading-spinner loading-xs"></span>
                {{ $isEdit ? 'Lưu thay đổi' : 'Tạo Workflow' }}
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {

    var META_URL = '{{ route('backend.api.workflows.meta') }}';

    Alpine.data('wfBuilderPage', function () {
        return {
            meta:       { trigger_groups: {}, action_groups: {}, operators: [], cooldown_types: [], subjects: {} },
            metaLoading: true,
            metaError:   false,
            loading:     false,
            form: {
                name: '', description: '', trigger_type: '', trigger_params: [],
                condition_match: 3, cooldown_type: 0, is_active: false, priority: 5,
                conditions: [], steps: [],
            },
            triggerConfig: {},

            get currentTrigger() {
                if (!this.form.trigger_type) return null;
                var groups = this.meta.trigger_groups ?? {};
                for (var mod in groups) {
                    var found = groups[mod].find(function (t) { return t.type === this.form.trigger_type; }, this);
                    if (found) return found;
                }
                return null;
            },

            init(initData) {
                this.form = Object.assign(this.form, initData);
                if (!this.form.steps)      this.form.steps      = [];
                if (!this.form.conditions) this.form.conditions = [];

                // Rebuild triggerConfig from trigger_params
                (this.form.trigger_params ?? []).forEach(function (p) {
                    this.triggerConfig[p.param_key] = p.param_value;
                }, this);

                // Ensure each step has headers array
                this.form.steps.forEach(function (s) {
                    if (!s.headers) s.headers = [];
                });

                this._fetchMeta();
            },

            _fetchMeta() {
                var self = this;
                self.metaLoading = true;
                fetch(META_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(function (data) {
                        self.meta        = data;
                        self.metaLoading = false;
                    })
                    .catch(function () {
                        self.metaError   = true;
                        self.metaLoading = false;
                    });
            },

            onTriggerChange() {
                this.triggerConfig = {};
                this.form.trigger_params = [];
                this.form.conditions     = [];
            },

            syncTriggerParams() {
                var params = [];
                var cfg    = this.triggerConfig;
                var fields = this.currentTrigger?.config_fields ?? [];
                fields.forEach(function (f) {
                    if (cfg[f.key] !== undefined && cfg[f.key] !== '') {
                        params.push({
                            param_key:   f.key,
                            param_value: String(cfg[f.key]),
                            param_type:  f.type === 'number' ? 2 : 1,
                        });
                    }
                });
                this.form.trigger_params = params;
            },

            addCondition() {
                this.form.conditions.push({ field: '', operator: '=', value: '', value_type: 1 });
            },

            removeCondition(i) {
                this.form.conditions.splice(i, 1);
            },

            addStep() {
                this.form.steps.push({
                    action_type: '', delay_minutes: 0, headers: [],
                    email_to: '', email_subject: '', email_template: '',
                    notif_title: '', notif_body: '', notif_target: '',
                    webhook_url: '', webhook_method: 2, webhook_secret: '',
                    update_model: '', update_field: '', update_value: '',
                    lead_source: '', lead_status: '',
                });
            },

            removeStep(i) {
                this.form.steps.splice(i, 1);
            },

            resetStepConfig(step) {
                var keys = ['email_to','email_subject','email_template','notif_title','notif_body','notif_target',
                            'webhook_url','webhook_method','webhook_secret','update_model','update_field','update_value',
                            'lead_source','lead_status'];
                keys.forEach(function (k) { step[k] = ''; });
                step.headers       = [];
                step.webhook_method = 2;
            },

            addHeader(step) {
                step.headers.push({ header_key: '', header_value: '' });
            },

            stepConfigFields(actionType) {
                var groups = this.meta.action_groups ?? {};
                for (var mod in groups) {
                    var action = groups[mod].find(function (a) { return a.type === actionType; });
                    if (action && action.config_fields) return action.config_fields;
                }
                return [];
            },

            submitForm() {
                this.syncTriggerParams();
                this.$nextTick(function () {
                    document.getElementById('wf-form').submit();
                });
            },
        };
    });
});
</script>
@endpush

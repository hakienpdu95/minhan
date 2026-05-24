{{--
    Scoring Config Wizard
    Nhận: $survey (Survey), $assessmentCode (string)
--}}
@extends('layouts.backend')

@section('title', 'Scoring Config — ' . $survey->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.edit', $survey) }}">{{ Str::limit($survey->title, 35) }}</a>
    <span class="sep">›</span>
    <span class="current">Scoring Config</span>
</nav>
@endsection

@section('content')
<div
    x-data="scoringConfig(@js($survey->id), @js(csrf_token()))"
    x-init="init()"
    :class="dirty && cfg.hasScoring ? 'pb-20' : ''"
    class="space-y-4"
>
    {{-- Flash ──────────────────────────────────────────────────────────────── --}}
    <div x-show="flash.text" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         :class="flash.type === 'error' ? 'alert-error' : 'alert-success'"
         class="alert text-sm py-2 px-4 rounded-lg" role="alert">
        <span x-text="flash.text"></span>
    </div>

    {{-- Header ─────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Cấu hình Scoring</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                {{ $survey->title }}
                <span class="badge badge-xs badge-ghost ml-1 align-middle font-mono">{{ $assessmentCode }}</span>
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <span x-show="loading" class="loading loading-spinner loading-sm text-base-content/40" title="Đang tải..."></span>
            <span x-show="saving" class="loading loading-spinner loading-sm text-primary" title="Đang lưu..."></span>
            <button @click="dryRunOpen = !dryRunOpen"
                    x-show="cfg.hasScoring"
                    class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                Chạy thử
            </button>
            <a href="{{ route('backend.surveys.edit', $survey) }}" class="btn btn-ghost btn-sm">
                ← Quay lại survey
            </a>
        </div>
    </div>

    {{-- Tab nav ─────────────────────────────────────────────────────────────── --}}
    <div class="tabs tabs-bordered overflow-x-auto flex-nowrap">
        <template x-for="tab in tabs" :key="tab.id">
            <button
                @click="!tab.disabled && (activeTab = tab.id)"
                :class="{
                    'tab-active': activeTab === tab.id,
                    'opacity-35 cursor-not-allowed': tab.disabled,
                    'text-error': tab.hasError,
                }"
                class="tab tab-bordered whitespace-nowrap gap-1.5"
                :disabled="tab.disabled"
            >
                <span x-text="tab.label"></span>
                <span x-show="tab.hasError" class="badge badge-error badge-xs">!</span>
                <span x-show="tab.valid && !tab.hasError" class="badge badge-success badge-xs">✓</span>
            </button>
        </template>
    </div>

    {{-- ══════════════════════════ TAB 1 — Khai báo cơ bản ══════════════════════ --}}
    <div x-show="activeTab === 1" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-6">

            {{-- has_scoring toggle --}}
            <div class="flex items-center justify-between p-4 rounded-xl border border-base-200 bg-base-50">
                <div>
                    <p class="font-semibold text-sm">Bật chấm điểm cho survey này</p>
                    <p class="text-xs text-base-content/50 mt-0.5">Tắt → hệ thống không chấm điểm sau khi submit</p>
                </div>
                <input type="checkbox" x-model="cfg.hasScoring" class="toggle toggle-primary toggle-lg">
            </div>

            <div x-show="!cfg.hasScoring" class="alert alert-info alert-soft text-sm py-3">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Survey này không chấm điểm. Bật toggle để cấu hình.
            </div>

            <template x-if="cfg.hasScoring">
                <div class="space-y-6">
                    {{-- aggregation_model --}}
                    <div>
                        <p class="font-semibold text-sm mb-3">Mô hình tổng hợp điểm</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <template x-for="opt in aggModelOptions" :key="opt.value">
                                <label :class="cfg.aggregationModel === opt.value ? 'ring-2 ring-primary bg-primary/5 border-primary' : 'border-base-300 hover:border-primary/50'"
                                       class="flex flex-col gap-1.5 p-4 rounded-xl border cursor-pointer transition-all">
                                    <input type="radio" x-model="cfg.aggregationModel" :value="opt.value" class="hidden">
                                    <span class="text-lg" x-text="opt.icon"></span>
                                    <span class="font-semibold text-sm" x-text="opt.label"></span>
                                    <span class="text-xs text-base-content/50 leading-relaxed" x-text="opt.desc"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- classification_type --}}
                    <div>
                        <p class="font-semibold text-sm mb-3">Kiểu phân loại kết quả</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <template x-for="opt in classTypeOptions" :key="opt.value">
                                <label :class="cfg.classificationType === opt.value ? 'ring-2 ring-primary bg-primary/5 border-primary' : 'border-base-300 hover:border-primary/50'"
                                       class="flex flex-col gap-1.5 p-3 rounded-xl border cursor-pointer transition-all">
                                    <input type="radio" x-model="cfg.classificationType" :value="opt.value" class="hidden">
                                    <span class="text-xl" x-text="opt.icon"></span>
                                    <span class="font-semibold text-sm" x-text="opt.label"></span>
                                    <span class="text-xs text-base-content/50" x-text="opt.desc"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 2 — Domains ═══════════════════════════════ --}}
    <div x-show="activeTab === 2" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-base" x-text="cfg.aggregationModel === 'sectioned' ? 'Sections & Score Range' : 'Domains & Trọng số'"></h2>
                <button @click="addDomain()" class="btn btn-primary btn-sm gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Thêm domain
                </button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-lg border border-base-200">
                <table class="table table-sm">
                    <thead class="bg-base-200/50 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="w-8"></th>
                            <th>Domain code</th>
                            <th>Label</th>
                            <th class="w-28" x-show="cfg.aggregationModel === 'weighted_domain'">Weight (0–1)</th>
                            <th class="w-28">Min score</th>
                            <th class="w-28">Max score</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(d, idx) in cfg.domains" :key="idx">
                            <tr class="hover">
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <button @click="moveDomainUp(idx)" :disabled="idx===0" class="btn btn-ghost btn-xs btn-circle opacity-40 hover:opacity-100 disabled:opacity-10 h-4 min-h-4 w-4">
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                        <button @click="moveDomainDown(idx)" :disabled="idx===cfg.domains.length-1" class="btn btn-ghost btn-xs btn-circle opacity-40 hover:opacity-100 disabled:opacity-10 h-4 min-h-4 w-4">
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td><input type="text" x-model="d.domain_code" class="input input-xs font-mono w-32" placeholder="vd: sales"></td>
                                <td><input type="text" x-model="d.label" class="input input-xs w-36" placeholder="Nhãn domain"></td>
                                <td x-show="cfg.aggregationModel === 'weighted_domain'">
                                    <input type="number" x-model.number="d.weight" step="0.01" min="0" max="1"
                                           class="input input-xs w-24"
                                           :class="Math.abs(weightSum - 1) > 0.01 ? 'input-error' : ''">
                                </td>
                                <td><input type="number" x-model.number="d.min_score" class="input input-xs w-24" :class="d.min_score >= d.max_score ? 'input-error' : ''"></td>
                                <td><input type="number" x-model.number="d.max_score" class="input input-xs w-24" :class="d.min_score >= d.max_score ? 'input-error' : ''"></td>
                                <td>
                                    <button @click="removeDomain(idx)" :disabled="cfg.domains.length <= 1"
                                            class="btn btn-ghost btn-xs btn-circle text-error/60 hover:text-error disabled:opacity-20">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cfg.domains.length === 0">
                            <td colspan="7" class="text-center text-base-content/30 text-sm py-6">Chưa có domain. Nhấn "Thêm domain".</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Weight sum bar --}}
            <div x-show="cfg.aggregationModel === 'weighted_domain'" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-base-content/60">Tổng weight</span>
                    <span :class="Math.abs(weightSum - 1) <= 0.01 ? 'text-success font-semibold' : 'text-error font-semibold'"
                          x-text="weightSum.toFixed(3) + (Math.abs(weightSum - 1) <= 0.01 ? '  ✅ Hợp lệ' : '  ❌ Phải = 1.00')"></span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all"
                         :class="Math.abs(weightSum - 1) <= 0.01 ? 'bg-success' : 'bg-error'"
                         :style="'width: ' + Math.min(weightSum * 100, 100) + '%'"></div>
                </div>
            </div>

            {{-- Min/Max formula callout --}}
            <div class="rounded-xl border border-info/30 bg-info/5 text-xs p-4 space-y-2 leading-relaxed">
                <p class="font-semibold text-info flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Công thức normalize (Min/Max dùng để làm gì?)
                </p>
                <p>Sau khi tích lũy raw scores từ các score rules, hệ thống normalize từng domain về thang 0–100:</p>
                <p class="font-mono bg-base-100 border border-base-200 px-3 py-1.5 rounded-lg inline-block">
                    normalized = (raw − min_score) / (max_score − min_score) × 100
                </p>
                <ul class="space-y-0.5 text-base-content/70 list-disc list-inside">
                    <li>Ví dụ: domain có <b>max_score = 10</b>, raw tích lũy = 7 → normalized = <b>70%</b></li>
                    <li><b>Min score</b> thường để 0. Chỉ đặt khác 0 khi domain có điểm âm (rules trừ điểm)</li>
                    <li><b>Max score</b> = tổng điểm tối đa có thể đạt nếu trả lời đúng tất cả câu trong domain</li>
                    <li x-show="cfg.aggregationModel === 'weighted_domain'"><b>Overall score</b> = Σ (normalized_domain × weight), kết quả cũng là 0–100</li>
                    <li x-show="cfg.aggregationModel === 'flat_sum'"><b>Flat sum</b>: cộng thẳng raw scores rồi normalize theo tổng max của tất cả domains</li>
                </ul>
            </div>

            {{-- Inline domain errors --}}
            <div x-show="domainErrors.length > 0" class="alert alert-error alert-soft text-sm py-2.5">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <ul class="list-disc list-inside space-y-0.5">
                    <template x-for="e in domainErrors" :key="e">
                        <li x-text="e"></li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 3 — Score Rules ═══════════════════════════ --}}
    <div x-show="activeTab === 3" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-3">

            {{-- Progress --}}
            <div class="flex items-center gap-3 text-sm">
                <span class="text-base-content/60">Đã cấu hình:</span>
                <span class="font-semibold" x-text="configuredRulesCount + '/' + fields.length + ' câu hỏi'"></span>
                <div class="flex-1 bg-base-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-primary transition-all"
                         :style="'width: ' + (fields.length ? configuredRulesCount / fields.length * 100 : 0) + '%'"></div>
                </div>
                <span class="text-xs text-base-content/40" x-text="fields.length ? Math.round(configuredRulesCount / fields.length * 100) + '%' : '0%'"></span>
            </div>

            {{-- Unconfigured hint --}}
            <div x-show="fields.length > 0 && (fields.length - configuredRulesCount) > 0"
                 class="alert alert-soft text-xs py-2.5 bg-base-200/60 border-base-300">
                <svg class="w-4 h-4 shrink-0 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    <b x-text="fields.length - configuredRulesCount"></b> câu hỏi chưa cấu hình
                    <span class="text-base-content/50">(hiển thị <b>○</b>) — mặc định bị bỏ qua, không tính vào điểm.</span>
                    Không bắt buộc phải cấu hình tất cả.
                </span>
            </div>

            {{-- Empty state --}}
            <div x-show="fields.length === 0" class="text-center py-12 text-base-content/30">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm">Survey chưa có câu hỏi</p>
                <a href="{{ route('backend.surveys.edit', $survey) }}" class="btn btn-primary btn-sm mt-3">Thêm câu hỏi</a>
            </div>

            {{-- Field list --}}
            <template x-for="(f, fIdx) in fields" :key="f.field_key">
                <div class="rounded-xl border border-base-200 overflow-hidden">
                    {{-- Row header --}}
                    <div @click="f._open = !f._open"
                         class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-base-50 transition-colors"
                         :class="f._open ? 'bg-base-50' : ''">
                        <svg :class="f._open ? 'rotate-90' : ''" class="w-3.5 h-3.5 text-base-content/30 shrink-0 transition-transform"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m9 18 6-6-6-6"/></svg>

                        <span class="w-5 h-5 shrink-0 flex items-center justify-center rounded-full text-xs font-bold"
                              :class="getRuleForField(f.field_key) ? 'bg-success/15 text-success' : 'bg-base-200 text-base-content/30'"
                              x-text="getRuleForField(f.field_key) ? '✓' : '○'"></span>

                        <span class="badge badge-xs" :class="fieldTypeBadge(f.field_type)" x-text="f.field_type_label"></span>

                        <span class="flex-1 text-sm font-medium truncate" x-text="f.label"></span>
                        <code class="text-xs font-mono text-base-content/30 shrink-0" x-text="f.field_key"></code>

                        <span x-show="getRuleForField(f.field_key)?.domain_code"
                              class="badge badge-ghost badge-xs shrink-0"
                              x-text="getRuleForField(f.field_key)?.domain_code"></span>
                        <span x-show="getRuleForField(f.field_key)?.question_scoring_type && getRuleForField(f.field_key)?.question_scoring_type !== 'none'"
                              class="badge badge-primary badge-xs shrink-0"
                              x-text="getRuleForField(f.field_key)?.question_scoring_type"></span>
                    </div>

                    {{-- Rule form --}}
                    <div x-show="f._open" class="border-t border-base-200 p-4 bg-base-50/50 space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Kiểu chấm điểm</legend>
                                <select x-model="getOrCreateRule(f.field_key).question_scoring_type"
                                        class="select select-sm w-full">
                                    <option value="none">— Không chấm —</option>
                                    <option value="boolean">Boolean (Có/Không)</option>
                                    <option value="single_choice">Single choice</option>
                                    <option value="multi_choice">Multi choice</option>
                                    <option value="numeric_range">Numeric range</option>
                                </select>
                            </fieldset>
                            <fieldset class="fieldset" x-show="cfg.aggregationModel !== 'flat_sum'">
                                <legend class="fieldset-legend text-xs">Domain</legend>
                                <select x-model="getOrCreateRule(f.field_key).domain_code" class="select select-sm w-full">
                                    <option value="">— Chọn domain —</option>
                                    <template x-for="d in cfg.domains" :key="d.domain_code">
                                        <option :value="d.domain_code" x-text="d.domain_code + (d.label ? ' – ' + d.label : '')"></option>
                                    </template>
                                </select>
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Feature code <span class="text-base-content/30">(auto)</span></legend>
                                <input type="text" x-model="getOrCreateRule(f.field_key).feature_code"
                                       :placeholder="f.field_key"
                                       class="input input-sm font-mono w-full">
                            </fieldset>
                            <fieldset class="fieldset" x-show="getOrCreateRule(f.field_key).question_scoring_type === 'boolean'">
                                <legend class="fieldset-legend text-xs flex items-center gap-1">
                                    Signal flag
                                    <span class="tooltip tooltip-right text-base-content/40 cursor-help"
                                          data-tip="Label boolean gắn vào kết quả câu hỏi này. Ví dụ: has_crm=true khi câu trả lời là Có. Dùng trong Pain Points và Persona Match để kích hoạt điều kiện phức hợp.">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                </legend>
                                <input type="text" x-model="getOrCreateRule(f.field_key).signal_flag"
                                       placeholder="vd: has_crm"
                                       class="input input-sm font-mono w-full">
                                <p class="text-xs text-base-content/40 mt-0.5">Để trống nếu không cần flag</p>
                            </fieldset>
                        </div>

                        {{-- Boolean --}}
                        <div x-show="getOrCreateRule(f.field_key).question_scoring_type === 'boolean'"
                             class="flex gap-4 items-end">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Score nếu CÓ</legend>
                                <input type="number" x-model.number="getOrCreateRule(f.field_key).score_if_true"
                                       class="input input-sm w-28"
                                       :class="getOrCreateRule(f.field_key).score_if_true > 0 ? 'text-success' : getOrCreateRule(f.field_key).score_if_true < 0 ? 'text-error' : ''">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Score nếu KHÔNG</legend>
                                <input type="number" x-model.number="getOrCreateRule(f.field_key).score_if_false"
                                       class="input input-sm w-28"
                                       :class="getOrCreateRule(f.field_key).score_if_false > 0 ? 'text-success' : getOrCreateRule(f.field_key).score_if_false < 0 ? 'text-error' : ''">
                            </fieldset>
                        </div>

                        {{-- Choice options --}}
                        <div x-show="['single_choice','multi_choice'].includes(getOrCreateRule(f.field_key).question_scoring_type)"
                             class="space-y-2">
                            <div class="overflow-x-auto rounded-lg border border-base-200">
                                <table class="table table-xs">
                                    <thead class="bg-base-200/40 text-xs">
                                        <tr>
                                            <th class="w-8"></th>
                                            <th>Option value</th>
                                            <th>Score</th>
                                            <th>Signal flag</th>
                                            <th class="w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(o, oIdx) in getOrCreateRule(f.field_key).options" :key="oIdx">
                                            <tr>
                                                <td>
                                                    <div class="flex flex-col">
                                                        <button @click="moveOptionUp(f.field_key, oIdx)" :disabled="oIdx===0" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                                        <button @click="moveOptionDown(f.field_key, oIdx)" :disabled="oIdx===getOrCreateRule(f.field_key).options.length-1" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                                    </div>
                                                </td>
                                                <td><input type="text" x-model="o.option_value" class="input input-xs font-mono w-28" placeholder="option_value"></td>
                                                <td>
                                                    <input type="number" x-model.number="o.score" class="input input-xs w-20"
                                                           :class="o.score > 0 ? 'text-success' : o.score < 0 ? 'text-error' : 'text-base-content/40'">
                                                </td>
                                                <td><input type="text" x-model="o.signal_flag" class="input input-xs font-mono w-32" placeholder="vd: has_crm"></td>
                                                <td>
                                                    <button @click="removeRuleOption(f.field_key, oIdx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex items-center gap-4 flex-wrap">
                                <button @click="addRuleOption(f.field_key, f)"
                                        class="flex items-center gap-1.5 text-xs text-primary hover:underline">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Thêm option
                                </button>
                                <div x-show="getOrCreateRule(f.field_key).question_scoring_type === 'multi_choice'"
                                     class="flex items-center gap-2 text-xs">
                                    <span class="text-base-content/50">Cap:</span>
                                    <input type="number" x-model.number="getOrCreateRule(f.field_key).min_score_cap" placeholder="min cap" class="input input-xs w-20">
                                    <span class="text-base-content/30">→</span>
                                    <input type="number" x-model.number="getOrCreateRule(f.field_key).max_score_cap" placeholder="max cap" class="input input-xs w-20">
                                </div>
                            </div>
                        </div>

                        {{-- Numeric ranges --}}
                        <div x-show="getOrCreateRule(f.field_key).question_scoring_type === 'numeric_range'" class="space-y-2">
                            <div class="overflow-x-auto rounded-lg border border-base-200">
                                <table class="table table-xs">
                                    <thead class="bg-base-200/40 text-xs">
                                        <tr>
                                            <th class="w-8"></th>
                                            <th>Min value (— = ∞)</th>
                                            <th>Max value (— = ∞)</th>
                                            <th>Score</th>
                                            <th>Signal flag</th>
                                            <th class="w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(r, rIdx) in getOrCreateRule(f.field_key).ranges" :key="rIdx">
                                            <tr>
                                                <td>
                                                    <div class="flex flex-col">
                                                        <button @click="moveRangeUp(f.field_key, rIdx)" :disabled="rIdx===0" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                                        <button @click="moveRangeDown(f.field_key, rIdx)" :disabled="rIdx===getOrCreateRule(f.field_key).ranges.length-1" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                                    </div>
                                                </td>
                                                <td><input type="number" x-model="r.min_value" placeholder="—" class="input input-xs w-24"></td>
                                                <td><input type="number" x-model="r.max_value" placeholder="—" class="input input-xs w-24"></td>
                                                <td><input type="number" x-model.number="r.score" class="input input-xs w-20" :class="r.score > 0 ? 'text-success' : r.score < 0 ? 'text-error' : ''"></td>
                                                <td><input type="text" x-model="r.signal_flag" class="input input-xs font-mono w-32" placeholder="flag"></td>
                                                <td>
                                                    <button @click="removeRuleRange(f.field_key, rIdx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <button @click="addRuleRange(f.field_key)"
                                    class="flex items-center gap-1.5 text-xs text-primary hover:underline">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Thêm khoảng giá trị
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 4 — Phân loại ═════════════════════════════ --}}
    <div x-show="activeTab === 4" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-4">
            <h2 class="font-bold text-base">Phân loại kết quả
                <span class="badge badge-ghost badge-sm ml-1" x-text="cfg.classificationType"></span>
            </h2>

            {{-- score_band --}}
            <template x-if="cfg.classificationType === 'score_band'">
                <div class="space-y-4">
                    <div class="overflow-x-auto rounded-lg border border-base-200">
                        <table class="table table-sm">
                            <thead class="bg-base-200/50 text-xs uppercase">
                                <tr>
                                    <th class="w-8"></th>
                                    <th>Label</th>
                                    <th>Band code</th>
                                    <th class="w-24">Min (0–100)</th>
                                    <th class="w-24">Max (0–100)</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(b, idx) in cfg.bands" :key="idx">
                                    <tr class="hover">
                                        <td>
                                            <div class="flex flex-col">
                                                <button @click="moveBandUp(idx)" :disabled="idx===0" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                                <button @click="moveBandDown(idx)" :disabled="idx===cfg.bands.length-1" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                            </div>
                                        </td>
                                        <td><input type="text" x-model="b.label" class="input input-xs w-44" placeholder="Nhãn hiển thị"></td>
                                        <td><input type="text" x-model="b.band_code" class="input input-xs font-mono w-44" placeholder="BAND_CODE"></td>
                                        <td><input type="number" x-model.number="b.min_score" min="0" max="100" class="input input-xs w-20"></td>
                                        <td><input type="number" x-model.number="b.max_score" min="0" max="100" class="input input-xs w-20"></td>
                                        <td>
                                            <button @click="removeBand(idx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button @click="addBand()" class="flex items-center gap-1.5 text-sm text-primary hover:underline">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm band
                    </button>
                    {{-- Visual ruler --}}
                    <div class="relative h-6 bg-base-200 rounded-full overflow-hidden" x-show="cfg.bands.length > 0">
                        <template x-for="b in cfg.bands" :key="b.band_code">
                            <div class="absolute top-0 bottom-0 flex items-center justify-center text-xs font-bold text-white"
                                 :style="'left: ' + b.min_score + '%; width: ' + (b.max_score - b.min_score) + '%; background: hsl(' + bandHue(b.band_code) + ', 65%, 55%);'"
                                 x-text="b.band_code.substring(0,3)"></div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- pass_fail --}}
            <template x-if="cfg.classificationType === 'pass_fail'">
                <div class="space-y-4">
                    {{-- Explanation --}}
                    <div class="rounded-xl border border-base-200 bg-base-50 p-4 text-xs space-y-2 leading-relaxed">
                        <p class="font-semibold text-sm">✅ Cơ chế Pass / Fail</p>
                        <p>Sau khi tính <b>overall_score</b> (0–100), hệ thống so sánh với <b>passing_score</b>:</p>
                        <div class="grid grid-cols-2 gap-2 my-1">
                            <div class="bg-success/10 border border-success/30 rounded-lg p-2 text-center">
                                <p class="font-mono text-success font-bold">overall ≥ passing_score</p>
                                <p class="text-success">→ <b>Đạt</b> (label_pass)</p>
                            </div>
                            <div class="bg-error/10 border border-error/30 rounded-lg p-2 text-center">
                                <p class="font-mono text-error font-bold">overall &lt; passing_score</p>
                                <p class="text-error">→ <b>Không đạt</b> (label_fail)</p>
                            </div>
                        </div>
                        <p class="text-base-content/50"><b>Dùng khi:</b> tuyển dụng sàng lọc, kiểm tra đầu vào, chứng chỉ đạt/không đạt. Không phân mức độ chi tiết.</p>
                    </div>

                    <div class="max-w-sm space-y-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Ngưỡng đạt (passing_score)</legend>
                            <div class="join">
                                <input type="number" x-model.number="cfg.passFailConfig.passing_score" class="input join-item w-32"
                                       min="0" max="100"
                                       :class="cfg.passFailConfig.passing_score < 0 || cfg.passFailConfig.passing_score > 100 ? 'input-error' : ''">
                                <span class="join-item btn btn-ghost no-animation text-sm">/ 100</span>
                            </div>
                            <p class="text-xs text-base-content/40 mt-1">Overall score ≥ giá trị này → Đạt</p>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-3">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Nhãn khi Đạt</legend>
                                <input type="text" x-model="cfg.passFailConfig.label_pass" class="input w-full" placeholder="Đạt yêu cầu">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Nhãn khi Không đạt</legend>
                                <input type="text" x-model="cfg.passFailConfig.label_fail" class="input w-full" placeholder="Chưa đạt yêu cầu">
                            </fieldset>
                        </div>
                    </div>
                </div>
            </template>

            {{-- persona_match --}}
            <template x-if="cfg.classificationType === 'persona_match'">
                <div class="space-y-3">
                    {{-- Explanation --}}
                    <div class="rounded-xl border border-base-200 bg-base-50 p-4 text-xs space-y-2 leading-relaxed">
                        <p class="font-semibold text-sm">👤 Cơ chế Persona Match</p>
                        <p>Hệ thống kiểm tra từng persona <b>theo thứ tự</b> — persona đầu tiên thỏa <b>tất cả</b> điều kiện (AND logic) sẽ được gán cho respondent.</p>
                        <div class="bg-base-100 border border-base-200 rounded-lg p-3 space-y-1 font-mono text-xs">
                            <p class="text-base-content/50 font-sans">Ví dụ: Persona "Nhà quản lý AI"</p>
                            <p><span class="badge badge-xs badge-primary">domain</span> leadership <span class="text-warning">≥</span> 70</p>
                            <p><span class="badge badge-xs badge-secondary">signal_flag</span> has_ai_tool <span class="text-warning">=</span> true</p>
                            <p><span class="badge badge-xs badge-ghost">overall</span> score <span class="text-warning">≥</span> 60</p>
                        </div>
                        <ul class="space-y-0.5 text-base-content/60 list-disc list-inside">
                            <li><b>target_type = domain</b>: so sánh normalized_score của domain (0–100)</li>
                            <li><b>target_type = signal_flag</b>: kiểm tra boolean flag từ câu trả lời (true/false)</li>
                            <li><b>target_type = overall</b>: so sánh overall_score tổng (0–100)</li>
                            <li>Persona <b>không khớp nào</b> → respondent không được phân loại persona</li>
                            <li><b>Dùng khi:</b> phân nhóm người dùng phức hợp (không phải đơn giản đạt/không đạt)</li>
                        </ul>
                    </div>

                    <template x-for="(p, pIdx) in cfg.personas" :key="pIdx">
                        <div class="rounded-xl border border-base-200 overflow-hidden">
                            <div @click="p._open = !p._open" class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-base-50">
                                <svg :class="p._open ? 'rotate-90':''" class="w-3.5 h-3.5 text-base-content/30 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m9 18 6-6-6-6"/></svg>
                                <span class="flex-1 font-medium text-sm" x-text="p.label || '(chưa đặt tên)'"></span>
                                <span class="badge badge-ghost badge-xs" x-text="(p.conditions?.length || 0) + ' điều kiện'"></span>
                                <button @click.stop="removePersona(pIdx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            <div x-show="p._open" class="border-t border-base-200 p-4 space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend text-xs">Persona code</legend>
                                        <input type="text" x-model="p.persona_code" class="input input-sm font-mono w-full">
                                    </fieldset>
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend text-xs">Label</legend>
                                        <input type="text" x-model="p.label" class="input input-sm w-full">
                                    </fieldset>
                                </div>
                                <div class="overflow-x-auto rounded-lg border border-base-200">
                                    <table class="table table-xs">
                                        <thead class="bg-base-200/40 text-xs">
                                            <tr><th>target_type</th><th>target_code</th><th>operator</th><th>value</th><th class="w-8"></th></tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(c, cIdx) in p.conditions" :key="cIdx">
                                                <tr>
                                                    <td>
                                                        <select x-model="c.target_type" class="select select-xs w-32">
                                                            <option value="domain">domain</option>
                                                            <option value="signal_flag">signal_flag</option>
                                                            <option value="overall">overall</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select x-show="c.target_type === 'domain'" x-model="c.target_code" class="select select-xs w-28">
                                                            <template x-for="d in cfg.domains" :key="d.domain_code"><option :value="d.domain_code" x-text="d.domain_code"></option></template>
                                                        </select>
                                                        <select x-show="c.target_type === 'signal_flag'" x-model="c.target_code" class="select select-xs w-32">
                                                            <template x-for="fl in flags" :key="fl"><option :value="fl" x-text="fl"></option></template>
                                                        </select>
                                                        <span x-show="c.target_type === 'overall'" class="text-xs text-base-content/40">—</span>
                                                    </td>
                                                    <td>
                                                        <select x-model="c.operator" class="select select-xs w-20">
                                                            <option value=">=">&gt;=</option>
                                                            <option value="<=">&lt;=</option>
                                                            <option value=">">&gt;</option>
                                                            <option value="<">&lt;</option>
                                                            <option value="=">=</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input x-show="c.target_type !== 'signal_flag'" type="number" x-model.number="c.threshold_value" class="input input-xs w-20">
                                                        <select x-show="c.target_type === 'signal_flag'" x-model="c.flag_value" class="select select-xs w-24">
                                                            <option :value="true">true</option>
                                                            <option :value="false">false</option>
                                                        </select>
                                                    </td>
                                                    <td><button @click="removePersonaCondition(pIdx, cIdx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                                <button @click="addPersonaCondition(pIdx)" class="flex items-center gap-1.5 text-xs text-primary hover:underline">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Thêm điều kiện
                                </button>
                            </div>
                        </div>
                    </template>
                    <button @click="addPersona()" class="flex items-center gap-1.5 text-sm text-primary hover:underline">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm persona
                    </button>
                </div>
            </template>

            <div x-show="cfg.classificationType === 'none'" class="alert alert-info alert-soft text-sm py-3">
                Kiểu "none" — hệ thống chỉ tính điểm, không phân loại.
            </div>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 5 — Outputs ═══════════════════════════════ --}}
    <div x-show="activeTab === 5" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-6">

            {{-- Pain Points --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-sm">Pain Point Rules</h3>
                    <button @click="addPainPoint()" class="btn btn-primary btn-xs gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm
                    </button>
                </div>
                <div class="overflow-x-auto rounded-lg border border-base-200">
                    <table class="table table-sm">
                        <thead class="bg-base-200/50 text-xs uppercase">
                            <tr><th>required_flags (AND logic)</th><th>pain_point_code</th><th>Label</th><th class="w-8"></th></tr>
                        </thead>
                        <tbody>
                            <template x-for="(pp, idx) in cfg.painPoints" :key="idx">
                                <tr class="hover">
                                    <td>
                                        <input type="text" x-model="pp.required_flags"
                                               placeholder="flag_a, !flag_b"
                                               class="input input-xs font-mono w-full">
                                        <p class="text-xs text-base-content/30 mt-0.5">Phân cách bằng dấu phẩy. Thêm ! phía trước để negate.</p>
                                    </td>
                                    <td><input type="text" x-model="pp.pain_point_code" class="input input-xs font-mono w-36" placeholder="code"></td>
                                    <td><input type="text" x-model="pp.label" class="input input-xs w-44" placeholder="Mô tả ngắn"></td>
                                    <td>
                                        <button @click="removePainPoint(idx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="cfg.painPoints.length === 0">
                                <td colspan="4" class="text-center text-base-content/30 text-sm py-4">Chưa có pain point rule</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="divider my-2"></div>

            {{-- Recommendations --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-sm">Recommendation Rules</h3>
                    <button @click="addRecommendation()" class="btn btn-primary btn-xs gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm
                    </button>
                </div>
                <div class="overflow-x-auto rounded-lg border border-base-200">
                    <table class="table table-sm">
                        <thead class="bg-base-200/50 text-xs uppercase">
                            <tr>
                                <th class="w-8"></th>
                                <th>Domain trigger</th>
                                <th class="w-24">Threshold (&lt;)</th>
                                <th>recommendation_code</th>
                                <th>Label</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(r, idx) in cfg.recommendations" :key="idx">
                                <tr class="hover">
                                    <td>
                                        <div class="flex flex-col">
                                            <button @click="moveRecUp(idx)" :disabled="idx===0" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                            <button @click="moveRecDown(idx)" :disabled="idx===cfg.recommendations.length-1" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                        </div>
                                    </td>
                                    <td>
                                        <select x-model="r.trigger_domain" class="select select-xs w-28">
                                            <option value="">— Chọn —</option>
                                            <template x-for="d in cfg.domains" :key="d.domain_code">
                                                <option :value="d.domain_code" x-text="d.domain_code"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td><input type="number" x-model.number="r.threshold_score" class="input input-xs w-20" min="0" max="100"></td>
                                    <td><input type="text" x-model="r.recommendation_code" class="input input-xs font-mono w-36" placeholder="code"></td>
                                    <td><input type="text" x-model="r.label" class="input input-xs w-44" placeholder="Mô tả"></td>
                                    <td>
                                        <button @click="removeRecommendation(idx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="cfg.recommendations.length === 0">
                                <td colspan="6" class="text-center text-base-content/30 text-sm py-4">Chưa có recommendation rule</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 6 — Roadmap ═══════════════════════════════ --}}
    <div x-show="activeTab === 6" class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6 space-y-4">
            <h2 class="font-bold text-base">Roadmap Phases theo Band</h2>

            <div x-show="cfg.bands.length === 0 && cfg.classificationType === 'score_band'"
                 class="alert alert-warning alert-soft text-sm py-3">
                Chưa có score bands. Hãy cấu hình ở Tab 4 trước.
            </div>

            {{-- Tabs con per band --}}
            <div x-show="roadmapBands.length > 0">
                <div class="tabs tabs-sm tabs-bordered mb-4">
                    <template x-for="band in roadmapBands" :key="band">
                        <button @click="activeRoadmapBand = band"
                                :class="activeRoadmapBand === band ? 'tab-active' : ''"
                                class="tab" x-text="band"></button>
                    </template>
                </div>

                <template x-for="band in roadmapBands" :key="band">
                    <div x-show="activeRoadmapBand === band" class="space-y-3">
                        <template x-for="(phase, pIdx) in getRoadmapPhases(band)" :key="pIdx">
                            <div class="rounded-xl border border-base-200 overflow-hidden">
                                <div class="flex items-center gap-2 px-4 py-2.5 bg-base-50">
                                    <div class="flex flex-col">
                                        <button @click="movePhaseUp(band, pIdx)" :disabled="pIdx===0" class="btn btn-ghost btn-xs btn-circle h-3.5 min-h-3.5 w-3.5 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                        <button @click="movePhaseDown(band, pIdx)" :disabled="pIdx===getRoadmapPhases(band).length-1" class="btn btn-ghost btn-xs btn-circle h-3.5 min-h-3.5 w-3.5 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                    </div>
                                    <span class="font-semibold text-sm flex-1" x-text="phase.title || '(Phase ' + (pIdx+1) + ')'"></span>
                                    <span class="badge badge-ghost badge-xs font-mono" x-text="phase.phase_code"></span>
                                    <button @click="phase._open = !phase._open" class="btn btn-ghost btn-xs">
                                        <span x-text="phase._open ? 'Thu gọn' : 'Mở rộng'"></span>
                                    </button>
                                    <button @click="removePhase(band, pIdx)" class="btn btn-ghost btn-xs btn-circle text-error/50 hover:text-error">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                <div x-show="phase._open" class="border-t border-base-200 p-4 space-y-3">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <fieldset class="fieldset col-span-2">
                                            <legend class="fieldset-legend text-xs">Tiêu đề phase</legend>
                                            <input type="text" x-model="phase.title" class="input input-sm w-full" placeholder="Tiêu đề phase">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="fieldset-legend text-xs">Phase code</legend>
                                            <input type="text" x-model="phase.phase_code" class="input input-sm font-mono w-full">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="fieldset-legend text-xs">Thời gian (tuần)</legend>
                                            <input type="number" x-model.number="phase.duration_weeks" class="input input-sm w-full" min="1">
                                        </fieldset>
                                    </div>
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend text-xs">Mô tả</legend>
                                        <textarea x-model="phase.description" rows="2" class="textarea textarea-sm w-full" placeholder="Mô tả ngắn về phase này..."></textarea>
                                    </fieldset>
                                    {{-- Milestones --}}
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium text-base-content/60">Milestones</p>
                                        <template x-for="(m, mIdx) in phase.milestones" :key="mIdx">
                                            <div class="flex items-center gap-2">
                                                <div class="flex flex-col shrink-0">
                                                    <button @click="moveMilestoneUp(band, pIdx, mIdx)" :disabled="mIdx===0" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                                    <button @click="moveMilestoneDown(band, pIdx, mIdx)" :disabled="mIdx===phase.milestones.length-1" class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 opacity-40 hover:opacity-100 disabled:opacity-10"><svg class="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                                </div>
                                                <span class="text-xs text-base-content/40 w-5 shrink-0 text-right tabular-nums" x-text="mIdx+1"></span>
                                                <input type="text" x-model="m.title" class="input input-xs flex-1" placeholder="Milestone title">
                                                <button @click="removeMilestone(band, pIdx, mIdx)" class="btn btn-ghost btn-xs btn-circle text-error/40 hover:text-error shrink-0">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                        <button @click="addMilestone(band, pIdx)" class="flex items-center gap-1 text-xs text-primary/70 hover:text-primary mt-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Thêm milestone
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button @click="addPhase(band)" class="flex items-center gap-1.5 text-sm text-primary hover:underline">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Thêm phase cho <span class="font-mono ml-1" x-text="band"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 7 — Review & Lưu ═════════════════════════ --}}
    <div x-show="activeTab === 7" class="space-y-4">
        {{-- Checklist --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-6 space-y-2">
                <h2 class="font-bold text-base mb-3">Checklist validation</h2>
                <template x-for="item in checklist" :key="item.label">
                    <div class="flex items-start gap-3 text-sm py-1">
                        <span class="shrink-0 mt-0.5"
                              :class="item.status === 'ok' ? 'text-success' : item.status === 'warn' ? 'text-warning' : 'text-error'"
                              x-text="item.status === 'ok' ? '✅' : item.status === 'warn' ? '⚠️' : '❌'"></span>
                        <div>
                            <span x-text="item.label"></span>
                            <span x-show="item.detail" class="text-xs text-base-content/50 ml-1" x-text="'— ' + item.detail"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Summary stats --}}
        <div class="stats shadow w-full border border-base-200">
            <div class="stat">
                <div class="stat-title text-xs">Domains</div>
                <div class="stat-value text-lg" x-text="cfg.domains.length"></div>
            </div>
            <div class="stat">
                <div class="stat-title text-xs">Rules</div>
                <div class="stat-value text-lg" x-text="configuredRulesCount"></div>
            </div>
            <div class="stat">
                <div class="stat-title text-xs">Bands</div>
                <div class="stat-value text-lg" x-text="cfg.bands.length"></div>
            </div>
            <div class="stat">
                <div class="stat-title text-xs">Pain points</div>
                <div class="stat-value text-lg" x-text="cfg.painPoints.length"></div>
            </div>
            <div class="stat">
                <div class="stat-title text-xs">Recommendations</div>
                <div class="stat-value text-lg" x-text="cfg.recommendations.length"></div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button @click="saveConfig()"
                    :disabled="saving || hasErrors"
                    class="btn btn-success gap-2">
                <span x-show="saving" class="loading loading-spinner loading-sm"></span>
                <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Lưu &amp; Kích hoạt
            </button>
            <button @click="exportJson()" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Xuất JSON config
            </button>
        </div>
    </div>

    {{-- ══════════════════════════ TAB 8 — Vị trí công việc ══════════════════════ --}}
    <div x-show="activeTab === 8" class="space-y-4">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-base">Vị trí công việc phù hợp</h2>
                        <p class="text-xs text-base-content/50 mt-0.5">Hệ thống sẽ tự động khớp kết quả với các vị trí phù hợp (Module 150C)</p>
                    </div>
                    <button @click="addJobPosition()" class="btn btn-primary btn-sm gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm vị trí
                    </button>
                </div>

                <table class="table table-sm">
                    <thead>
                        <tr class="text-xs">
                            <th>Mã vị trí</th>
                            <th>Tên vị trí</th>
                            <th>Điểm tối thiểu</th>
                            <th>Yêu cầu domain</th>
                            <th class="w-24 text-center">Kích hoạt</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(jp, idx) in cfg.jobPositions" :key="idx">
                            <tr>
                                <td class="align-top pt-3">
                                    <input x-model="jp.position_code"
                                           class="input input-xs input-bordered font-mono w-28"
                                           placeholder="vd: senior_dev" />
                                </td>
                                <td class="align-top pt-3">
                                    <input x-model="jp.title"
                                           class="input input-xs input-bordered w-44"
                                           placeholder="Tên vị trí" />
                                    <textarea x-model="jp.description"
                                              class="textarea textarea-bordered textarea-xs w-44 mt-1"
                                              rows="2"
                                              placeholder="Mô tả (tùy chọn)"></textarea>
                                </td>
                                <td class="align-top pt-3">
                                    <input x-model.number="jp.min_overall_score"
                                           type="number" min="0" max="100" step="1"
                                           class="input input-xs input-bordered w-20"
                                           placeholder="0-100" />
                                    <span class="text-xs text-base-content/40 block mt-0.5">điểm tổng</span>
                                </td>
                                <td class="align-top pt-3">
                                    <div class="space-y-1">
                                        <template x-for="(minScore, domainCode) in (jp.requirements || {})" :key="domainCode">
                                            <div class="flex items-center gap-1">
                                                <span class="font-mono text-xs bg-base-200 px-1 rounded" x-text="domainCode"></span>
                                                <span class="text-xs text-base-content/50">≥</span>
                                                <span class="text-xs font-semibold" x-text="minScore + '%'"></span>
                                                <button @click="removeRequirement(jp, domainCode)"
                                                        class="btn btn-ghost btn-xs btn-circle h-3 min-h-3 w-3 text-error opacity-60 hover:opacity-100">×</button>
                                            </div>
                                        </template>
                                        <button @click="addRequirement(jp)"
                                                class="flex items-center gap-1 text-xs text-primary hover:underline mt-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Thêm domain
                                        </button>
                                    </div>
                                </td>
                                <td class="align-top pt-3 text-center">
                                    <input type="checkbox" class="toggle toggle-sm toggle-primary" x-model="jp.is_active" />
                                </td>
                                <td class="align-top pt-3">
                                    <button @click="removeJobPosition(idx)"
                                            class="btn btn-ghost btn-xs btn-circle text-error opacity-60 hover:opacity-100">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="cfg.jobPositions.length === 0">
                            <td colspan="6" class="text-center text-sm text-base-content/30 py-8">
                                Chưa có vị trí công việc nào. Nhấn "Thêm vị trí" để bắt đầu.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="text-xs text-base-content/40">
                    <b>Cách hoạt động:</b> Sau khi tính điểm, hệ thống sẽ tự khớp với các vị trí có <code>match_score ≥ 50%</code>.
                    Yêu cầu domain: domain_code → điểm normalized tối thiểu (0-100).
                </p>
            </div>
        </div>

        <div class="flex justify-end">
            <button @click="activeTab = 7" class="btn btn-ghost btn-sm gap-1.5 text-xs">
                Sang Tab ⑦ Review để lưu
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════ Sticky save bar ══════════════════════════════ --}}
    <div x-show="dirty && cfg.hasScoring" x-cloak
         class="fixed bottom-0 left-0 right-0 z-40 bg-base-100/95 backdrop-blur border-t border-base-200 shadow-lg"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-base-content/60">
                <svg class="w-4 h-4 text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Có thay đổi chưa lưu
                <span x-show="hasErrors" class="badge badge-error badge-xs ml-1">Có lỗi — kiểm tra Tab ②</span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="activeTab = 7" x-show="activeTab !== 7"
                        class="btn btn-ghost btn-sm text-xs">
                    Xem checklist ⑦
                </button>
                <button @click="saveConfig()"
                        :disabled="saving || hasErrors"
                        class="btn btn-success btn-sm gap-2">
                    <span x-show="saving" class="loading loading-spinner loading-sm"></span>
                    <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Lưu &amp; Kích hoạt
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════ Dry-run panel ═════════════════════════════════ --}}
    <div x-show="dryRunOpen" x-cloak
         class="fixed inset-0 z-50 flex justify-end"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="absolute inset-0 bg-black/30" @click="dryRunOpen = false"></div>
        <div class="relative w-full max-w-md bg-base-100 shadow-2xl flex flex-col h-full overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-base-200 shrink-0">
                <h3 class="font-bold text-base">🧪 Dry-run — Kiểm tra config</h3>
                <button @click="dryRunOpen = false" class="btn btn-ghost btn-sm btn-circle">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto p-5 space-y-3">
                <p class="text-xs text-base-content/50">Nhập câu trả lời mẫu để kiểm tra pipeline scoring.</p>
                <template x-for="f in fields" :key="f.field_key">
                    <div x-show="dryRunRule(f) && dryRunRule(f).question_scoring_type !== 'none'" class="space-y-1">
                        <label class="text-xs font-medium" x-text="f.label + ' (' + f.field_key + ')'"></label>
                        {{-- boolean --}}
                        <div x-show="dryRunRule(f)?.question_scoring_type === 'boolean'" class="flex gap-2">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" x-model="dryRunAnswers[f.field_key]" :value="true" class="radio radio-sm radio-primary">
                                <span class="text-sm">Có</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" x-model="dryRunAnswers[f.field_key]" :value="false" class="radio radio-sm">
                                <span class="text-sm">Không</span>
                            </label>
                        </div>
                        {{-- choice --}}
                        <div x-show="['single_choice','multi_choice'].includes(dryRunRule(f)?.question_scoring_type)" class="flex flex-wrap gap-2">
                            <template x-for="o in (dryRunRule(f)?.options || [])" :key="o.option_value">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input x-show="dryRunRule(f)?.question_scoring_type === 'single_choice'"
                                           type="radio" :name="'dr_' + f.field_key"
                                           :value="o.option_value"
                                           @change="dryRunAnswers[f.field_key] = $event.target.value"
                                           class="radio radio-xs radio-primary">
                                    <input x-show="dryRunRule(f)?.question_scoring_type === 'multi_choice'"
                                           type="checkbox" :value="o.option_value"
                                           @change="toggleDryRunMulti(f.field_key, o.option_value)"
                                           class="checkbox checkbox-xs checkbox-primary">
                                    <span class="text-sm" x-text="o.option_value"></span>
                                </label>
                            </template>
                        </div>
                        {{-- numeric --}}
                        <input x-show="dryRunRule(f)?.question_scoring_type === 'numeric_range'"
                               type="number"
                               x-model.number="dryRunAnswers[f.field_key]"
                               class="input input-sm w-full"
                               :placeholder="'Nhập giá trị số cho ' + f.field_key">
                    </div>
                </template>
            </div>
            <div class="shrink-0 border-t border-base-200 p-5 space-y-3">
                <button @click="runDryRun()" :disabled="dryRunLoading" class="btn btn-primary w-full gap-2">
                    <span x-show="dryRunLoading" class="loading loading-spinner loading-sm"></span>
                    Chạy thử
                </button>
                <div x-show="dryRunResult" class="rounded-xl border border-base-200 bg-base-50 p-3 text-xs space-y-1">
                    <p class="font-bold text-sm mb-2">Kết quả</p>
                    <template x-for="[code, ds] in Object.entries(dryRunResult?.domain_scores || {})" :key="code">
                        <div class="flex justify-between">
                            <span class="font-mono" x-text="code"></span>
                            <span x-text="'raw: ' + ds.raw + ' → ' + ds.normalized + '%'"></span>
                        </div>
                    </template>
                    <div class="divider my-1"></div>
                    <div class="flex justify-between font-semibold">
                        <span>Overall</span>
                        <span x-text="dryRunResult?.overall_score + ' / 100'"></span>
                    </div>
                    <div x-show="dryRunResult?.classification?.band_code">
                        Band: <span class="badge badge-sm badge-primary" x-text="dryRunResult?.classification?.band_code"></span>
                    </div>
                    <div x-show="dryRunResult?.pain_points?.length">
                        Pain points: <span class="font-mono" x-text="dryRunResult?.pain_points?.join(', ')"></span>
                    </div>
                    <div x-show="dryRunResult?.recommendations?.length">
                        Recs: <span class="font-mono" x-text="dryRunResult?.recommendations?.join(', ')"></span>
                    </div>
                </div>
                <div x-show="dryRunError" class="alert alert-error text-xs py-2" x-text="dryRunError"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function scoringConfig(surveyId, csrfToken) {
    return {
        surveyId,
        csrfToken,
        loading:    false,
        saving:     false,
        dirty:      false,
        activeTab:  1,
        flash:      { text: '', type: 'success' },
        fields:     [],
        flags:      [],
        dryRunOpen:    false,
        dryRunLoading: false,
        dryRunAnswers: {},
        dryRunResult:  null,
        dryRunError:   null,
        activeRoadmapBand: null,

        cfg: {
            hasScoring:         false,
            aggregationModel:   'weighted_domain',
            classificationType: 'score_band',
            domains:            [],
            rules:              {},
            bands:              [],
            passFailConfig:     { passing_score: 70, label_pass: 'Đạt', label_fail: 'Không đạt' },
            personas:           [],
            painPoints:         [],
            recommendations:    [],
            roadmap:            {},
            jobPositions:       [],
        },

        // ── Options statiques ───────────────────────────────────────────────────
        aggModelOptions: [
            { value: 'weighted_domain', icon: '⚖️', label: 'Weighted Domain', desc: 'Gộp theo domain + trọng số' },
            { value: 'flat_sum',        icon: '∑',  label: 'Flat Sum',        desc: 'Cộng thẳng tất cả features' },
            { value: 'sectioned',       icon: '▦',  label: 'Sectioned',       desc: 'Điểm độc lập từng section' },
        ],
        classTypeOptions: [
            { value: 'score_band',    icon: '📊', label: 'Score Band',    desc: 'Phân loại theo dải điểm' },
            { value: 'pass_fail',     icon: '✅', label: 'Pass / Fail',   desc: 'Chỉ Đạt / Không đạt' },
            { value: 'persona_match', icon: '👤', label: 'Persona Match', desc: 'Khớp nhóm người dùng' },
            { value: 'none',          icon: '—',  label: 'Không phân',   desc: 'Chỉ tính điểm' },
        ],

        // ── Computed ────────────────────────────────────────────────────────────
        get weightSum() {
            return this.cfg.domains.reduce((s, d) => s + (parseFloat(d.weight) || 0), 0);
        },

        get configuredRulesCount() {
            return Object.values(this.cfg.rules).filter(r => r.question_scoring_type && r.question_scoring_type !== 'none').length;
        },

        get roadmapBands() {
            if (this.cfg.classificationType === 'score_band') {
                return this.cfg.bands.map(b => b.band_code).filter(Boolean);
            }
            return Object.keys(this.cfg.roadmap);
        },

        get tabs() {
            const isNone    = this.cfg.classificationType === 'none';
            const noScoring = !this.cfg.hasScoring;

            return [
                { id: 1, label: '① Khai báo',  disabled: false,          hasError: false,                            valid: true },
                { id: 2, label: '② Domains',    disabled: noScoring,      hasError: this.domainErrors.length > 0,    valid: this.cfg.domains.length > 0 },
                { id: 3, label: '③ Rules',      disabled: noScoring,      hasError: false,                            valid: this.configuredRulesCount > 0 },
                { id: 4, label: '④ Bands',      disabled: isNone || noScoring, hasError: false,                      valid: this.cfg.bands.length > 0 || this.cfg.classificationType !== 'score_band' },
                { id: 5, label: '⑤ Outputs',    disabled: noScoring,      hasError: false,                            valid: this.cfg.painPoints.length > 0 || this.cfg.recommendations.length > 0 },
                { id: 6, label: '⑥ Roadmap',    disabled: noScoring,      hasError: false,                            valid: Object.keys(this.cfg.roadmap).length > 0 },
                { id: 7, label: '⑦ Review',     disabled: false,          hasError: this.hasErrors,                  valid: false },
                { id: 8, label: '⑧ Vị trí',    disabled: noScoring,      hasError: false,                            valid: this.cfg.jobPositions.length > 0 },
            ];
        },

        get domainErrors() {
            const errors = [];
            this.cfg.domains.forEach(d => {
                if (!d.domain_code) errors.push('domain_code không được để trống');
                else if (!/^[a-z0-9_]+$/.test(d.domain_code)) errors.push(`domain_code '${d.domain_code}' chỉ được dùng a-z, 0-9, _`);
                if (parseFloat(d.min_score) >= parseFloat(d.max_score)) errors.push(`Domain '${d.domain_code}': min_score >= max_score`);
            });
            if (this.cfg.aggregationModel === 'weighted_domain' && this.cfg.domains.length > 0) {
                if (Math.abs(this.weightSum - 1) > 0.01) errors.push('Tổng weight ≠ 1.00 (hiện: ' + this.weightSum.toFixed(3) + ')');
            }
            return errors;
        },

        get hasErrors() {
            return this.domainErrors.length > 0;
        },

        get checklist() {
            const agg   = this.cfg.aggregationModel;
            const items = [
                { label: 'has_scoring', status: this.cfg.hasScoring ? 'ok' : 'warn', detail: this.cfg.hasScoring ? 'true' : 'false' },
                { label: 'aggregation_model', status: 'ok', detail: agg },
                { label: 'classification_type', status: 'ok', detail: this.cfg.classificationType },
            ];
            if (agg === 'weighted_domain') {
                const wOk = Math.abs(this.weightSum - 1) <= 0.01;
                items.push({ label: 'Tổng weight domains', status: wOk ? 'ok' : 'error', detail: this.weightSum.toFixed(3) });
            }
            items.push({ label: 'Domains', status: this.cfg.domains.length > 0 ? 'ok' : 'warn', detail: this.cfg.domains.length + ' domain(s)' });
            items.push({ label: 'Score rules', status: this.configuredRulesCount > 0 ? 'ok' : 'warn', detail: this.configuredRulesCount + '/' + this.fields.length + ' câu hỏi' });
            if (this.cfg.classificationType === 'score_band') {
                items.push({ label: 'Score bands', status: this.cfg.bands.length > 0 ? 'ok' : 'error', detail: this.cfg.bands.length + ' bands' });
            }
            items.push({ label: 'Pain points', status: 'ok', detail: this.cfg.painPoints.length + ' rules' });
            items.push({ label: 'Recommendations', status: 'ok', detail: this.cfg.recommendations.length + ' rules' });
            items.push({ label: 'Dynamic weights (Phase 2)', status: 'warn', detail: 'Chưa kích hoạt' });
            this.domainErrors.forEach(e => items.push({ label: 'Lỗi', status: 'error', detail: e }));
            return items;
        },

        // ── Init ────────────────────────────────────────────────────────────────
        async init() {
            await Promise.all([this.loadConfig(), this.loadFields(), this.loadFlags()]);
            this.$watch('cfg', () => { this.dirty = true; }, { deep: true });
            window.addEventListener('beforeunload', (e) => {
                if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
            });
        },

        async loadConfig() {
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/scoring/config`, 'GET');
            if (!res) return;
            if (res.assessment) {
                this.cfg.hasScoring         = res.assessment.has_scoring;
                this.cfg.aggregationModel   = res.assessment.aggregation_model;
                this.cfg.classificationType = res.assessment.classification_type;
            }
            this.cfg.domains         = res.domains || [];
            this.cfg.bands           = res.bands || [];
            this.cfg.passFailConfig  = res.pass_fail || { passing_score: 70, label_pass: 'Đạt', label_fail: 'Không đạt' };
            this.cfg.personas        = (res.personas || []).map(p => ({ ...p, _open: false }));
            this.cfg.painPoints      = res.pain_points || [];
            this.cfg.recommendations = res.recommendations || [];
            this.cfg.roadmap         = res.roadmap || {};
            this.cfg.jobPositions    = (res.job_positions || []).map(jp => ({ ...jp, _open: false, _reqStr: JSON.stringify(jp.requirements || {}) }));
            // Phases cần _open flag
            Object.keys(this.cfg.roadmap).forEach(band => {
                this.cfg.roadmap[band] = this.cfg.roadmap[band].map(p => ({ ...p, _open: false }));
            });
            // Index rules by field_key
            this.cfg.rules = {};
            (res.rules || []).forEach(r => { this.cfg.rules[r.field_key] = r; });

            if (this.roadmapBands.length > 0) this.activeRoadmapBand = this.roadmapBands[0];
        },

        async loadFields() {
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/scoring/fields`, 'GET');
            if (!res) return;
            this.fields = (res.fields || []).map(f => ({ ...f, _open: false }));
        },

        async loadFlags() {
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/scoring/flags`, 'GET');
            if (res) this.flags = res.flags || [];
        },

        // ── Save ────────────────────────────────────────────────────────────────
        async saveConfig() {
            if (this.hasErrors) { this.err('Có lỗi cần sửa trước khi lưu.'); return; }
            const payload = {
                assessment: {
                    has_scoring:         this.cfg.hasScoring,
                    aggregation_model:   this.cfg.aggregationModel,
                    classification_type: this.cfg.classificationType,
                },
                domains:         this.cfg.domains,
                rules:           Object.values(this.cfg.rules).filter(r => r.question_scoring_type !== 'none'),
                bands:           this.cfg.bands,
                pass_fail:       this.cfg.passFailConfig,
                personas:        this.cfg.personas.map(({ _open, ...p }) => p),
                pain_points:     this.cfg.painPoints,
                recommendations: this.cfg.recommendations,
                roadmap:         Object.fromEntries(
                    Object.entries(this.cfg.roadmap).map(([band, phases]) => [
                        band,
                        phases.map(({ _open, ...p }) => p),
                    ])
                ),
                job_positions:   this.cfg.jobPositions.map(({ _open, _reqStr, ...jp }) => ({
                    ...jp,
                    requirements: jp.requirements || {},
                })),
            };
            const res = await this.api(`/dashboard/surveys/${this.surveyId}/scoring/config`, 'PUT', payload);
            if (res?.success) {
                this.dirty = false;
                this.ok(res.message || 'Đã lưu thành công.');
            }
        },

        // ── Domain CRUD ─────────────────────────────────────────────────────────
        addDomain() {
            this.cfg.domains.push({ domain_code: '', label: '', weight: 0, min_score: 0, max_score: 10 });
        },
        removeDomain(idx) {
            if (this.cfg.domains.length <= 1) return;
            if (!confirm('Xóa domain này? Các rules gắn domain này sẽ bị unset.')) return;
            this.cfg.domains.splice(idx, 1);
        },
        moveDomainUp(idx)   { if (idx > 0) [this.cfg.domains[idx-1], this.cfg.domains[idx]] = [this.cfg.domains[idx], this.cfg.domains[idx-1]]; },
        moveDomainDown(idx) { if (idx < this.cfg.domains.length-1) [this.cfg.domains[idx+1], this.cfg.domains[idx]] = [this.cfg.domains[idx], this.cfg.domains[idx+1]]; },

        // ── Rule helpers ────────────────────────────────────────────────────────
        getRuleForField(fieldKey) {
            const r = this.cfg.rules[fieldKey];
            return (r && r.question_scoring_type && r.question_scoring_type !== 'none') ? r : null;
        },

        getOrCreateRule(fieldKey) {
            if (!this.cfg.rules[fieldKey]) {
                this.cfg.rules[fieldKey] = {
                    field_key: fieldKey, feature_code: '', domain_code: '',
                    question_scoring_type: 'none', signal_flag: '',
                    score_if_true: 0, score_if_false: 0,
                    min_score_cap: null, max_score_cap: null,
                    options: [], ranges: [],
                };
            }
            return this.cfg.rules[fieldKey];
        },

        addRuleOption(fieldKey, field) {
            const rule = this.getOrCreateRule(fieldKey);
            const firstFieldOpt = (field?.field_options || [])[rule.options.length];
            rule.options.push({ option_value: firstFieldOpt?.value || '', option_label: firstFieldOpt?.label || '', score: 0, signal_flag: '' });
        },
        removeRuleOption(fieldKey, idx)   { this.cfg.rules[fieldKey]?.options.splice(idx, 1); },
        moveOptionUp(fieldKey, idx)       {
            const opts = this.cfg.rules[fieldKey]?.options;
            if (opts && idx > 0) [opts[idx-1], opts[idx]] = [opts[idx], opts[idx-1]];
        },
        moveOptionDown(fieldKey, idx)     {
            const opts = this.cfg.rules[fieldKey]?.options;
            if (opts && idx < opts.length-1) [opts[idx+1], opts[idx]] = [opts[idx], opts[idx+1]];
        },

        addRuleRange(fieldKey) { this.getOrCreateRule(fieldKey).ranges.push({ min_value: '', max_value: '', score: 0, signal_flag: '' }); },
        removeRuleRange(fieldKey, idx)    { this.cfg.rules[fieldKey]?.ranges.splice(idx, 1); },
        moveRangeUp(fieldKey, idx)        {
            const rs = this.cfg.rules[fieldKey]?.ranges;
            if (rs && idx > 0) [rs[idx-1], rs[idx]] = [rs[idx], rs[idx-1]];
        },
        moveRangeDown(fieldKey, idx)      {
            const rs = this.cfg.rules[fieldKey]?.ranges;
            if (rs && idx < rs.length-1) [rs[idx+1], rs[idx]] = [rs[idx], rs[idx+1]];
        },

        // ── Band CRUD ───────────────────────────────────────────────────────────
        addBand() { this.cfg.bands.push({ band_code: '', label: '', min_score: 0, max_score: 100 }); },
        removeBand(idx)  { this.cfg.bands.splice(idx, 1); },
        moveBandUp(idx)  { if (idx > 0) [this.cfg.bands[idx-1], this.cfg.bands[idx]] = [this.cfg.bands[idx], this.cfg.bands[idx-1]]; },
        moveBandDown(idx){ if (idx < this.cfg.bands.length-1) [this.cfg.bands[idx+1], this.cfg.bands[idx]] = [this.cfg.bands[idx], this.cfg.bands[idx+1]]; },

        bandHue(code) {
            const hues = { MANUAL: 0, DIGITAL: 45, AI_READY: 120, AI_TRANS: 270 };
            for (const k in hues) if (code.includes(k)) return hues[k];
            return (code.charCodeAt(0) * 57) % 360;
        },

        // ── Persona CRUD ────────────────────────────────────────────────────────
        addPersona() {
            this.cfg.personas.push({ persona_code: 'persona_' + Date.now(), label: '', description: '', conditions: [], _open: true });
        },
        removePersona(idx) { if (confirm('Xóa persona này?')) this.cfg.personas.splice(idx, 1); },
        addPersonaCondition(pIdx) {
            this.cfg.personas[pIdx].conditions.push({ target_type: 'domain', target_code: this.cfg.domains[0]?.domain_code || '', operator: '>=', threshold_value: 50, flag_value: null });
        },
        removePersonaCondition(pIdx, cIdx) { this.cfg.personas[pIdx].conditions.splice(cIdx, 1); },

        // ── Pain point / Recommendation CRUD ───────────────────────────────────
        addPainPoint()        { this.cfg.painPoints.push({ pain_point_code: '', label: '', required_flags: '' }); },
        removePainPoint(idx)  { this.cfg.painPoints.splice(idx, 1); },
        addRecommendation()   { this.cfg.recommendations.push({ recommendation_code: '', label: '', trigger_domain: '', threshold_score: 50 }); },
        removeRecommendation(idx) { this.cfg.recommendations.splice(idx, 1); },
        moveRecUp(idx)        { if (idx > 0) [this.cfg.recommendations[idx-1], this.cfg.recommendations[idx]] = [this.cfg.recommendations[idx], this.cfg.recommendations[idx-1]]; },
        moveRecDown(idx)      { if (idx < this.cfg.recommendations.length-1) [this.cfg.recommendations[idx+1], this.cfg.recommendations[idx]] = [this.cfg.recommendations[idx], this.cfg.recommendations[idx+1]]; },

        // ── Job Position CRUD ────────────────────────────────────────────────────
        addJobPosition() {
            this.cfg.jobPositions.push({
                position_code: '', title: '', description: '',
                min_overall_score: null, requirements: {}, sort_order: this.cfg.jobPositions.length,
                is_active: true, _open: false, _reqStr: '{}',
            });
        },
        removeJobPosition(idx) { this.cfg.jobPositions.splice(idx, 1); },
        addRequirement(jp) {
            const dc = prompt('Domain code (vd: leadership):');
            if (!dc || !dc.trim()) return;
            const ms = parseFloat(prompt('Điểm normalized tối thiểu (0-100):', '60'));
            if (isNaN(ms)) return;
            if (!jp.requirements) jp.requirements = {};
            jp.requirements[dc.trim()] = ms;
        },
        removeRequirement(jp, domainCode) {
            if (jp.requirements) {
                const reqs = { ...jp.requirements };
                delete reqs[domainCode];
                jp.requirements = reqs;
            }
        },

        // ── Roadmap CRUD ────────────────────────────────────────────────────────
        getRoadmapPhases(band) {
            if (!this.cfg.roadmap[band]) this.cfg.roadmap[band] = [];
            return this.cfg.roadmap[band];
        },
        addPhase(band) {
            this.getRoadmapPhases(band).push({ phase_code: 'phase_' + Date.now(), title: '', description: '', duration_weeks: 4, milestones: [], _open: true });
        },
        removePhase(band, idx) { if (confirm('Xóa phase này?')) this.cfg.roadmap[band]?.splice(idx, 1); },
        movePhaseUp(band, idx) {
            const phases = this.cfg.roadmap[band];
            if (phases && idx > 0) [phases[idx-1], phases[idx]] = [phases[idx], phases[idx-1]];
        },
        movePhaseDown(band, idx) {
            const phases = this.cfg.roadmap[band];
            if (phases && idx < phases.length-1) [phases[idx+1], phases[idx]] = [phases[idx], phases[idx+1]];
        },
        addMilestone(band, pIdx)               { this.cfg.roadmap[band][pIdx].milestones.push({ title: '', sort_order: 0 }); },
        removeMilestone(band, pIdx, mIdx)       { this.cfg.roadmap[band][pIdx].milestones.splice(mIdx, 1); },
        moveMilestoneUp(band, pIdx, mIdx)       {
            const ms = this.cfg.roadmap[band][pIdx].milestones;
            if (ms && mIdx > 0) [ms[mIdx-1], ms[mIdx]] = [ms[mIdx], ms[mIdx-1]];
        },
        moveMilestoneDown(band, pIdx, mIdx)     {
            const ms = this.cfg.roadmap[band][pIdx].milestones;
            if (ms && mIdx < ms.length-1) [ms[mIdx+1], ms[mIdx]] = [ms[mIdx], ms[mIdx+1]];
        },

        // ── Dry-run ─────────────────────────────────────────────────────────────
        dryRunRule(field) { return this.cfg.rules[field.field_key] || null; },

        toggleDryRunMulti(fieldKey, value) {
            if (!Array.isArray(this.dryRunAnswers[fieldKey])) this.dryRunAnswers[fieldKey] = [];
            const arr = this.dryRunAnswers[fieldKey];
            const idx = arr.indexOf(value);
            if (idx >= 0) arr.splice(idx, 1); else arr.push(value);
        },

        async runDryRun() {
            this.dryRunLoading = true;
            this.dryRunResult  = null;
            this.dryRunError   = null;

            // Build answer payload in AnswerReader format
            const answers = {};
            for (const f of this.fields) {
                const rule = this.dryRunRule(f);
                if (!rule || rule.question_scoring_type === 'none') continue;
                const raw = this.dryRunAnswers[f.field_key];
                if (raw === undefined || raw === null) continue;
                const type = rule.question_scoring_type;
                if (type === 'boolean') {
                    answers[f.field_key] = { type: 'boolean', value: !!raw };
                } else if (type === 'single_choice') {
                    answers[f.field_key] = { type: 'choice', values: [raw] };
                } else if (type === 'multi_choice') {
                    answers[f.field_key] = { type: 'choice', values: Array.isArray(raw) ? raw : [raw] };
                } else if (type === 'numeric_range') {
                    answers[f.field_key] = { type: 'number', value: parseFloat(raw) };
                }
            }

            const res = await this.api(`/dashboard/surveys/${this.surveyId}/scoring/dry-run`, 'POST', { answers });
            if (res?.error) {
                this.dryRunError = res.error;
            } else {
                this.dryRunResult = res;
            }
            this.dryRunLoading = false;
        },

        // ── Export ──────────────────────────────────────────────────────────────
        exportJson() {
            const blob = new Blob([JSON.stringify(this.cfg, null, 2)], { type: 'application/json' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = 'scoring-config.json'; a.click();
            URL.revokeObjectURL(url);
        },

        // ── UI helpers ──────────────────────────────────────────────────────────
        fieldTypeBadge(type) {
            const map = { 1:'badge-info', 2:'badge-info', 3:'badge-success', 7:'badge-success', 4:'badge-secondary', 5:'badge-secondary', 6:'badge-secondary', 8:'badge-ghost', 9:'badge-primary' };
            return map[type] || 'badge-neutral';
        },

        ok(msg)  { this.flash = { text: msg, type: 'success' }; setTimeout(() => this.flash.text = '', 3500); },
        err(msg) { this.flash = { text: msg, type: 'error'   }; setTimeout(() => this.flash.text = '', 6000); },

        async api(url, method, body = null) {
            const isWrite = method !== 'GET';
            if (isWrite) this.saving = true; else this.loading = true;
            try {
                const opts = {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                };
                if (body && isWrite) opts.body = JSON.stringify(body);
                const response = await fetch(url, opts);
                const json     = await response.json();
                if (!response.ok) {
                    const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(' ') : 'Có lỗi xảy ra.');
                    this.err(msg);
                    return null;
                }
                return json;
            } catch {
                this.err('Lỗi kết nối. Vui lòng thử lại.');
                return null;
            } finally {
                if (isWrite) this.saving = false; else this.loading = false;
            }
        },
    };
}
</script>
@endpush

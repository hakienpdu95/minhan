@extends('layouts.backend')
@section('title', $lead->displayTitle())

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <span class="current">{{ $lead->displayTitle() }}</span>
</nav>
@endsection

@section('content')
<div x-data="leadShowPage" x-init="init()">

    {{-- ── Lead header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-xs font-mono text-base-content/30">#{{ $lead->id }}</span>
                {{-- Stage badge --}}
                <span class="badge badge-sm gap-1" style="background:{{ $lead->stage?->color ?? '#94a3b8' }}20; color:{{ $lead->stage?->color ?? '#94a3b8' }}; border-color:{{ $lead->stage?->color ?? '#94a3b8' }}40">
                    <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $lead->stage?->color ?? '#94a3b8' }}"></span>
                    {{ $lead->stage?->label ?? 'Không rõ' }}
                </span>
                {{-- Status badge --}}
                <span class="badge badge-sm badge-soft {{ $lead->status?->badgeClass() }}">
                    {{ $lead->status?->label() }}
                </span>
                {{-- Score --}}
                @if($lead->lead_score !== null)
                <span class="badge badge-sm {{ $lead->lead_score >= 70 ? 'badge-error' : ($lead->lead_score >= 40 ? 'badge-warning' : 'badge-ghost') }}">
                    {{ $lead->lead_score }} điểm
                </span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-base-content">{{ $lead->displayTitle() }}</h1>
            @if($lead->description)
            <p class="text-sm text-base-content/60 mt-1 line-clamp-2">{{ $lead->description }}</p>
            @endif
        </div>

        <div class="flex gap-2 shrink-0">
            @can('update', $lead)
            <a href="{{ route('lead.edit', $lead) }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Sửa
            </a>
            @endcan
            @can('delete', $lead)
            <form method="POST" action="{{ route('lead.destroy', $lead) }}"
                  onsubmit="return confirm('Xóa cơ hội này?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm text-error gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Xóa
                </button>
            </form>
            @endcan
        </div>
    </div>

    {{-- ── 2-column layout ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Tabs ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Tab bar --}}
            <div class="tabs tabs-bordered">
                <button class="tab tab-sm" :class="tab === 'activities' ? 'tab-active' : ''"
                        @click="tab = 'activities'">
                    Hoạt động
                    <span class="badge badge-xs badge-ghost ml-1">{{ $lead->activity_count }}</span>
                </button>
                <button class="tab tab-sm" :class="tab === 'notes' ? 'tab-active' : ''"
                        @click="tab = 'notes'">
                    Ghi chú
                    <span class="badge badge-xs badge-ghost ml-1">{{ $lead->notes->count() }}</span>
                </button>
                <button class="tab tab-sm" :class="tab === 'history' ? 'tab-active' : ''"
                        @click="tab = 'history'">
                    Lịch sử tình trạng
                </button>
                @if($assessmentResult)
                <button class="tab tab-sm" :class="tab === 'assessment' ? 'tab-active' : ''"
                        @click="tab = 'assessment'">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Đánh giá sâu
                    <span class="badge badge-xs badge-primary ml-1">{{ round($assessmentResult->overall_score) }}</span>
                </button>
                @endif
            </div>

            {{-- ── Tab: Activities ─────────────────────────────────────── --}}
            <div x-show="tab === 'activities'">

                {{-- Log activity form --}}
                @can('update', $lead)
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body py-3 px-4">
                        <div x-data="{ open: false }">
                            <button class="btn btn-sm btn-ghost gap-1.5 text-primary" @click="open = !open">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Ghi lại hoạt động
                            </button>

                            <div x-show="open" x-transition class="mt-3 pt-3 border-t border-base-200">
                                <form id="activity-form" @submit.prevent="submitActivity($event)">
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div class="form-control">
                                            <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Loại <span class="text-error">*</span></span></label>
                                            <select name="type" class="select select-bordered select-xs w-full" required>
                                                @foreach(\Modules\Lead\Enums\LeadActivityType::cases() as $type)
                                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-control">
                                            <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Ngày hoàn thành</span></label>
                                            <input type="datetime-local" name="completed_at"
                                                   value="{{ now()->format('Y-m-d\TH:i') }}"
                                                   class="input input-bordered input-xs w-full">
                                        </div>
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Tiêu đề <span class="text-error">*</span></span></label>
                                        <input type="text" name="title" required
                                               class="input input-bordered input-xs w-full"
                                               placeholder="VD: Gọi điện tư vấn sản phẩm">
                                    </div>
                                    <div class="form-control mb-3">
                                        <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Nội dung / Kết quả</span></label>
                                        <textarea name="description" rows="2"
                                                  class="textarea textarea-bordered textarea-xs w-full"
                                                  placeholder="Ghi tóm tắt nội dung trao đổi..."></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-xs" :disabled="actSaving">
                                            <span x-show="actSaving" class="loading loading-spinner loading-xs mr-1"></span>
                                            Lưu hoạt động
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" @click="open = false">Hủy</button>
                                    </div>
                                    <p x-show="actError" x-text="actError" class="text-xs text-error mt-2"></p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Activity timeline --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-0">
                        @if($lead->activities->isEmpty())
                        <div class="py-12 text-center text-base-content/30">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">Chưa có hoạt động nào</p>
                        </div>
                        @else
                        <ul id="activity-list" class="divide-y divide-base-200">
                            @foreach($lead->activities as $act)
                            <li class="px-5 py-4 flex gap-3">
                                <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="text-xs">{{ $act->type?->icon() ?? '•' }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-medium text-sm">{{ $act->title }}</p>
                                        <span class="text-xs text-base-content/40 shrink-0">
                                            {{ $act->completed_at?->format('d/m/Y H:i') ?? $act->created_at?->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    @if($act->description)
                                    <p class="text-sm text-base-content/60 mt-1">{{ $act->description }}</p>
                                    @endif
                                    <p class="text-xs text-base-content/40 mt-1">{{ $act->actor_name }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ── Tab: Notes ───────────────────────────────────────────── --}}
            <div x-show="tab === 'notes'">

                {{-- Add note --}}
                <div class="card bg-base-100 shadow-sm border border-base-200 mb-3">
                    <div class="card-body py-3 px-4">
                        <form @submit.prevent="submitNote($event)">
                            <textarea id="note-input" name="content" rows="3"
                                      class="textarea textarea-bordered textarea-sm w-full mb-2"
                                      placeholder="Thêm ghi chú..." required></textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-xs" :disabled="noteSaving">
                                    <span x-show="noteSaving" class="loading loading-spinner loading-xs mr-1"></span>
                                    Thêm ghi chú
                                </button>
                            </div>
                            <p x-show="noteError" x-text="noteError" class="text-xs text-error mt-1"></p>
                        </form>
                    </div>
                </div>

                {{-- Notes list --}}
                <div class="space-y-2" id="notes-list">
                    @forelse($lead->notes as $note)
                    <div class="card bg-base-100 shadow-sm border border-base-200{{ $note->is_pinned ? ' border-warning/40 bg-warning/5' : '' }}"
                         data-note-id="{{ $note->id }}">
                        <div class="card-body py-3 px-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    @if($note->is_pinned)
                                    <span class="badge badge-xs badge-warning mb-1.5">📌 Đã ghim</span>
                                    @endif
                                    <p class="text-sm whitespace-pre-line">{{ $note->content }}</p>
                                    <p class="text-xs text-base-content/40 mt-2">
                                        {{ $note->author_name }} &bull; {{ $note->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex gap-1 shrink-0">
                                    <button class="btn btn-ghost btn-xs btn-square text-base-content/30 hover:text-warning"
                                            title="{{ $note->is_pinned ? 'Bỏ ghim' : 'Ghim' }}"
                                            onclick="toggleNotePin({{ $note->id }}, this)">
                                        <svg class="w-3.5 h-3.5" fill="{{ $note->is_pinned ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    </button>
                                    <button class="btn btn-ghost btn-xs btn-square text-error/30 hover:text-error"
                                            title="Xóa ghi chú"
                                            onclick="deleteNote({{ $note->id }}, this)">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body py-12 text-center text-base-content/30">
                            <p class="text-sm">Chưa có ghi chú nào</p>
                        </div>
                    </div>
                    @endforelse
                </div>

            </div>

            {{-- ── Tab: Stage history ───────────────────────────────────── --}}
            <div x-show="tab === 'history'">
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-0">
                        @if($lead->stageHistory->isEmpty())
                        <div class="py-12 text-center text-base-content/30 text-sm">Chưa có lịch sử tình trạng</div>
                        @else
                        <ul class="divide-y divide-base-200">
                            @foreach($lead->stageHistory as $h)
                            <li class="px-5 py-4 flex gap-3 items-start">
                                <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        @if($h->stage_from_label)
                                        <span class="text-base-content/50">{{ $h->stage_from_label }}</span>
                                        <svg class="w-3.5 h-3.5 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        @endif
                                        <span class="font-medium">{{ $h->stage_to_label }}</span>
                                    </div>
                                    @if($h->note)
                                    <p class="text-xs text-base-content/60 mt-1">{{ $h->note }}</p>
                                    @endif
                                    <p class="text-xs text-base-content/40 mt-1">
                                        {{ $h->changed_by_name }} &bull; {{ \Carbon\Carbon::parse($h->changed_at)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Tab: Đánh giá sâu (Assessment) ──────────────────────── --}}
            @if($assessmentResult)
            <div x-show="tab === 'assessment'">
                <div class="space-y-4">

                    {{-- Overall score card --}}
                    <div class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body py-4 px-5">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-base-content/50 mb-1">Điểm tổng</p>
                                    <p class="text-4xl font-bold text-primary">{{ round($assessmentResult->overall_score, 1) }}</p>
                                    <p class="text-xs text-base-content/40 mt-0.5">/ 100</p>
                                </div>
                                <div>
                                    <p class="text-xs text-base-content/50 mb-1">Phân loại</p>
                                    <span class="badge badge-lg badge-soft badge-info font-semibold">
                                        {{ $assessmentResult->maturity_level ?? '—' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-base-content/50 mb-1">Cập nhật</p>
                                    <p class="text-sm font-medium">{{ $assessmentResult->calculated_at?->format('d/m H:i') ?? '—' }}</p>
                                    @can('assessment.reprocess')
                                    <button onclick="rerunLeadAssessment()"
                                            class="btn btn-xs btn-ghost text-warning mt-1" title="Tính lại">↻ Tính lại</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Domain scores --}}
                    @if($assessmentResult->domainScores->isNotEmpty())
                    <div class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Điểm theo domain</div>
                        <div class="divide-y divide-base-200">
                            @foreach($assessmentResult->domainScores as $ds)
                            <div class="px-5 py-3 flex items-center gap-4">
                                <div class="w-36 shrink-0 text-sm font-medium">{{ $ds->domain_code }}</div>
                                <div class="flex-1 flex items-center gap-3">
                                    <div class="flex-1 bg-base-200 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full bg-primary transition-all"
                                             style="width: {{ min(100, round($ds->normalized_score)) }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-primary w-10 text-right">
                                        {{ round($ds->normalized_score, 1) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Pain points --}}
                    @if($assessmentResult->painPoints->isNotEmpty())
                    <div class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm text-warning">⚠ Điểm yếu</div>
                        <div class="px-5 py-3 flex flex-wrap gap-2">
                            @foreach($assessmentResult->painPoints as $pp)
                            <span class="badge badge-sm badge-soft badge-warning font-mono">{{ $pp->pain_point_code }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Recommendations --}}
                    @if($assessmentResult->recommendations->isNotEmpty())
                    <div class="card bg-base-100 shadow-sm border border-base-200">
                        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">💡 Đề xuất hành động</div>
                        <div class="divide-y divide-base-200">
                            @foreach($assessmentResult->recommendations as $rec)
                            <div class="px-5 py-3 flex items-center gap-3">
                                <span class="badge badge-xs badge-ghost w-6 text-center shrink-0">#{{ $rec->priority }}</span>
                                <span class="text-sm font-mono text-base-content/70">{{ $rec->recommendation_code }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

        </div>

        {{-- ── RIGHT: Sidebar info ─────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Stage change --}}
            @can('update', $lead)
            @if(!$lead->isTerminal())
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <h3 class="font-semibold text-sm mb-2">Đổi tình trạng</h3>
                    <select id="stage-select" class="select select-bordered select-sm w-full mb-2"
                            onchange="stageSelectChanged(this)">
                        @foreach($stages as $stage)
                        <option value="{{ $stage->id }}"
                            {{ $lead->stage_id == $stage->id ? 'selected' : '' }}
                            data-won="{{ $stage->is_won ? '1' : '0' }}"
                            data-lost="{{ $stage->is_lost ? '1' : '0' }}">
                            {{ $stage->label }}
                            @if($stage->probability) ({{ $stage->probability }}%) @endif
                        </option>
                        @endforeach
                    </select>
                    <textarea id="stage-note" rows="2"
                              class="textarea textarea-bordered textarea-xs w-full mb-2 hidden"
                              placeholder="Ghi chú (tuỳ chọn)..."></textarea>
                    <button id="stage-save-btn" class="btn btn-primary btn-sm w-full hidden"
                            onclick="saveStageChange()">
                        Lưu thay đổi
                    </button>
                    <p id="stage-error" class="text-xs text-error mt-1 hidden"></p>
                </div>
            </div>
            @endif
            @endcan

            {{-- Lead info --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <h3 class="font-semibold text-sm mb-3">Thông tin cơ hội</h3>
                    <dl class="space-y-2 text-sm">
                        @if($lead->expected_value)
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Giá trị</dt>
                            <dd class="font-semibold text-success">
                                {{ number_format($lead->expected_value) }} {{ $lead->currency }}
                            </dd>
                        </div>
                        @endif
                        @if($lead->expected_close_date)
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Ngày chốt dự kiến</dt>
                            <dd class="{{ $lead->expected_close_date->isPast() && $lead->isActive() ? 'text-error font-medium' : '' }}">
                                {{ $lead->expected_close_date->format('d/m/Y') }}
                            </dd>
                        </div>
                        @endif
                        @if($lead->actual_close_date)
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Ngày chốt thực tế</dt>
                            <dd>{{ $lead->actual_close_date->format('d/m/Y') }}</dd>
                        </div>
                        @endif
                        @if($lead->source)
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Nguồn</dt>
                            <dd>{{ $lead->source->label }}</dd>
                        </div>
                        @endif
                        @if($lead->survey_score !== null)
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Điểm khảo sát</dt>
                            <dd class="font-medium">{{ $lead->survey_score }}
                                @if($lead->survey_band_code)
                                <span class="badge badge-xs badge-ghost ml-1">{{ $lead->survey_band_code }}</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                        @if($surveyResponseUrl)
                        <div class="flex justify-between items-center">
                            <dt class="text-base-content/50 text-xs">Kết quả khảo sát</dt>
                            <dd>
                                <a href="{{ $surveyResponseUrl }}" target="_blank"
                                   class="link link-primary text-xs flex items-center gap-1">
                                    Xem chi tiết
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Tạo lúc</dt>
                            <dd class="text-xs">{{ $lead->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/50 text-xs">Cập nhật</dt>
                            <dd class="text-xs">{{ $lead->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Contact info --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-sm">Khách hàng</h3>
                        @if($maskContact)
                        <span class="badge badge-xs badge-warning">Ẩn thông tin</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="avatar placeholder">
                            <div class="w-9 rounded-full bg-primary/20 text-primary text-sm font-bold">
                                <span>{{ mb_substr($lead->contact_name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            @if($maskContact)
                            <p class="font-medium text-sm">{{ mb_substr($lead->contact_name, 0, 1) }}***</p>
                            @else
                            <p class="font-medium text-sm">{{ $lead->contact_name }}</p>
                            @endif
                            @if($lead->contact_company)
                            <p class="text-xs text-base-content/50">
                                @if($maskContact) *** @else {{ $lead->contact_company }} @endif
                            </p>
                            @endif
                        </div>
                    </div>
                    @unless($maskContact)
                    <dl class="space-y-1.5 text-sm">
                        @if($lead->contact_phone)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $lead->contact_phone }}" class="hover:text-primary transition-colors">{{ $lead->contact_phone }}</a>
                        </div>
                        @endif
                        @if($lead->contact?->email)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-base-content/40 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $lead->contact->email }}" class="hover:text-primary transition-colors">{{ $lead->contact->email }}</a>
                        </div>
                        @endif
                    </dl>
                    @endunless
                </div>
            </div>

            {{-- Assignment --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <h3 class="font-semibold text-sm mb-2">Người phụ trách</h3>
                    @if($lead->assignee)
                    <div class="flex items-center gap-2">
                        <div class="avatar placeholder">
                            <div class="w-8 rounded-full bg-primary/20 text-primary text-xs font-bold">
                                <span>{{ mb_substr($lead->assignee->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-sm">{{ $lead->assignee->name }}</p>
                            @if($lead->assigned_at)
                            <p class="text-xs text-base-content/40">Từ {{ $lead->assigned_at->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-base-content/40">Chưa phân công</p>
                    @endif
                </div>
            </div>

            {{-- Tags --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body py-3 px-4">
                    <h3 class="font-semibold text-sm mb-2">Tags</h3>

                    {{-- Current tags display --}}
                    <div id="tags-display" class="flex flex-wrap gap-1.5 mb-2 min-h-5">
                        @forelse($lead->tags as $tag)
                        <span class="badge badge-sm font-medium text-white"
                              style="background: {{ $tag->color }}">{{ $tag->name }}</span>
                        @empty
                        <span class="text-xs text-base-content/40" id="tags-empty">Chưa có tag</span>
                        @endforelse
                    </div>

                    @can('update', $lead)
                    <select id="tag-select" multiple
                            class="select select-bordered select-xs w-full"
                            data-tags-url="{{ route('api.api.lead.tags.list') }}"
                            data-sync-url="{{ route('api.api.lead.tags.sync', $lead) }}"
                            placeholder="Thêm / gỡ tag...">
                        @foreach($lead->tags as $tag)
                        <option value="{{ $tag->id }}" selected
                                data-color="{{ $tag->color }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <p id="tags-error" class="text-xs text-error mt-1 hidden"></p>
                    @endcan
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite(['resources/js/modules/tom-select.js'], 'build/backend')
<script>
var CSRF_TOKEN       = '{{ csrf_token() }}';
var LEAD_ID          = {{ $lead->id }};
var CHANGE_STAGE_URL = '{{ route('lead.change-stage', $lead) }}';
var ORIG_STAGE_ID    = {{ $lead->stage_id }};

// ── Lead Assessment rerun ─────────────────────────────────────────────────────
function rerunLeadAssessment() {
    if (!confirm('Tính lại đánh giá sâu cho lead này?')) return;
    @if($assessmentResult)
    var code = @js($assessmentResult->assessment_code);
    var id   = @js($assessmentResult->id);
    fetch('/dashboard/assessments/' + code + '/results/' + id + '/recalculate', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    }).then(r => r.json()).then(d => {
        if (d.ok) { setTimeout(() => location.reload(), 800); }
        else alert(d.message || 'Lỗi khi tính lại.');
    }).catch(() => alert('Lỗi kết nối.'));
    @endif
}

// ── Stage change ──────────────────────────────────────────────────────────────
function stageSelectChanged(sel) {
    var newId = parseInt(sel.value);
    var note  = document.getElementById('stage-note');
    var btn   = document.getElementById('stage-save-btn');
    var err   = document.getElementById('stage-error');

    if (newId !== ORIG_STAGE_ID) {
        note.classList.remove('hidden');
        btn.classList.remove('hidden');
    } else {
        note.classList.add('hidden');
        btn.classList.add('hidden');
    }
    err.classList.add('hidden');
}

async function saveStageChange() {
    var sel  = document.getElementById('stage-select');
    var note = document.getElementById('stage-note');
    var btn  = document.getElementById('stage-save-btn');
    var err  = document.getElementById('stage-error');

    btn.disabled    = true;
    btn.textContent = 'Đang lưu...';

    try {
        var res = await fetch(CHANGE_STAGE_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify({
                stage_id: parseInt(sel.value),
                note:     note.value.trim() || null,
            }),
        });

        var data = await res.json();

        if (res.ok && data.ok) {
            window.location.reload();
        } else {
            err.textContent = data.message || 'Đổi tình trạng thất bại.';
            err.classList.remove('hidden');
            btn.disabled    = false;
            btn.textContent = 'Lưu thay đổi';
        }
    } catch (e) {
        err.textContent = 'Lỗi kết nối.';
        err.classList.remove('hidden');
        btn.disabled    = false;
        btn.textContent = 'Lưu thay đổi';
    }
}

// ── Note helpers ──────────────────────────────────────────────────────────────
async function toggleNotePin(noteId, btn) {
    btn.disabled = true;
    try {
        var res = await fetch('/api/v1/leads/' + LEAD_ID + '/notes/' + noteId + '/toggle-pin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        });
        if (res.ok) window.location.reload();
    } finally {
        btn.disabled = false;
    }
}

async function deleteNote(noteId, btn) {
    if (!confirm('Xóa ghi chú này?')) return;
    btn.disabled = true;
    try {
        var res = await fetch('/api/v1/leads/' + LEAD_ID + '/notes/' + noteId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':     CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        });
        if (res.ok) {
            var card = btn.closest('[data-note-id]');
            if (card) card.remove();
        }
    } finally {
        btn.disabled = false;
    }
}

// ── Tags TomSelect ────────────────────────────────────────────────────────────
var tagSelectEl = document.getElementById('tag-select');
if (tagSelectEl) {
    var tagsUrl = tagSelectEl.dataset.tagsUrl;
    var syncUrl = tagSelectEl.dataset.syncUrl;

    var tagTs = new window.TomSelect('#tag-select', {
        plugins: ['remove_button'],
        valueField:  'id',
        labelField:  'text',
        searchField: ['text'],
        placeholder: 'Thêm / gỡ tag...',
        create: false,
        load: function(query, callback) {
            fetch(tagsUrl + '?q=' + encodeURIComponent(query))
                .then(function(r) { return r.json(); })
                .then(callback)
                .catch(function() { callback(); });
        },
        render: {
            option: function(data) {
                return '<div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background:' + (data.color || '#6b7280') + '"></span>' + data.text + '</div>';
            },
            item: function(data) {
                return '<div class="flex items-center gap-1" style="background:' + (data.color || '#6b7280') + ';color:#fff;border-radius:4px;padding:0 6px">' + data.text + '</div>';
            },
        },
        onChange: function(values) {
            var tagIds = values.map(function(v) { return parseInt(v); }).filter(Boolean);
            syncTags(tagIds);
        },
    });

    function syncTags(tagIds) {
        var err = document.getElementById('tags-error');
        err.classList.add('hidden');

        fetch(syncUrl, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN':     CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify({ tag_ids: tagIds }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                // Re-render badges
                var display = document.getElementById('tags-display');
                if (data.tags && data.tags.length) {
                    display.innerHTML = data.tags.map(function(t) {
                        return '<span class="badge badge-sm font-medium text-white" style="background:' + t.color + '">' + t.name + '</span>';
                    }).join('');
                } else {
                    display.innerHTML = '<span class="text-xs text-base-content/40">Chưa có tag</span>';
                }
            } else {
                err.textContent = 'Lỗi khi cập nhật tag.';
                err.classList.remove('hidden');
            }
        })
        .catch(function() {
            err.textContent = 'Lỗi kết nối.';
            err.classList.remove('hidden');
        });
    }
}

// ── Alpine component ──────────────────────────────────────────────────────────
document.addEventListener('alpine:init', function() {
    Alpine.data('leadShowPage', function() {
        return {
            tab:       'activities',
            actSaving: false,
            actError:  '',
            noteSaving: false,
            noteError:  '',

            init() {},

            async submitActivity(e) {
                var form = e.target;
                var fd   = new FormData(form);
                var body = {};
                fd.forEach(function(v, k) { body[k] = v; });

                if (!body.type || !body.title) {
                    this.actError = 'Vui lòng điền đầy đủ loại và tiêu đề.';
                    return;
                }

                this.actSaving = true;
                this.actError  = '';

                try {
                    var res = await fetch('/api/v1/leads/' + LEAD_ID + '/activities', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     CSRF_TOKEN,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept':           'application/json',
                            'Content-Type':     'application/json',
                        },
                        body: JSON.stringify(body),
                    });

                    var data = await res.json();

                    if (res.ok && data.ok) {
                        window.location.reload();
                    } else {
                        var msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Lỗi không xác định.');
                        this.actError = msgs;
                    }
                } catch(e) {
                    this.actError = 'Lỗi kết nối.';
                } finally {
                    this.actSaving = false;
                }
            },

            async submitNote(e) {
                var content = document.getElementById('note-input').value.trim();
                if (!content) return;

                this.noteSaving = true;
                this.noteError  = '';

                try {
                    var res = await fetch('/api/v1/leads/' + LEAD_ID + '/notes', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     CSRF_TOKEN,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept':           'application/json',
                            'Content-Type':     'application/json',
                        },
                        body: JSON.stringify({ content: content }),
                    });

                    var data = await res.json();

                    if (res.ok && data.ok) {
                        window.location.reload();
                    } else {
                        var msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Lỗi không xác định.');
                        this.noteError = msgs;
                    }
                } catch(e) {
                    this.noteError = 'Lỗi kết nối.';
                } finally {
                    this.noteSaving = false;
                }
            },
        };
    });
});
</script>
@endpush

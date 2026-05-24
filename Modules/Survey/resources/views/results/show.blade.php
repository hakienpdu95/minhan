@extends('layouts.backend')

@section('title', 'Kết quả #' . $response->id . ' — ' . $survey->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.edit', $survey) }}">{{ Str::limit($survey->title, 35) }}</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.responses.index', $survey) }}">Responses</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.responses.show', [$survey, $response]) }}">#{{ $response->id }}</a>
    <span class="sep">›</span>
    <span class="current">Kết quả chấm điểm</span>
</nav>
@endsection

@section('content')
<div class="space-y-5 max-w-4xl" x-data="resultShowPage({{ $response->id }}, {{ $survey->id }})">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Kết quả chấm điểm</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                Response #{{ $response->id }}
                @if($response->respondent_ref)
                    · <span class="font-medium text-base-content/70">{{ $response->respondent_ref }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('survey.update')
            <button type="button" @click="recalculate()" :disabled="recalculating"
                    class="btn btn-warning btn-sm gap-1.5">
                <svg class="w-4 h-4" :class="{'animate-spin': recalculating}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="recalculating ? 'Đang tính...' : 'Tính lại'"></span>
            </button>
            @endcan
            <a href="{{ route('backend.surveys.responses.show', [$survey, $response]) }}"
               class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Xem response
            </a>
        </div>
    </div>

    {{-- ── Recalculate flash ────────────────────────────────────────────── --}}
    <div x-show="flashMsg" x-transition class="alert text-sm py-2 px-4 rounded-lg"
         :class="flashSuccess ? 'alert-success' : 'alert-error'" x-text="flashMsg"></div>

    @if(!$result)
    {{-- ── No result yet ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body text-center py-12">
            <svg class="w-10 h-10 mx-auto text-base-content/20 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 4h16v16H4z"/>
            </svg>
            <p class="text-base-content/50 text-sm">Kết quả chấm điểm chưa sẵn sàng.</p>
            @can('survey.update')
            <button type="button" @click="recalculate()" :disabled="recalculating"
                    class="btn btn-primary btn-sm mt-4 mx-auto">
                <span x-text="recalculating ? 'Đang tính...' : 'Tính điểm ngay'"></span>
            </button>
            @endcan
        </div>
    </div>
    @else

    {{-- ── Overall score card ───────────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 text-center">
                <div class="sm:col-span-1">
                    <p class="text-xs text-base-content/50 mb-1">Điểm tổng</p>
                    <p class="text-4xl font-bold text-primary">{{ round($result->overall_score, 1) }}</p>
                    <p class="text-xs text-base-content/40 mt-0.5">/ 100</p>
                </div>
                <div>
                    <p class="text-xs text-base-content/50 mb-1">Mức độ trưởng thành</p>
                    <span class="badge badge-lg badge-soft badge-info font-semibold">{{ $result->maturity_level }}</span>
                </div>
                <div>
                    <p class="text-xs text-base-content/50 mb-1">Assessment</p>
                    <p class="font-mono text-sm font-medium text-base-content">{{ $result->assessment_code }}</p>
                </div>
                <div>
                    <p class="text-xs text-base-content/50 mb-1">Tính lúc</p>
                    <p class="text-sm font-medium">{{ $result->calculated_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Domain scores ────────────────────────────────────────────────── --}}
    @if($result->domainScores->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Điểm theo domain</div>
        <div class="divide-y divide-base-200">
            @foreach($result->domainScores as $ds)
            <div class="px-5 py-3 flex items-center gap-4">
                <div class="w-40 shrink-0">
                    <p class="text-sm font-medium text-base-content">{{ $domainLabels[$ds->domain_code] ?? $ds->domain_code }}</p>
                    <p class="text-xs font-mono text-base-content/40">{{ $ds->domain_code }}</p>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-base-200 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-primary transition-all"
                                 style="width: {{ min(100, round($ds->normalized_score)) }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-primary w-10 text-right">{{ round($ds->normalized_score, 1) }}</span>
                    </div>
                </div>
                <div class="text-xs text-base-content/40 w-20 text-right">
                    Raw: {{ $ds->raw_score }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Signal flags ─────────────────────────────────────────────────── --}}
    @if($result->signalFlags->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Signal Flags</div>
        <div class="px-5 py-3 flex flex-wrap gap-2">
            @foreach($result->signalFlags as $flag)
            <span class="badge badge-sm badge-soft {{ $flag->flag_value ? 'badge-success' : 'badge-neutral' }} gap-1">
                <span class="font-mono">{{ $flag->flag_code }}</span>
                <span>{{ $flag->flag_value ? 'true' : 'false' }}</span>
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Pain points ──────────────────────────────────────────────────── --}}
    @if($result->painPoints->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Pain Points</div>
        <div class="px-5 py-3 flex flex-wrap gap-2">
            @foreach($result->painPoints as $pp)
            <span class="badge badge-sm badge-soft badge-warning font-mono">{{ $pp->pain_point_code }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Recommendations ──────────────────────────────────────────────── --}}
    @if($result->recommendations->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Đề xuất</div>
        <div class="divide-y divide-base-200">
            @foreach($result->recommendations as $rec)
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="badge badge-sm badge-soft badge-ghost font-mono text-xs w-8 text-center shrink-0">
                    #{{ $rec->priority }}
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-base-content">
                        {{ $recLabels[$rec->recommendation_code] ?? $rec->recommendation_code }}
                    </p>
                    <p class="text-xs font-mono text-base-content/40">{{ $rec->recommendation_code }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Roadmap ───────────────────────────────────────────────────────── --}}
    @if($result->roadmapPhases->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm">Lộ trình</div>
        <div class="divide-y divide-base-200">
            @foreach($result->roadmapPhases as $rp)
            <div class="px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="badge badge-sm badge-soft badge-primary mt-0.5 shrink-0 font-mono">
                        {{ $rp->phase?->phase_code ?? '?' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-sm text-base-content">{{ $rp->phase?->title ?? '—' }}</p>
                        @if($rp->phase?->duration_weeks)
                        <p class="text-xs text-base-content/50 mt-0.5">{{ $rp->phase->duration_weeks }} tuần</p>
                        @endif
                        @if($rp->phase?->milestones->isNotEmpty())
                        <ul class="mt-2 space-y-1">
                            @foreach($rp->phase->milestones as $ms)
                            <li class="flex items-start gap-1.5 text-xs text-base-content/70">
                                <svg class="w-3.5 h-3.5 text-success mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ $ms->title }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Job Positions ─────────────────────────────────────────────────── --}}
    @php $jobPositions = $result->jobPositions ?? collect(); @endphp
    @if($jobPositions->isNotEmpty())
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="px-5 py-3 border-b border-base-200 font-semibold text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Vị trí công việc phù hợp
        </div>
        <div class="divide-y divide-base-200">
            @foreach($jobPositions as $jp)
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-medium text-sm text-base-content">
                        {{ \Modules\Survey\Models\JobPosition::where('position_code', $jp->position_code)
                            ->where('assessment_code', $result->assessment_code)
                            ->value('title') ?? $jp->position_code }}
                    </p>
                    <p class="text-xs text-base-content/50 font-mono">{{ $jp->position_code }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <span class="text-lg font-bold {{ $jp->match_score >= 80 ? 'text-success' : ($jp->match_score >= 60 ? 'text-primary' : 'text-warning') }}">
                        {{ round($jp->match_score) }}%
                    </span>
                    <p class="text-xs text-base-content/40">khớp</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end $result --}}
</div>

@push('scripts')
<script>
function resultShowPage(responseId, surveyId) {
    return {
        recalculating: false,
        flashMsg: '',
        flashSuccess: true,

        async recalculate() {
            this.recalculating = true;
            this.flashMsg = '';
            try {
                const res = await fetch(
                    `/dashboard/surveys/${surveyId}/responses/${responseId}/recalculate`,
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    }
                );
                const data = await res.json();
                this.flashSuccess = data.success ?? res.ok;
                this.flashMsg = data.message ?? (res.ok ? 'Đã tính lại thành công.' : 'Lỗi.');
                if (res.ok) {
                    setTimeout(() => location.reload(), 1200);
                }
            } catch (e) {
                this.flashSuccess = false;
                this.flashMsg = 'Lỗi kết nối.';
            } finally {
                this.recalculating = false;
            }
        }
    };
}
</script>
@endpush
@endsection

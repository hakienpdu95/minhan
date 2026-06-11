@extends('layouts.backend')
@section('title', 'Hồ sơ Workforce — ' . ($workforceProfile->employee?->full_name ?? 'N/A'))

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

@php
    $emp = $workforceProfile->employee;
    $prof = $workforceProfile;
    $maturityMap = [
        'DIGITAL_BEGINNER'     => ['Khởi đầu số',    'badge-ghost',   '#94a3b8'],
        'DIGITAL_AWARE'        => ['Nhận thức số',   'badge-info',    '#38bdf8'],
        'DIGITAL_PRACTITIONER' => ['Thực hành số',   'badge-warning', '#fbbf24'],
        'DIGITAL_PROFESSIONAL' => ['Chuyên nghiệp',  'badge-success', '#34d399'],
        'DIGITAL_LEADER'       => ['Dẫn dắt số',     'badge-accent',  '#a78bfa'],
    ];
    $ml  = $maturityMap[$prof->tdwcf_maturity_level] ?? ['—', 'badge-ghost', '#94a3b8'];
    $domains = [
        'D1' => ['D1 — Mù chữ số',          $prof->score_d1_digital_literacy, '#6366f1'],
        'D2' => ['D2 — Mù chữ dữ liệu',     $prof->score_d2_data_literacy,    '#0ea5e9'],
        'D3' => ['D3 — Mù chữ AI',           $prof->score_d3_ai_literacy,      '#10b981'],
        'D4' => ['D4 — Quy trình & TĐH',    $prof->score_d4_workflow,          '#f59e0b'],
        'D5' => ['D5 — Đổi mới & Sáng kiến',$prof->score_d5_innovation,        '#ec4899'],
        'D6' => ['D6 — Hiệu suất & KPI',    $prof->score_d6_performance,       '#8b5cf6'],
    ];
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-sm font-bold text-primary shrink-0">
            {{ strtoupper(substr($emp?->full_name ?? 'U', 0, 1)) }}
        </div>
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ $emp?->full_name ?? '—' }}</h1>
            <p class="text-sm text-base-content/50">{{ $emp?->position ?? '' }}{{ $emp?->department ? ' · '.$emp->department : '' }}</p>
        </div>
        <span class="badge {{ $ml[1] }} badge-sm">{{ $ml[0] }}</span>
    </div>
    <a href="{{ route('backend.workforce.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Workforce Admin
    </a>
</div>

{{-- ── Score cards ─────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">TDWCF Score</div>
        <div class="stat-value text-2xl text-primary">{{ $prof->tdwcf_score ? number_format($prof->tdwcf_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Trust Score</div>
        <div class="stat-value text-2xl text-success">{{ $prof->workforce_trust_score ? number_format($prof->workforce_trust_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">AI Readiness</div>
        <div class="stat-value text-2xl text-accent">{{ $prof->ai_readiness_score ? number_format($prof->ai_readiness_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Sandbox sessions</div>
        <div class="stat-value text-2xl">{{ $prof->sandbox_sessions_total ?? 0 }}</div>
        <div class="stat-desc text-xs">{{ round(($prof->sandbox_hours_total ?? 0), 1) }}h</div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Left: 6-domain breakdown --}}
    <div class="xl:col-span-2 card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-4">6 Miền TDWCF</h2>
            <div class="space-y-3">
                @foreach($domains as $key => [$label, $score, $color])
                <div>
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="font-medium">{{ $label }}</span>
                        <span class="font-semibold">{{ $score ? number_format($score, 1) : '—' }}</span>
                    </div>
                    <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                        <div class="h-2 rounded-full transition-all" style="width: {{ min($score ?? 0, 100) }}%; background:{{ $color }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Profile metadata --}}
    <div class="space-y-4">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body py-4 px-5">
                <h3 class="font-semibold text-sm mb-3">Thông tin hồ sơ</h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Cấp độ hiện tại</dt>
                        <dd><span class="badge {{ $ml[1] }} badge-xs">{{ $ml[0] }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Đánh giá cuối</dt>
                        <dd class="font-medium">{{ $prof->tdwcf_assessed_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Điểm sandbox TB</dt>
                        <dd class="font-medium">{{ $prof->sandbox_score_avg ? number_format($prof->sandbox_score_avg, 1) : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Cập nhật</dt>
                        <dd class="font-medium">{{ $prof->updated_at?->diffForHumans() ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($history->count())
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body py-4 px-5">
                <h3 class="font-semibold text-sm mb-3">Lịch sử điểm</h3>
                <div class="space-y-2">
                    @foreach($history->take(6) as $h)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/40">{{ $h->created_at?->format('d/m/Y') }}</span>
                        <span class="font-semibold {{ $h->tdwcf_score >= 70 ? 'text-success' : ($h->tdwcf_score >= 40 ? 'text-warning' : 'text-error') }}">
                            {{ number_format($h->tdwcf_score, 1) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

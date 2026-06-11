@extends('layouts.backend')
@section('title', 'Hồ sơ năng lực số của tôi')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

{{-- ── Page header ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Hồ sơ năng lực số</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Digital Twin — toàn cảnh năng lực AI của bạn trong tổ chức</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('backend.sandbox.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            AI Sandbox
        </a>
        <a href="{{ route('backend.certifications.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            Chứng nhận
        </a>
        <a href="{{ route('backend.career-pathway.index') }}" class="btn btn-primary btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            Lộ trình
        </a>
    </div>
</div>

@if(!$profile)
{{-- Chưa có profile — onboarding state --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body items-center text-center py-16">
        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold mb-2">Chưa có hồ sơ năng lực</h2>
        <p class="text-base-content/50 text-sm max-w-md mb-6">Hoàn thành một khảo sát TDWCF để hệ thống tự động tạo Digital Twin cho bạn.</p>
        <a href="{{ route('backend.surveys.index') }}" class="btn btn-primary btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Làm khảo sát ngay
        </a>
    </div>
</div>
@else

{{-- ── Row 1: KPI cards ─────────────────────────────────────────────────────── --}}
@php
    $maturityLabels = [
        'DIGITAL_BEGINNER'     => ['Khởi đầu số',    'badge-ghost'],
        'DIGITAL_AWARE'        => ['Nhận thức số',   'badge-info'],
        'DIGITAL_PRACTITIONER' => ['Thực hành số',   'badge-warning'],
        'DIGITAL_PROFESSIONAL' => ['Chuyên nghiệp',  'badge-success'],
        'DIGITAL_LEADER'       => ['Dẫn dắt số',     'badge-accent'],
    ];
    $ml = $maturityLabels[$profile->tdwcf_maturity_level] ?? ['—', 'badge-ghost'];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Điểm TDWCF</div>
        <div class="stat-value text-2xl text-primary">{{ $profile->tdwcf_score ? number_format($profile->tdwcf_score, 1) : '—' }}</div>
        <div class="stat-desc">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Điểm Tin tưởng</div>
        <div class="stat-value text-2xl text-secondary">{{ $profile->workforce_trust_score ? number_format($profile->workforce_trust_score, 1) : '—' }}</div>
        <div class="stat-desc">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">AI Readiness</div>
        <div class="stat-value text-2xl text-accent">{{ $profile->ai_readiness_score ? number_format($profile->ai_readiness_score, 1) : '—' }}</div>
        <div class="stat-desc">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Sandbox</div>
        <div class="stat-value text-2xl">{{ $sandboxStats?->minutes ? round($sandboxStats->minutes / 60, 1) : ($profile->sandbox_hours_total ?? 0) }} <span class="text-base font-normal">h</span></div>
        <div class="stat-desc">{{ $profile->sandbox_sessions_total ?? 0 }} phiên hoàn thành</div>
    </div>
</div>

{{-- ── Row 2: 2-column layout ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- 6 Domain scores --}}
    <div class="lg:col-span-2 card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-5">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                6 Năng lực TDWCF
            </h2>
            @php
                $domains = [
                    ['code' => 'D1', 'field' => 'score_d1_digital_literacy', 'label' => 'D1 — Mù chữ số',         'color' => 'bg-blue-500'],
                    ['code' => 'D2', 'field' => 'score_d2_data_literacy',    'label' => 'D2 — Mù chữ dữ liệu',    'color' => 'bg-indigo-500'],
                    ['code' => 'D3', 'field' => 'score_d3_ai_literacy',      'label' => 'D3 — Mù chữ AI',          'color' => 'bg-violet-500'],
                    ['code' => 'D4', 'field' => 'score_d4_workflow',         'label' => 'D4 — Quy trình & TĐH',   'color' => 'bg-purple-500'],
                    ['code' => 'D5', 'field' => 'score_d5_innovation',       'label' => 'D5 — Đổi mới & Sáng kiến', 'color' => 'bg-fuchsia-500'],
                    ['code' => 'D6', 'field' => 'score_d6_performance',      'label' => 'D6 — Hiệu suất & KPI',   'color' => 'bg-pink-500'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($domains as $d)
                @php $score = $profile->{$d['field']} ?? 0; @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-base-content/70">{{ $d['label'] }}</span>
                        <span class="font-semibold text-base-content">{{ $score ? number_format($score, 1) : '—' }}</span>
                    </div>
                    <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                        <div class="{{ $d['color'] }} h-2 rounded-full transition-all duration-500"
                             style="width: {{ min($score, 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Maturity Level + Quick info --}}
    <div class="space-y-4">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Cấp độ hiện tại</p>
                <div class="text-center py-3">
                    <span class="badge {{ $ml[1] }} badge-lg text-sm font-semibold px-4 py-3">{{ $ml[0] }}</span>
                    <p class="text-xs text-base-content/40 mt-3">
                        Cập nhật {{ $profile->updated_at?->diffForHumans() ?? '—' }}
                    </p>
                </div>
                @if($currentPathwayStep)
                <div class="divider my-2 text-xs text-base-content/30">Bước tiếp theo</div>
                <div class="text-sm">
                    <p class="font-medium text-base-content/80">{{ $currentPathwayStep->title }}</p>
                    <p class="text-xs text-base-content/50 mt-1">~{{ $currentPathwayStep->estimated_weeks }} tuần</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Sandbox</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Điểm TB</span>
                        <span class="font-semibold">{{ $profile->sandbox_score_avg ? number_format($profile->sandbox_score_avg, 1) : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Phiên hoàn thành</span>
                        <span class="font-semibold">{{ $profile->sandbox_sessions_total ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Tổng giờ</span>
                        <span class="font-semibold">{{ $profile->sandbox_hours_total ?? 0 }}h</span>
                    </div>
                </div>
                <a href="{{ route('backend.sandbox.index') }}" class="btn btn-ghost btn-xs mt-3 w-full">Xem Sandbox →</a>
            </div>
        </div>
    </div>

</div>

{{-- ── Row 3: Certifications ───────────────────────────────────────────────── --}}
@if($certifications->count())
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h2 class="card-title text-base">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Chứng nhận đã đạt
            </h2>
            <a href="{{ route('backend.certifications.index') }}" class="btn btn-ghost btn-xs">Xem tất cả →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($certifications->take(6) as $cert)
            @php
                $statusClass = match($cert->status) {
                    'active'  => 'border-success text-success',
                    'expired' => 'border-error text-error',
                    default   => 'border-base-300 text-base-content/50',
                };
                $levelBadge = match($cert->definition?->level_code ?? '') {
                    'FOUNDATION'   => 'badge-info',
                    'PRACTITIONER' => 'badge-warning',
                    'PROFESSIONAL' => 'badge-success',
                    'LEADER'       => 'badge-accent',
                    default        => 'badge-ghost',
                };
            @endphp
            <div class="border {{ $statusClass }} rounded-xl p-3 flex items-start gap-3">
                <svg class="w-8 h-8 shrink-0 mt-0.5 {{ $statusClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <div>
                    <p class="text-sm font-semibold leading-snug">{{ $cert->definition?->name ?? $cert->cert_code }}</p>
                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                        <span class="badge {{ $levelBadge }} badge-xs">{{ $cert->definition?->level_code ?? '—' }}</span>
                        <span class="text-xs text-base-content/40">Cấp {{ $cert->issued_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    @if($cert->expires_at)
                    <p class="text-xs text-base-content/40 mt-0.5">HH: {{ $cert->expires_at->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Row 4: Recent activity ──────────────────────────────────────────────── --}}
@if($recentHistory->count())
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-5">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Hoạt động gần đây
        </h2>
        <div class="space-y-3">
            @foreach($recentHistory as $event)
            @php
                $icon = match($event->event_type) {
                    'assessment'       => ['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'text-info'],
                    'sandbox'          => ['M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'text-warning'],
                    'certification'    => ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text-success'],
                    default            => ['M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text-base-content/40'],
                };
            @endphp
            <div class="flex items-start gap-3">
                <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $icon[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon[0] }}"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-base-content/80">{{ $event->notes }}</p>
                    <p class="text-xs text-base-content/40 mt-0.5">{{ $event->recorded_at?->diffForHumans() ?? '—' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endif {{-- end if profile --}}

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

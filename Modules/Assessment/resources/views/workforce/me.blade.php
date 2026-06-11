@extends('layouts.backend')
@section('title', 'Hồ sơ năng lực số của tôi')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success mb-4 py-2 px-4 text-sm">{{ session('success') }}</div>
@endif

{{-- ── Page header ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Hồ sơ năng lực số</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Digital Twin — toàn cảnh năng lực AI của bạn trong tổ chức</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('backend.ai-impact.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            AI Impact
        </a>
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
{{-- ── Chưa có profile ───────────────────────────────────────────────────────── --}}
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

{{-- ── Profile completeness bar ─────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5">
    <div class="card-body py-3 px-5">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-medium text-base-content/60">Độ hoàn thiện hồ sơ</span>
                    <span class="font-bold {{ $completeness >= 80 ? 'text-success' : ($completeness >= 40 ? 'text-warning' : 'text-base-content/50') }}">{{ $completeness }}%</span>
                </div>
                <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                    <div class="h-2 rounded-full transition-all {{ $completeness >= 80 ? 'bg-success' : ($completeness >= 40 ? 'bg-warning' : 'bg-base-content/30') }}"
                         style="width: {{ $completeness }}%"></div>
                </div>
            </div>
            <div class="flex gap-2 shrink-0 text-xs text-base-content/40">
                @foreach([
                    [$profile->tdwcf_score,            'TDWCF'],
                    [$profile->certifications_count,   'Cert'],
                    [$profile->sandbox_sessions_total, 'Sandbox'],
                    [$profile->impact_score,           'Impact'],
                    [$profile->kpi_achievement_avg,    'KPI'],
                ] as [$val, $lbl])
                <div class="flex flex-col items-center gap-0.5">
                    <div class="w-5 h-5 rounded-full {{ $val ? 'bg-success/20 text-success' : 'bg-base-200 text-base-content/20' }} flex items-center justify-center">
                        @if($val)
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </div>
                    <span>{{ $lbl }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── KPI cards (6 slots) ──────────────────────────────────────────────────── --}}
@php
    $maturityLabels = [
        'DIGITAL_BEGINNER'     => ['Khởi đầu số',   'badge-ghost'],
        'DIGITAL_AWARE'        => ['Nhận thức số',  'badge-info'],
        'DIGITAL_PRACTITIONER' => ['Thực hành số',  'badge-warning'],
        'DIGITAL_PROFESSIONAL' => ['Chuyên nghiệp', 'badge-success'],
        'DIGITAL_LEADER'       => ['Dẫn dắt số',    'badge-accent'],
    ];
    $ml = $maturityLabels[$profile->tdwcf_maturity_level] ?? ['—', 'badge-ghost'];
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Điểm TDWCF</div>
        <div class="stat-value text-2xl text-primary">{{ $profile->tdwcf_score ? number_format($profile->tdwcf_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">Cấp: <span class="badge {{ $ml[1] }} badge-xs">{{ $ml[0] }}</span></div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Trust Score</div>
        <div class="stat-value text-2xl text-secondary">{{ $profile->workforce_trust_score ? number_format($profile->workforce_trust_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">/ 100</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">AI Readiness</div>
        <div class="stat-value text-2xl text-accent">{{ $profile->ai_readiness_score ? number_format($profile->ai_readiness_score, 1) : '—' }}</div>
        <div class="stat-desc text-xs">(D3+D4) / 2</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">AI Impact (AII)</div>
        <div class="stat-value text-2xl {{ ($profile->impact_score ?? 0) >= 50 ? 'text-success' : 'text-base-content/60' }}">
            {{ $profile->impact_score ? number_format($profile->impact_score, 1) : '—' }}
        </div>
        <div class="stat-desc text-xs"><a href="{{ route('backend.ai-impact.index') }}" class="link link-hover text-xs">Xem chi tiết →</a></div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">KPI TB</div>
        <div class="stat-value text-2xl {{ ($profile->kpi_achievement_avg ?? 0) >= 80 ? 'text-success' : 'text-base-content/60' }}">
            {{ $profile->kpi_achievement_avg ? number_format($profile->kpi_achievement_avg, 1).'%' : '—' }}
        </div>
        <div class="stat-desc text-xs">Performance review</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Sandbox</div>
        <div class="stat-value text-2xl">{{ $profile->sandbox_hours_total ?? 0 }}<span class="text-base font-normal">h</span></div>
        <div class="stat-desc text-xs">{{ $profile->sandbox_sessions_total ?? 0 }} phiên</div>
    </div>
</div>

{{-- ── Row 2: 6 domains + Trust breakdown + Maturity ──────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- 6 Domain scores --}}
    <div class="lg:col-span-2 card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-base">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    6 Năng lực TDWCF
                </h2>
                <p class="text-xs text-base-content/30">Cập nhật {{ $profile->tdwcf_assessed_at?->diffForHumans() ?? 'chưa có' }}</p>
            </div>
            @php
                $domains = [
                    ['field'=>'score_d1_digital_literacy','label'=>'D1 — Năng lực số cơ bản',       'desc'=>'Sử dụng công cụ kỹ thuật số, bảo mật, thiết bị',    'color'=>'bg-blue-500'],
                    ['field'=>'score_d2_data_literacy',   'label'=>'D2 — Năng lực dữ liệu',         'desc'=>'Đọc, phân tích và trình bày dữ liệu',               'color'=>'bg-indigo-500'],
                    ['field'=>'score_d3_ai_literacy',     'label'=>'D3 — Năng lực AI',               'desc'=>'Hiểu & ứng dụng AI vào công việc',                  'color'=>'bg-violet-500'],
                    ['field'=>'score_d4_workflow',        'label'=>'D4 — Quy trình & Tự động hoá',  'desc'=>'Tự động hoá task, tối ưu quy trình',                'color'=>'bg-purple-500'],
                    ['field'=>'score_d5_innovation',      'label'=>'D5 — Đổi mới & Sáng kiến',      'desc'=>'Tư duy sáng tạo, đề xuất cải tiến',                'color'=>'bg-fuchsia-500'],
                    ['field'=>'score_d6_performance',     'label'=>'D6 — Hiệu suất & KPI',           'desc'=>'Đạt mục tiêu, đo lường kết quả',                   'color'=>'bg-pink-500'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($domains as $d)
                @php $score = $profile->{$d['field']} ?? 0; $hasScore = $profile->{$d['field']} !== null; @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-base-content/70" title="{{ $d['desc'] }}">{{ $d['label'] }}</span>
                        <span class="font-semibold {{ $score >= 70 ? 'text-success' : ($score >= 40 ? 'text-warning' : ($hasScore ? 'text-error' : 'text-base-content/30')) }}">
                            {{ $hasScore ? number_format($score, 1) : '—' }}
                        </span>
                    </div>
                    <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                        <div class="{{ $d['color'] }} h-2 rounded-full transition-all duration-500"
                             style="width: {{ min($score, 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @if(! $profile->tdwcf_score)
            <div class="mt-4 p-3 bg-info/5 border border-info/20 rounded-lg text-xs text-base-content/60">
                Chưa có điểm TDWCF. <a href="{{ route('backend.surveys.index') }}" class="link link-primary">Làm khảo sát TDWCF</a> để cập nhật 6 năng lực.
            </div>
            @endif
        </div>
    </div>

    {{-- Right column: Maturity + Trust Breakdown --}}
    <div class="space-y-4">

        {{-- Maturity level card --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Cấp độ trưởng thành</p>
                <div class="text-center py-2">
                    <span class="badge {{ $ml[1] }} badge-lg text-sm font-semibold px-4 py-3">{{ $ml[0] }}</span>
                </div>
                @if($currentPathwayStep)
                <div class="divider my-2 text-xs text-base-content/30">Bước tiếp theo</div>
                <p class="text-sm font-medium text-base-content/80">{{ $currentPathwayStep->title }}</p>
                <p class="text-xs text-base-content/40 mt-1">~{{ $currentPathwayStep->estimated_weeks }} tuần · <a href="{{ route('backend.career-pathway.index') }}" class="link link-hover">Xem lộ trình</a></p>
                @endif
            </div>
        </div>

        {{-- Trust Score breakdown --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">
                    Trust Score = {{ $profile->workforce_trust_score ? number_format($profile->workforce_trust_score, 1) : '—' }}
                </p>
                <div class="space-y-2">
                    @foreach($trustBreakdown as $item)
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-base-content/50 w-20 shrink-0">{{ $item['label'] }}</span>
                        <div class="flex-1 h-1.5 bg-base-200 rounded-full overflow-hidden">
                            <div class="h-1.5 bg-secondary/60 rounded-full"
                                 style="width: {{ $item['raw'] > 0 ? min($item['raw'], 100) : 0 }}%"></div>
                        </div>
                        <span class="font-mono text-base-content/60 w-8 text-right">×{{ $item['weight'] }}%</span>
                        <span class="font-semibold w-8 text-right text-secondary">{{ $item['contribution'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Career goal (editable) ─────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5"
     x-data="{ editing: false }">
    <div class="card-body p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="card-title text-base">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Mục tiêu nghề nghiệp
            </h2>
            <button @click="editing = !editing" class="btn btn-ghost btn-xs gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span x-text="editing ? 'Huỷ' : 'Chỉnh sửa'"></span>
            </button>
        </div>

        {{-- Read mode --}}
        <div x-show="!editing">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-base-content/40 mb-1">Mục tiêu</p>
                    <p class="text-sm {{ $profile->career_goal ? 'text-base-content/80' : 'text-base-content/30 italic' }}">
                        {{ $profile->career_goal ?? 'Chưa đặt mục tiêu nghề nghiệp' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-base-content/40 mb-1">Lộ trình đang học</p>
                    <p class="text-sm {{ $profile->current_learning_path ? 'text-base-content/80' : 'text-base-content/30 italic' }}">
                        {{ $profile->current_learning_path ?? 'Chưa có lộ trình học tập' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Edit mode --}}
        <div x-show="editing" x-cloak>
            <form method="POST" action="{{ route('backend.workforce.me.goal') }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Mục tiêu nghề nghiệp</span></label>
                        <textarea name="career_goal" rows="3" class="textarea textarea-bordered text-sm"
                                  placeholder="VD: Trở thành AI Practitioner trong 6 tháng...">{{ $profile->career_goal }}</textarea>
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Lộ trình đang học</span></label>
                        <input type="text" name="current_learning_path" class="input input-bordered input-sm"
                               value="{{ $profile->current_learning_path }}"
                               placeholder="VD: AI for Business — Coursera">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                    <button type="button" @click="editing = false" class="btn btn-ghost btn-sm">Huỷ</button>
                </div>
            </form>
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
                Chứng nhận đã đạt ({{ $certifications->count() }})
            </h2>
            <a href="{{ route('backend.certifications.index') }}" class="btn btn-ghost btn-xs">Xem tất cả →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($certifications->take(6) as $cert)
            @php
                $isActive   = $cert->status === 'active';
                $isExpired  = $cert->status === 'expired';
                $borderCls  = $isActive ? 'border-success text-success' : ($isExpired ? 'border-error text-error' : 'border-base-300 text-base-content/50');
                $levelBadge = match($cert->definition?->level_code ?? '') {
                    'FOUNDATION'   => 'badge-info',
                    'PRACTITIONER' => 'badge-warning',
                    'PROFESSIONAL' => 'badge-success',
                    'LEADER'       => 'badge-accent',
                    default        => 'badge-ghost',
                };
            @endphp
            <div class="border {{ $borderCls }} rounded-xl p-3 flex items-start gap-3">
                <svg class="w-7 h-7 shrink-0 mt-0.5 {{ $borderCls }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <div>
                    <p class="text-sm font-semibold leading-snug">{{ $cert->definition?->name ?? '—' }}</p>
                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                        <span class="badge {{ $levelBadge }} badge-xs">{{ $cert->definition?->level_code ?? '—' }}</span>
                        <span class="text-xs text-base-content/40">{{ $cert->issued_at?->format('d/m/Y') }}</span>
                    </div>
                    @if($cert->expires_at)
                    <p class="text-xs {{ $isExpired ? 'text-error' : 'text-base-content/30' }} mt-0.5">
                        HH: {{ $cert->expires_at->format('d/m/Y') }}
                        @if($isActive && $cert->expires_at->diffInDays() < 30)
                        <span class="badge badge-warning badge-xs ml-1">Sắp hết hạn</span>
                        @endif
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Portfolio ─────────────────────────────────────────────────────────────── --}}
@if($portfolios->count())
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h2 class="card-title text-base">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Portfolio ({{ $portfolios->count() }})
            </h2>
            @php $approvedCount = $portfolios->where('approval_status','approved')->count(); @endphp
            <span class="text-xs text-base-content/40">{{ $approvedCount }} được duyệt</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($portfolios->take(4) as $item)
            @php
                $statusCls = match($item->approval_status) {
                    'approved' => ['badge-success', 'Đã duyệt'],
                    'rejected' => ['badge-error', 'Từ chối'],
                    default    => ['badge-ghost', 'Chờ duyệt'],
                };
                $typeCls = match($item->item_type) {
                    'assessment_result' => 'badge-info',
                    'sandbox_result'    => 'badge-warning',
                    'impact_data'       => 'badge-success',
                    default             => 'badge-ghost',
                };
            @endphp
            <div class="border border-base-200 rounded-xl p-3">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium leading-snug">{{ $item->title }}</p>
                    <span class="badge {{ $statusCls[0] }} badge-xs shrink-0">{{ $statusCls[1] }}</span>
                </div>
                <div class="flex gap-1.5 mt-1.5">
                    <span class="badge {{ $typeCls }} badge-xs">{{ $item->item_type }}</span>
                </div>
                @if($item->evidence_url)
                <a href="{{ $item->evidence_url }}" target="_blank" class="text-xs link link-primary mt-1 inline-block">Xem bằng chứng →</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Recent activity ──────────────────────────────────────────────────────── --}}
@if($recentHistory->count())
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-4">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Hoạt động gần đây
        </h2>
        <div class="space-y-3">
            @foreach($recentHistory as $event)
            @php
                $iconPath = match($event->event_type) {
                    'assessment'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                    'sandbox'           => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                    'certification'     => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'impact'            => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    'performance_review'=> 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    default             => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                };
                $iconCls = match($event->event_type) {
                    'assessment'         => 'text-info',
                    'sandbox'            => 'text-warning',
                    'certification'      => 'text-success',
                    'impact'             => 'text-accent',
                    'performance_review' => 'text-primary',
                    default              => 'text-base-content/30',
                };
            @endphp
            <div class="flex items-start gap-3">
                <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $iconCls }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $iconPath }}"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-base-content/80">{{ $event->notes }}</p>
                    @if($event->change_delta)
                    <span class="text-xs {{ $event->change_delta >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $event->change_delta >= 0 ? '+' : '' }}{{ number_format($event->change_delta, 1) }} điểm
                    </span>
                    @endif
                    <p class="text-xs text-base-content/30 mt-0.5">{{ $event->recorded_at?->diffForHumans() ?? '—' }}</p>
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

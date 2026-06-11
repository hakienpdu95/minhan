@extends('layouts.backend')
@section('title', 'Chứng nhận năng lực AI')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Chứng nhận AI Workforce</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Hành trình chứng nhận năng lực AI của bạn</p>
    </div>
    <a href="{{ route('backend.workforce.me') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Hồ sơ của tôi
    </a>
</div>

{{-- ── Earned certifications ────────────────────────────────────────────────── --}}
@if($certifications->count())
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5">
    <div class="card-body">
        <h2 class="card-title text-base mb-5">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Chứng nhận của tôi ({{ $certifications->count() }})
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($certifications as $cert)
            @php
                $isActive  = $cert->status === 'active';
                $isExpired = $cert->status === 'expired';
                $levelColor = match($cert->definition?->level_code ?? '') {
                    'FOUNDATION'   => 'border-info bg-info/5',
                    'PRACTITIONER' => 'border-warning bg-warning/5',
                    'PROFESSIONAL' => 'border-success bg-success/5',
                    'LEADER'       => 'border-accent bg-accent/5',
                    default        => 'border-base-300',
                };
                $statusBadge = $isActive ? ['Hiệu lực', 'badge-success'] : ($isExpired ? ['Hết hạn', 'badge-error'] : ['Chờ xử lý', 'badge-ghost']);
            @endphp
            <div class="border {{ $levelColor }} rounded-xl p-5 flex gap-4">
                <div class="shrink-0">
                    <div class="w-12 h-12 rounded-xl {{ $isActive ? 'bg-success/10' : 'bg-base-200' }} flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $isActive ? 'text-success' : 'text-base-content/30' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-sm leading-snug">{{ $cert->definition?->name ?? $cert->cert_code }}</h3>
                        <span class="badge {{ $statusBadge[1] }} badge-xs shrink-0">{{ $statusBadge[0] }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        @if($cert->definition?->level_code)
                        @php
                            $levelBadge = match($cert->definition->level_code) {
                                'FOUNDATION'   => ['Foundation', 'badge-info'],
                                'PRACTITIONER' => ['Practitioner', 'badge-warning'],
                                'PROFESSIONAL' => ['Professional', 'badge-success'],
                                'LEADER'       => ['Leader', 'badge-accent'],
                                default        => [$cert->definition->level_code, 'badge-ghost'],
                            };
                        @endphp
                        <span class="badge {{ $levelBadge[1] }} badge-xs">{{ $levelBadge[0] }}</span>
                        @endif
                        <span class="text-xs text-base-content/40">{{ $cert->definition?->cert_type_code ?? '—' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 mt-3 text-xs text-base-content/50">
                        <div>
                            <span class="block text-base-content/30">Cấp ngày</span>
                            <span class="font-medium text-base-content/70">{{ $cert->issued_at?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-base-content/30">Hết hạn</span>
                            <span class="font-medium {{ $isExpired ? 'text-error' : 'text-base-content/70' }}">
                                {{ $cert->expires_at?->format('d/m/Y') ?? 'Không hạn' }}
                            </span>
                        </div>
                    </div>
                    @if($cert->composite_score)
                    <div class="mt-2">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-base-content/40">Điểm tổng hợp</span>
                            <span class="font-semibold">{{ number_format($cert->composite_score, 1) }}</span>
                        </div>
                        <div class="h-1.5 bg-base-200 rounded-full overflow-hidden">
                            <div class="h-1.5 bg-success rounded-full" style="width: {{ min($cert->composite_score, 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                    @if($cert->certificate_number)
                    <p class="text-xs text-base-content/30 mt-2 font-mono">{{ $cert->certificate_number }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Available certifications ─────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-5">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Lộ trình chứng nhận
        </h2>
        @php
            $grouped = $available->groupBy('cert_type_code');
            $levels = ['FOUNDATION', 'PRACTITIONER', 'PROFESSIONAL', 'LEADER'];
            $levelLabels = ['FOUNDATION'=>'Foundation','PRACTITIONER'=>'Practitioner','PROFESSIONAL'=>'Professional','LEADER'=>'Leader'];
        @endphp
        @foreach($grouped as $typeCode => $defs)
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-3">{{ $typeCode }}</h3>
            <div class="flex flex-wrap items-center gap-2">
                @foreach($levels as $lvl)
                @php
                    $def     = $defs->firstWhere('level_code', $lvl);
                    $earned  = $def && in_array($def->cert_code, $earnedCodes);
                    $btnClass = $earned
                        ? 'bg-success/10 border-success text-success'
                        : 'bg-base-200 border-base-300 text-base-content/40';
                @endphp
                <div class="flex items-center">
                    <div class="border {{ $btnClass }} rounded-lg px-3 py-2 text-center min-w-28">
                        @if($earned)
                        <svg class="w-4 h-4 mx-auto mb-1 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        @else
                        <div class="w-4 h-4 mx-auto mb-1 rounded-full border-2 border-current opacity-30"></div>
                        @endif
                        <p class="text-xs font-medium">{{ $levelLabels[$lvl] ?? $lvl }}</p>
                        @if($def)
                        <p class="text-xs opacity-50">≥ {{ $def->min_workforce_score }}</p>
                        @endif
                    </div>
                    @if(!$loop->last)
                    <svg class="w-4 h-4 mx-1 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

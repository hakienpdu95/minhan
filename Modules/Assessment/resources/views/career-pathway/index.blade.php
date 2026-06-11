@extends('layouts.backend')
@section('title', 'Lộ trình nghề nghiệp')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Lộ trình nghề nghiệp số</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Từ Khởi đầu đến Dẫn dắt chuyển đổi — {{ $profile ? 'hành trình cá nhân của bạn' : 'tổng quan lộ trình' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('backend.sandbox.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            AI Sandbox
        </a>
        <a href="{{ route('backend.workforce.me') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Hồ sơ của tôi
        </a>
    </div>
</div>

{{-- ── Current level banner --}}
@if($profile)
@php
    $maturityLabels = [
        'DIGITAL_BEGINNER'     => ['Khởi đầu số',    'alert-info'],
        'DIGITAL_AWARE'        => ['Nhận thức số',   'alert-info'],
        'DIGITAL_PRACTITIONER' => ['Thực hành số',   'alert-warning'],
        'DIGITAL_PROFESSIONAL' => ['Chuyên nghiệp',  'alert-success'],
        'DIGITAL_LEADER'       => ['Dẫn dắt số',     'alert-success'],
    ];
    $ml = $maturityLabels[$currentLevel] ?? ['—', 'alert-info'];
@endphp
<div class="alert {{ $ml[1] }} mb-6 py-3 px-5">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="text-sm">Cấp độ hiện tại của bạn: <strong>{{ $ml[0] }}</strong> (TDWCF: {{ $profile->tdwcf_score ? number_format($profile->tdwcf_score, 1) : '—' }}/100)</span>
</div>
@endif

{{-- ── Steps timeline ───────────────────────────────────────────────────────── --}}
<div class="space-y-4">
    @foreach($steps as $step)
    @php
        $fromOrder   = $levelOrder[$step->from_level] ?? 0;
        $isDone      = $fromOrder < $currentOrder;
        $isCurrent   = $step->from_level === $currentLevel;
        $isLocked    = $fromOrder > $currentOrder;

        $cardClass = match(true) {
            $isDone    => 'border-success/30 bg-success/5',
            $isCurrent => 'border-primary shadow-md',
            default    => 'border-base-200 opacity-60',
        };
        $dotClass = match(true) {
            $isDone    => 'bg-success text-success-content',
            $isCurrent => 'bg-primary text-primary-content ring-4 ring-primary/20',
            default    => 'bg-base-300 text-base-content/30',
        };
    @endphp
    <div class="relative">
        {{-- Connector line --}}
        @if(!$loop->last)
        <div class="absolute left-5 top-12 w-0.5 h-full {{ $isDone ? 'bg-success/40' : 'bg-base-200' }} -translate-x-1/2 z-0"></div>
        @endif

        <div class="card border {{ $cardClass }} bg-base-100 relative z-10">
            <div class="card-body p-5">
                <div class="flex items-start gap-4">
                    {{-- Step dot --}}
                    <div class="w-10 h-10 rounded-full {{ $dotClass }} flex items-center justify-center shrink-0 font-bold text-sm">
                        @if($isDone)
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                        {{ $step->step_order }}
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-base-content {{ $isLocked ? 'text-base-content/40' : '' }}">
                                    {{ $step->title }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="badge badge-ghost badge-xs">{{ $step->from_level }}</span>
                                    @if($step->from_level !== $step->to_level)
                                    <svg class="w-3 h-3 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span class="badge badge-ghost badge-xs">{{ $step->to_level }}</span>
                                    @endif
                                    <span class="text-xs text-base-content/40">~{{ $step->estimated_weeks }} tuần</span>
                                </div>
                            </div>
                            @if($isCurrent)
                            <span class="badge badge-primary badge-sm shrink-0">Bước hiện tại</span>
                            @elseif($isDone)
                            <span class="badge badge-success badge-sm shrink-0">Hoàn thành</span>
                            @endif
                        </div>

                        <p class="text-sm text-base-content/60 mt-2">{{ $step->description }}</p>

                        @if(!$isLocked)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                            @if($step->required_cert_code)
                            <div class="flex items-center gap-2 text-xs">
                                <svg class="w-3.5 h-3.5 text-warning shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                <span class="text-base-content/60">Cần chứng nhận: <span class="font-medium">{{ $step->required_cert_code }}</span></span>
                            </div>
                            @endif
                            @if($step->recommended_sandbox_env_code)
                            <div class="flex items-center gap-2 text-xs">
                                <svg class="w-3.5 h-3.5 text-info shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                <span class="text-base-content/60">Sandbox: <span class="font-medium">{{ $step->recommended_sandbox_env_code }}</span></span>
                            </div>
                            @endif
                            @if($step->recommended_kc_tag)
                            <div class="flex items-center gap-2 text-xs">
                                <svg class="w-3.5 h-3.5 text-accent shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span class="text-base-content/60">Tài liệu: <span class="font-medium">{{ Str::limit($step->recommended_kc_tag, 30) }}</span></span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

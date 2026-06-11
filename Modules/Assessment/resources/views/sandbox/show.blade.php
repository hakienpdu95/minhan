@extends('layouts.backend')
@section('title', 'Chi tiết phiên Sandbox')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

@php
    $statusMap = [
        'completed'   => $session->passed ? ['Đạt', 'badge-success'] : ['Không đạt', 'badge-error'],
        'in_progress' => ['Đang làm', 'badge-warning'],
        'expired'     => ['Hết hạn', 'badge-ghost'],
        'submitted'   => ['Chờ chấm', 'badge-info'],
    ];
    $st = $statusMap[$session->status] ?? [$session->status, 'badge-ghost'];
@endphp

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-base-content">{{ $session->task?->title ?? 'Phiên Sandbox' }}</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="text-sm text-base-content/50">{{ $session->task?->environment?->name ?? '—' }}</span>
            <span class="badge {{ $st[1] }} badge-sm">{{ $st[0] }}</span>
        </div>
    </div>
    <a href="{{ route('backend.sandbox.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Sandbox
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- ── Left: Task + Submission ─────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Task info --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base mb-4">Nhiệm vụ</h2>
                @if($session->task?->instruction)
                <div class="prose prose-sm max-w-none mb-4">
                    <p class="text-base-content/70 whitespace-pre-line">{{ $session->task->instruction }}</p>
                </div>
                @endif

                @if($session->task?->expected_output)
                <div class="bg-base-200/60 rounded-xl p-4 mb-4">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-1">Kết quả mong đợi</p>
                    <p class="text-sm text-base-content/70 whitespace-pre-line">{{ $session->task->expected_output }}</p>
                </div>
                @endif

                @if($session->task && count($session->task->scoringRubricItems()))
                <div>
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-2">Tiêu chí chấm điểm</p>
                    <ul class="space-y-1">
                        @foreach($session->task->scoringRubricItems() as $item)
                        <li class="flex items-start gap-2 text-sm text-base-content/70">
                            <svg class="w-3.5 h-3.5 text-primary shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($session->task && count($session->task->allowedAiTools()))
                <div class="flex flex-wrap gap-1.5 mt-3">
                    <span class="text-xs text-base-content/40">AI cho phép:</span>
                    @foreach($session->task->allowedAiTools() as $tool)
                    <span class="badge badge-ghost badge-xs">{{ $tool }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Submission --}}
        @if($session->submission)
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base mb-4">Bài nộp</h2>
                @if($session->submission->submitted_content)
                <div class="bg-base-200/50 rounded-xl p-4 text-sm text-base-content/80 whitespace-pre-line font-mono">{{ $session->submission->submitted_content }}</div>
                @endif

                @php $usedTools = $session->submission->usedAiTools(); @endphp
                @if(count($usedTools))
                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                    <span class="text-xs text-base-content/40">AI đã dùng:</span>
                    @foreach($usedTools as $tool)
                    <span class="badge badge-info badge-xs">{{ $tool }}</span>
                    @endforeach
                </div>
                @endif

                <p class="text-xs text-base-content/30 mt-2">Nộp lúc {{ $session->submission->submitted_at?->format('H:i d/m/Y') ?? '—' }}</p>
            </div>
        </div>
        @endif

        {{-- Activity timeline --}}
        @if($session->activities->count())
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-base mb-5">Nhật ký hoạt động ({{ $session->activities->count() }})</h2>
                <div class="space-y-3">
                    @foreach($session->activities as $act)
                    @php
                        $actIcon = match($act->activity_type) {
                            'start'      => ['M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', 'text-primary'],
                            'ai_use'     => ['M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'text-info'],
                            'edit'       => ['M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'text-warning'],
                            'submit'     => ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text-success'],
                            default      => ['M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text-base-content/30'],
                        };
                    @endphp
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $actIcon[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $actIcon[0] }}"/>
                        </svg>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm text-base-content/80">{{ $act->activity_description }}</p>
                                <span class="text-xs text-base-content/30 shrink-0">{{ $act->occurred_at?->format('H:i:s') }}</span>
                            </div>
                            @if($act->ai_tool_used)
                            <span class="badge badge-ghost badge-xs mt-1">{{ $act->ai_tool_used }}</span>
                            @endif
                            @if($act->quality_note)
                            <div class="flex gap-0.5 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3 h-3 {{ $i <= $act->quality_note ? 'text-warning' : 'text-base-content/20' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right: Scores ───────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Score breakdown --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h3 class="font-semibold text-sm mb-4">Kết quả chấm điểm</h3>
                <div class="text-center mb-4">
                    <span class="text-4xl font-black {{ $session->passed ? 'text-success' : ($session->final_score !== null ? 'text-error' : 'text-base-content/20') }}">
                        {{ $session->final_score !== null ? number_format($session->final_score, 1) : '—' }}
                    </span>
                    <span class="text-base-content/30 text-sm"> / 100</span>
                    <p class="text-xs text-base-content/40 mt-1">{{ $st[0] }}</p>
                </div>
                <div class="space-y-2 text-sm">
                    @foreach([
                        ['Chất lượng (40%)',       $session->quality_score,      '#6366f1'],
                        ['Năng suất (35%)',         $session->productivity_score, '#0ea5e9'],
                        ['Ứng dụng AI (25%)',       $session->ai_adoption_score,  '#10b981'],
                    ] as [$label, $score, $color])
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-base-content/60">{{ $label }}</span>
                            <span class="font-semibold">{{ $score !== null ? number_format($score, 1) : '—' }}</span>
                        </div>
                        <div class="h-1.5 bg-base-200 rounded-full overflow-hidden">
                            <div class="h-1.5 rounded-full" style="width: {{ min($score ?? 0, 100) }}%; background: {{ $color }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($session->feedback)
                <div class="mt-4 pt-4 border-t border-base-200">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-1">Nhận xét</p>
                    <p class="text-sm text-base-content/70">{{ $session->feedback }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Meta --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h3 class="font-semibold text-sm mb-3">Thông tin phiên</h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Bắt đầu</dt>
                        <dd>{{ $session->started_at?->format('H:i d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Nộp bài</dt>
                        <dd>{{ $session->submitted_at?->format('H:i d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Thời gian làm</dt>
                        <dd>{{ $session->duration_minutes ? $session->duration_minutes . ' phút' : '—' }}</dd>
                    </div>
                    @if($session->task?->time_limit_minutes)
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Giới hạn</dt>
                        <dd>{{ $session->task->time_limit_minutes }} phút</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-base-content/40">Hoạt động</dt>
                        <dd>{{ $session->activities->count() }} bước</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

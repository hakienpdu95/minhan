@extends('layouts.backend')
@section('title', 'AI Sandbox — Thực hành năng lực')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">AI Sandbox</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Thực hành ứng dụng AI trong môi trường mô phỏng an toàn</p>
    </div>
    <a href="{{ route('backend.workforce.me') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Hồ sơ của tôi
    </a>
</div>

{{-- ── Stat cards ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Phiên hoàn thành</div>
        <div class="stat-value text-2xl text-primary">{{ $stats['total'] }}</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Tổng giờ luyện tập</div>
        <div class="stat-value text-2xl">{{ $stats['hours'] }}<span class="text-base font-normal">h</span></div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Điểm trung bình</div>
        <div class="stat-value text-2xl text-success">{{ $stats['avg'] ? number_format($stats['avg'], 1) : '—' }}</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Đạt (≥ 60)</div>
        <div class="stat-value text-2xl text-accent">{{ $stats['passed'] }}</div>
    </div>
</div>

{{-- ── Environments ─────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200 mb-5">
    <div class="card-body">
        <h2 class="card-title text-base mb-5">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Môi trường Sandbox
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($environments as $env)
            @php
                $typeIcon = match($env->type) {
                    'office'     => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'data'       => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                    'sales'      => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    'hr'         => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'workflow'   => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
                    'leadership' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    default      => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                };
                $completedCount = $mySessions->where('task.sandbox_env_id', $env->id)->where('status','completed')->count();
            @endphp
            <div class="border border-base-200 rounded-xl p-4 hover:border-primary/40 hover:shadow-sm transition-all cursor-default group">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $typeIcon }}"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="badge badge-ghost badge-xs">Tier {{ $env->tier }}</span>
                        @if($completedCount > 0)
                        <span class="badge badge-success badge-xs">{{ $completedCount }} xong</span>
                        @endif
                    </div>
                </div>
                <h3 class="font-semibold text-sm mb-1 group-hover:text-primary transition-colors">{{ $env->name }}</h3>
                <p class="text-xs text-base-content/50 mb-3 line-clamp-2">{{ $env->description }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-base-content/40">{{ $env->tasks->count() }} nhiệm vụ</span>
                    <span class="text-xs text-primary font-medium">Khám phá →</span>
                </div>
                @if($env->tasks->count())
                <div class="mt-3 pt-3 border-t border-base-200 space-y-1">
                    @foreach($env->tasks->take(2) as $task)
                    <div class="flex items-center gap-1.5 text-xs text-base-content/60">
                        <svg class="w-3 h-3 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        {{ Str::limit($task->title, 40) }}
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── My sessions ─────────────────────────────────────────────────────────── --}}
@if($mySessions->count())
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body">
        <h2 class="card-title text-base mb-5">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Lịch sử phiên thực hành
        </h2>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr class="text-xs text-base-content/50">
                        <th>Nhiệm vụ</th>
                        <th>Môi trường</th>
                        <th class="text-center">Điểm</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Hoàn thành</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mySessions as $session)
                    <tr class="hover:bg-base-50">
                        <td class="font-medium text-sm">{{ $session->task?->title ?? '—' }}</td>
                        <td class="text-xs text-base-content/60">{{ $session->task?->environment?->name ?? '—' }}</td>
                        <td class="text-center">
                            @if($session->final_score !== null)
                            <span class="font-semibold {{ $session->passed ? 'text-success' : 'text-error' }}">
                                {{ number_format($session->final_score, 1) }}
                            </span>
                            @else
                            <span class="text-base-content/30">—</span>
                            @endif
                        </td>
                        <td class="text-center text-xs text-base-content/60">
                            {{ $session->duration_minutes ? $session->duration_minutes . ' phút' : '—' }}
                        </td>
                        <td class="text-center">
                            @php
                                $sc = match($session->status) {
                                    'completed' => $session->passed ? ['Đạt', 'badge-success'] : ['Không đạt', 'badge-error'],
                                    'in_progress' => ['Đang làm', 'badge-warning'],
                                    'expired'   => ['Hết hạn', 'badge-ghost'],
                                    default     => [$session->status, 'badge-ghost'],
                                };
                            @endphp
                            <span class="badge {{ $sc[1] }} badge-xs">{{ $sc[0] }}</span>
                        </td>
                        <td class="text-xs text-base-content/40">{{ $session->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body items-center text-center py-12">
        <svg class="w-12 h-12 text-base-content/20 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <p class="text-base-content/50 text-sm">Bạn chưa có phiên thực hành nào. Chọn một môi trường phía trên để bắt đầu.</p>
    </div>
</div>
@endif

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

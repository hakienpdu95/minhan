@extends('layouts.backend')
@section('title', 'AI Impact — Tác động kinh doanh')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">AI Impact Tracker</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Ghi nhận và theo dõi tác động kinh doanh từ ứng dụng AI</p>
    </div>
    <a href="{{ route('backend.ai-impact.create') }}" class="btn btn-primary btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ghi nhận tác động
    </a>
</div>

{{-- ── Summary cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Tổng bản ghi</div>
        <div class="stat-value text-2xl text-primary">{{ $snapshots->total() }}</div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Cải thiện TB</div>
        <div class="stat-value text-2xl text-success">
            {{ $stats['avgImprovement'] ? number_format($stats['avgImprovement'], 1) . '%' : '—' }}
        </div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">ROI trung bình</div>
        <div class="stat-value text-2xl text-accent">
            {{ $stats['avgRoi'] ? number_format($stats['avgRoi'], 1) . 'x' : '—' }}
        </div>
    </div>
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
        <div class="stat-title text-xs">Tuần này</div>
        <div class="stat-value text-2xl">{{ $stats['thisWeek'] ?? 0 }}</div>
    </div>
</div>

{{-- ── Snapshots list ───────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body p-0">
        @if($snapshots->count())
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr class="text-xs text-base-content/50 border-b border-base-200">
                        <th class="px-5 py-3">Nhân viên</th>
                        <th>Danh mục</th>
                        <th>Chỉ số đo</th>
                        <th class="text-center">Cải thiện</th>
                        <th class="text-center">ROI</th>
                        <th class="text-center">Tiết kiệm giờ</th>
                        <th>Kỳ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($snapshots as $snap)
                    @php
                        $catLabels = [
                            'learning'     => ['Học tập',     'badge-info'],
                            'productivity' => ['Năng suất',   'badge-success'],
                            'quality'      => ['Chất lượng',  'badge-accent'],
                            'ai_adoption'  => ['Ứng dụng AI', 'badge-primary'],
                            'business'     => ['Kinh doanh',  'badge-warning'],
                        ];
                        $cat = $catLabels[$snap->impact_category?->value ?? $snap->impact_category] ?? [$snap->impact_category, 'badge-ghost'];
                    @endphp
                    <tr class="hover:bg-base-50 border-b border-base-200/50 last:border-0">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="avatar placeholder">
                                    <div class="bg-primary/10 rounded-full w-7 h-7 text-xs font-semibold text-primary flex items-center justify-center">
                                        {{ strtoupper(substr($snap->employee?->full_name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-sm leading-tight">{{ $snap->employee?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-base-content/40">{{ $snap->employee?->position ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $cat[1] }} badge-xs">{{ $cat[0] }}</span>
                        </td>
                        <td class="text-sm text-base-content/70 max-w-36 truncate" title="{{ $snap->impact_type }}">
                            {{ $snap->impact_type ?? '—' }}
                        </td>
                        <td class="text-center">
                            @if($snap->improvement_pct !== null)
                            <span class="font-semibold {{ $snap->improvement_pct >= 0 ? 'text-success' : 'text-error' }}">
                                {{ $snap->improvement_pct >= 0 ? '+' : '' }}{{ number_format($snap->improvement_pct, 1) }}%
                            </span>
                            @else
                            <span class="text-base-content/30">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($snap->roi_pct !== null)
                            <span class="font-semibold text-accent">{{ number_format($snap->roi_pct / 100, 1) }}x</span>
                            @else
                            <span class="text-base-content/30">—</span>
                            @endif
                        </td>
                        <td class="text-center text-sm text-base-content/60">
                            {{ $snap->time_saved_hours ?? '—' }}
                        </td>
                        <td class="text-xs text-base-content/40">
                            {{ $snap->period_start?->format('d/m/Y') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($snapshots->hasPages())
        <div class="px-5 py-4 border-t border-base-200">
            {{ $snapshots->links() }}
        </div>
        @endif
        @else
        <div class="items-center text-center py-14">
            <svg class="w-12 h-12 text-base-content/20 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-base-content/50 text-sm mb-3">Chưa có bản ghi tác động AI nào.</p>
            <a href="{{ route('backend.ai-impact.create') }}" class="btn btn-primary btn-sm">Ghi nhận ngay</a>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
    @vite(['Modules/Assessment/resources/assets/js/assessment.js'], 'build/backend')
@endpush

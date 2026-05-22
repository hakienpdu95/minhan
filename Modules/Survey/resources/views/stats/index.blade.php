@extends('layouts.backend')

@section('title', 'Thống kê — ' . $survey->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.edit', $survey) }}">{{ Str::limit($survey->title, 35) }}</a>
    <span class="sep">›</span>
    <span class="current">Thống kê</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Thống kê</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                Survey: <span class="font-semibold text-base-content/70">{{ $survey->title }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('survey.export')
            <a href="{{ route('backend.surveys.responses.export', $survey) }}"
               class="btn btn-success btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            @endcan
            @can('survey.view_responses')
            <a href="{{ route('backend.surveys.responses.index', $survey) }}"
               class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Xem responses
            </a>
            @endcan
            <a href="{{ route('backend.surveys.edit', $survey) }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Builder
            </a>
        </div>
    </div>

    {{-- ── Summary cards ────────────────────────────────────────────── --}}
    @php
        $totalByDaySum = collect($byDay)->sum('count');
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Tổng responses</div>
            <div class="stat-value text-3xl">{{ number_format($stats['total_responses']) }}</div>
            <div class="stat-desc">Hoàn chỉnh</div>
        </div>
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Fields đang theo dõi</div>
            <div class="stat-value text-3xl">{{ count($stats['fields']) }}</div>
            <div class="stat-desc">Active fields</div>
        </div>
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">30 ngày gần nhất</div>
            <div class="stat-value text-3xl text-primary">{{ number_format($totalByDaySum) }}</div>
            <div class="stat-desc">responses nộp</div>
        </div>
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Trung bình / ngày</div>
            <div class="stat-value text-3xl text-info">
                {{ $totalByDaySum > 0 ? number_format($totalByDaySum / 30, 1) : '0' }}
            </div>
            <div class="stat-desc">responses/ngày</div>
        </div>
    </div>

    {{-- ── Daily chart ──────────────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-4 pb-2">
            <h2 class="font-semibold text-base-content mb-3">Submissions theo ngày (30 ngày)</h2>
            <div id="dailyChart" class="w-full" style="height:192px"></div>
        </div>
    </div>

    {{-- ── Field stats ──────────────────────────────────────────────── --}}
    @if(count($stats['fields']) === 0)
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body text-center py-12 text-base-content/40">
            Survey chưa có field nào hoặc chưa nhận response.
        </div>
    </div>
    @else
    <div>
        <h2 class="font-semibold text-base-content mb-3">Thống kê theo field</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($stats['fields'] as $field)
            <div class="card bg-base-100 border border-base-200 shadow-sm">
                <div class="card-body p-4">

                    {{-- Field header --}}
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <p class="font-medium text-sm text-base-content leading-snug">{{ $field['label'] }}</p>
                        <span class="badge badge-xs badge-ghost badge-soft shrink-0 font-mono text-base-content/40">
                            {{ $field['field_type'] }}
                        </span>
                    </div>

                    {{-- Stats body --}}
                    @if(!$field['stats'])
                    <p class="text-xs text-base-content/40 italic">Không có dữ liệu thống kê.</p>

                    @elseif($field['stats']['type'] === 'choice')
                        @if(empty($field['stats']['distribution']))
                        <p class="text-xs text-base-content/40 italic">Chưa có câu trả lời.</p>
                        @else
                        <div class="space-y-2">
                            @foreach($field['stats']['distribution'] as $opt)
                            <div>
                                <div class="flex justify-between text-xs mb-0.5">
                                    <span class="text-base-content/70 truncate mr-2">{{ $opt['label'] }}</span>
                                    <span class="text-base-content/50 shrink-0">{{ $opt['count'] }} ({{ $opt['percent'] }}%)</span>
                                </div>
                                <div class="h-2 bg-base-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all"
                                         style="width: {{ $opt['percent'] }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    @elseif($field['stats']['type'] === 'number')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-base-200/60 rounded-lg px-3 py-2 text-center">
                            <p class="text-xs text-base-content/50">Trung bình</p>
                            <p class="text-xl font-bold text-primary">
                                {{ $field['stats']['avg'] !== null ? number_format($field['stats']['avg'], 1) : '—' }}
                            </p>
                        </div>
                        <div class="bg-base-200/60 rounded-lg px-3 py-2 text-center">
                            <p class="text-xs text-base-content/50">Số lượt trả lời</p>
                            <p class="text-xl font-bold">{{ $field['stats']['count'] }}</p>
                        </div>
                        <div class="bg-base-200/40 rounded-lg px-3 py-1.5 text-center">
                            <p class="text-xs text-base-content/40">Min</p>
                            <p class="font-semibold">{{ $field['stats']['min'] ?? '—' }}</p>
                        </div>
                        <div class="bg-base-200/40 rounded-lg px-3 py-1.5 text-center">
                            <p class="text-xs text-base-content/40">Max</p>
                            <p class="font-semibold">{{ $field['stats']['max'] ?? '—' }}</p>
                        </div>
                    </div>

                    @elseif($field['stats']['type'] === 'boolean')
                    @php
                        $bTotal  = $field['stats']['total'];
                        $yesPct  = $bTotal > 0 ? round($field['stats']['yes_count'] / $bTotal * 100) : 0;
                        $noPct   = 100 - $yesPct;
                    @endphp
                    <div class="space-y-2">
                        <div class="flex gap-3 text-sm">
                            <div class="flex-1 text-center">
                                <p class="text-xs text-base-content/50 mb-0.5">Có</p>
                                <p class="font-bold text-success text-lg">{{ $field['stats']['yes_count'] }}</p>
                                <p class="text-xs text-base-content/40">{{ $yesPct }}%</p>
                            </div>
                            <div class="divider divider-horizontal m-0"></div>
                            <div class="flex-1 text-center">
                                <p class="text-xs text-base-content/50 mb-0.5">Không</p>
                                <p class="font-bold text-error text-lg">{{ $field['stats']['no_count'] }}</p>
                                <p class="text-xs text-base-content/40">{{ $noPct }}%</p>
                            </div>
                        </div>
                        {{-- Split bar --}}
                        <div class="h-2 bg-error/30 rounded-full overflow-hidden">
                            <div class="h-full bg-success rounded-full"
                                 style="width: {{ $yesPct }}%"></div>
                        </div>
                        <p class="text-xs text-base-content/40 text-right">Tổng: {{ $bTotal }} câu trả lời</p>
                    </div>

                    @elseif($field['stats']['type'] === 'text')
                    <div class="flex items-center gap-3 py-2">
                        <svg class="w-8 h-8 text-base-content/20 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div>
                            <p class="text-2xl font-bold">{{ number_format($field['stats']['count']) }}</p>
                            <p class="text-xs text-base-content/50">câu trả lời văn bản</p>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script>
(function () {
    const byDay = @json($byDay);
    const dom   = document.getElementById('dailyChart');
    if (!dom) return;

    const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
    const chart  = echarts.init(dom, isDark ? 'dark' : null, { renderer: 'canvas' });

    const labels = byDay.map(d => {
        const [, m, day] = d.day.split('-');
        return `${day}/${m}`;
    });

    chart.setOption({
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross', label: { backgroundColor: '#6366f1' } },
            formatter: (params) => {
                const i = params[0].dataIndex;
                return `<b>${byDay[i].day}</b><br/>${params[0].marker} ${params[0].value} responses`;
            },
        },
        grid: { left: 45, right: 15, top: 15, bottom: 35 },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: labels,
            axisLabel: { interval: 4, fontSize: 11 },
            axisTick: { show: false },
            axisLine: { show: false },
        },
        yAxis: {
            type: 'value',
            minInterval: 1,
            splitLine: { lineStyle: { type: 'dashed' } },
            axisLabel: { fontSize: 11 },
        },
        series: [{
            type: 'line',
            data: byDay.map(d => d.count),
            smooth: 0.35,
            symbol: 'circle',
            symbolSize: 5,
            showSymbol: false,
            emphasis: { focus: 'series' },
            lineStyle: { color: '#6366f1', width: 2 },
            itemStyle: { color: '#6366f1' },
            areaStyle: {
                color: {
                    type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [
                        { offset: 0, color: 'rgba(99,102,241,0.25)' },
                        { offset: 1, color: 'rgba(99,102,241,0.02)' },
                    ],
                },
            },
        }],
    });

    // Responsive — dùng ResizeObserver thay vì window resize
    new ResizeObserver(() => chart.resize()).observe(dom);
})();
</script>
@endpush

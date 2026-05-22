@extends('layouts.backend')

@section('title', 'Responses — ' . $survey->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.edit', $survey) }}">{{ Str::limit($survey->title, 35) }}</a>
    <span class="sep">›</span>
    <span class="current">Responses</span>
</nav>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Responses</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                Survey: <span class="font-semibold text-base-content/70">{{ $survey->title }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('survey.export')
            <a href="{{ route('backend.surveys.responses.export', array_merge(['survey' => $survey], array_filter($filters))) }}"
               class="btn btn-success btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            @endcan
            <a href="{{ route('backend.surveys.stats.index', $survey) }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Thống kê
            </a>
            <a href="{{ route('backend.surveys.edit', $survey) }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Builder
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success text-sm py-2 px-4 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Stats summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Tổng responses</div>
            <div class="stat-value text-2xl">{{ number_format($totalAll) }}</div>
        </div>
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Hoàn chỉnh</div>
            <div class="stat-value text-2xl text-success">{{ number_format($totalComplete) }}</div>
        </div>
        <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-4 shadow-sm">
            <div class="stat-title text-xs">Đang điền (partial)</div>
            <div class="stat-value text-2xl text-warning">{{ number_format($totalAll - $totalComplete) }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('backend.surveys.responses.index', $survey) }}"
          class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4">
            <div class="flex flex-wrap gap-3 items-end">

                <div class="flex-1 min-w-[160px]">
                    <label class="label py-0 pb-1 text-xs text-base-content/60">Respondent ref</label>
                    <input type="text" name="respondent_ref"
                           value="{{ $filters['respondent_ref'] ?? '' }}"
                           placeholder="Tìm theo ref..."
                           class="input input-sm w-full">
                </div>

                <div class="min-w-[140px]">
                    <label class="label py-0 pb-1 text-xs text-base-content/60">Trạng thái</label>
                    <select name="status" class="select select-sm w-full">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $s)
                        <option value="{{ $s->value }}" @selected(isset($filters['status']) && (int)$filters['status'] === $s->value)>
                            {{ $s === \Modules\Survey\Enums\ResponseStatus::Complete ? 'Hoàn chỉnh' : 'Đang điền' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[130px]">
                    <label class="label py-0 pb-1 text-xs text-base-content/60">Từ ngày</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input input-sm w-full">
                </div>

                <div class="min-w-[130px]">
                    <label class="label py-0 pb-1 text-xs text-base-content/60">Đến ngày</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input input-sm w-full">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                    @if(array_filter($filters))
                    <a href="{{ route('backend.surveys.responses.index', $survey) }}" class="btn btn-ghost btn-sm">Reset</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0">

            @if($responses->isEmpty())
            <div class="text-center py-14">
                <svg class="w-10 h-10 mx-auto mb-3 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="font-semibold text-base-content/40">Chưa có response nào</p>
                <p class="text-xs text-base-content/30 mt-1">
                    @if(array_filter($filters))
                    Thử thay đổi bộ lọc
                    @else
                    Survey chưa có lượt nộp nào
                    @endif
                </p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-xs text-base-content/50 uppercase tracking-wide border-b border-base-200">
                            <th class="pl-5 w-12">#</th>
                            <th>Respondent</th>
                            <th>IP</th>
                            <th>Trạng thái</th>
                            <th>Nộp lúc</th>
                            <th class="pr-5 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responses as $response)
                        <tr class="border-b border-base-200 last:border-0 hover:bg-base-50 transition-colors">
                            <td class="pl-5 text-xs text-base-content/40 font-mono">{{ $response->id }}</td>

                            <td>
                                <span class="text-sm font-medium">
                                    {{ $response->respondent_ref ?? '—' }}
                                </span>
                            </td>

                            <td class="font-mono text-xs text-base-content/50">
                                {{ $response->respondent_ip ?? '—' }}
                            </td>

                            <td>
                                <span class="badge badge-xs badge-soft {{ $response->status === \Modules\Survey\Enums\ResponseStatus::Complete ? 'badge-success' : 'badge-warning' }}">
                                    {{ $response->status === \Modules\Survey\Enums\ResponseStatus::Complete ? 'Hoàn chỉnh' : 'Đang điền' }}
                                </span>
                            </td>

                            <td class="text-sm text-base-content/60">
                                {{ $response->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>

                            <td class="pr-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('backend.surveys.responses.show', [$survey, $response]) }}"
                                       class="btn btn-ghost btn-xs gap-1 text-base-content/50 hover:text-info">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Xem
                                    </a>

                                    <form method="POST"
                                          action="{{ route('backend.surveys.responses.destroy', [$survey, $response]) }}"
                                          onsubmit="return confirm('Xóa response #{{ $response->id }}?\n\nHành động này có thể khôi phục từ database.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-ghost btn-xs gap-1 text-base-content/30 hover:text-error">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($responses->hasPages())
            <div class="px-5 py-3 border-t border-base-200">
                {{ $responses->links() }}
            </div>
            @endif

            {{-- Row count --}}
            <div class="px-5 py-2 text-xs text-base-content/40 border-t border-base-200">
                Hiển thị {{ $responses->firstItem() }}–{{ $responses->lastItem() }} / {{ $responses->total() }} responses
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

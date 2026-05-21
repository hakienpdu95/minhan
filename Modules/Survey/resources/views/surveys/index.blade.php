@extends('layouts.backend')

@section('title', 'Quản lý Khảo sát')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Khảo sát</span>
</nav>
@endsection

@section('content')
{{-- Header --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Khảo sát</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tạo và quản lý các khảo sát thu thập phản hồi.</p>
    </div>
    @can('survey.create')
    <a href="{{ route('backend.surveys.create') }}" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tạo khảo sát
    </a>
    @endcan
</div>

{{-- Filter bar --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Tìm theo tên, slug..."
           class="input input-bordered input-sm w-64">
    <select name="status" class="select select-bordered select-sm">
        <option value="">-- Tất cả trạng thái --</option>
        @foreach($statuses as $st)
            <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>
                {{ $st->label() }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-ghost">Lọc</button>
    @if(request('q') || request('status') !== null)
        <a href="{{ route('backend.surveys.index') }}" class="btn btn-sm btn-ghost text-error">Xóa bộ lọc</a>
    @endif
</form>

{{-- Table --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="overflow-x-auto">
        <table class="table table-sm">
            <thead>
                <tr class="text-xs text-base-content/50 uppercase bg-base-50">
                    <th>Tiêu đề / Slug</th>
                    <th class="text-center">Version</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Responses</th>
                    <th>Ngày tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($surveys as $survey)
                <tr class="border-b border-base-100 last:border-0 hover">
                    <td>
                        <p class="font-semibold text-sm">{{ $survey->title }}</p>
                        <p class="text-xs text-base-content/40 font-mono">{{ $survey->slug }}</p>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-ghost badge-sm">v{{ $survey->version }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-sm {{ $survey->status->badgeClass() }}">
                            {{ $survey->status->label() }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="font-medium">{{ number_format($survey->responses_count) }}</span>
                    </td>
                    <td class="text-sm text-base-content/60">
                        {{ $survey->created_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div class="flex items-center gap-1 justify-end">
                            @can('survey.update')
                            <a href="{{ route('backend.surveys.edit', $survey) }}"
                               class="btn btn-ghost btn-xs btn-circle" title="Chỉnh sửa">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endcan

                            @can('survey.delete')
                            @if($survey->status->value !== 1)
                            <form method="POST" action="{{ route('backend.surveys.destroy', $survey) }}"
                                  onsubmit="return confirm('Xóa survey này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs btn-circle text-error" title="Xóa">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-base-content/40">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p>Chưa có khảo sát nào.</p>
                        @can('survey.create')
                        <a href="{{ route('backend.surveys.create') }}" class="btn btn-primary btn-sm mt-3">Tạo khảo sát đầu tiên</a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($surveys->hasPages())
    <div class="px-5 py-3 border-t border-base-200">
        {{ $surveys->links() }}
    </div>
    @endif
</div>
@endsection

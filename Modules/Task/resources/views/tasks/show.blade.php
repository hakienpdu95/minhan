@extends('layouts.backend')
@section('title', $task->title)

@section('content')

{{-- Page header --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div class="min-w-0">
        <div class="text-sm breadcrumbs mb-1">
            <ul>
                <li><a href="{{ route('backend.tasks.index') }}">Công việc</a></li>
                @if($task->project)
                <li><a href="{{ route('backend.tasks.index') }}?prj={{ $task->project_id }}">{{ $task->project->name }}</a></li>
                @endif
                <li class="text-base-content/60 truncate max-w-xs">{{ $task->title }}</li>
            </ul>
        </div>
        <div class="flex flex-wrap items-center gap-2 mt-1">
            <span class="badge badge-soft {{ $task->task_type->badgeClass() }}">
                {{ $task->task_type->icon() }} {{ $task->task_type->label() }}
            </span>
            <span class="badge badge-soft {{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span>
            <span class="badge badge-soft {{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span>
            @if($task->is_archived)
            <span class="badge badge-warning badge-soft">Đã lưu trữ</span>
            @endif
        </div>
        <h1 class="text-xl font-bold text-base-content mt-2">{{ $task->title }}</h1>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        @can('update', $task)
        <a href="{{ route('backend.tasks.edit', $task) }}" class="btn btn-warning btn-sm gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Sửa
        </a>
        @endcan
        @can('delete', $task)
        <form method="POST" action="{{ route('backend.tasks.destroy', $task) }}"
              onsubmit="return confirm('Bạn có chắc muốn xóa công việc này?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-error btn-sm gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Xóa
            </button>
        </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

    {{-- ── Main content ────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        @if($task->description)
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h3 class="text-sm font-semibold text-base-content/60 uppercase tracking-wide mb-3">Mô tả</h3>
                <div class="prose prose-sm max-w-none text-base-content/80">{!! nl2br(e($task->description)) !!}</div>
            </div>
        </div>
        @endif

        {{-- Labels --}}
        @if($task->labels->isNotEmpty())
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h3 class="text-sm font-semibold text-base-content/60 uppercase tracking-wide mb-3">Nhãn</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($task->labels as $label)
                    <span class="badge badge-sm gap-1.5" style="background-color: {{ $label->color_hex }}20; border-color: {{ $label->color_hex }}60; color: {{ $label->color_hex }}">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $label->color_hex }}"></span>
                        {{ $label->name }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Progress --}}
        @if(!$task->is_leaf)
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h3 class="text-sm font-semibold text-base-content/60 uppercase tracking-wide mb-3">Tiến độ con</h3>
                <div class="flex items-center gap-3">
                    <progress class="progress progress-primary flex-1" value="{{ $task->progress_pct }}" max="100"></progress>
                    <span class="text-sm font-semibold w-10 text-right">{{ $task->progress_pct }}%</span>
                </div>
                <p class="text-xs text-base-content/50 mt-1">{{ $task->subtask_done }}/{{ $task->subtask_total }} công việc con hoàn thành</p>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Info card --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-4 space-y-3">

                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Chi tiết</p>

                <div class="divide-y divide-base-200 text-sm">

                    <div class="py-2.5 flex justify-between items-start gap-2">
                        <span class="text-base-content/50 shrink-0">Dự án</span>
                        @if($task->project)
                        <a href="{{ route('backend.projects.show', $task->project) }}" class="font-medium text-right hover:text-primary transition-colors">
                            {{ $task->project->name }}
                            <span class="text-xs font-mono text-base-content/40">({{ $task->project->code }})</span>
                        </a>
                        @else
                        <span class="text-base-content/25">—</span>
                        @endif
                    </div>

                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Người thực hiện</span>
                        <span class="font-medium text-right">{{ $task->employee?->full_name ?? '—' }}</span>
                    </div>

                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Cấp độ</span>
                        <span class="badge badge-xs">{{ $task->depth }}</span>
                    </div>

                    @if($task->start_date)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Bắt đầu</span>
                        <span>{{ $task->start_date->format('d/m/Y') }}</span>
                    </div>
                    @endif

                    @if($task->due_date)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Hạn</span>
                        <span class="{{ now()->gt($task->due_date) && !$task->status->isDone() ? 'text-error font-semibold' : '' }}">
                            {{ $task->due_date->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif

                    @if($task->completed_at)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Hoàn thành</span>
                        <span class="text-success">{{ $task->completed_at->format('d/m/Y') }}</span>
                    </div>
                    @endif

                    @if($task->story_points)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Story Points</span>
                        <span class="badge badge-neutral badge-sm">{{ $task->story_points }} SP</span>
                    </div>
                    @endif

                    @if($task->estimated_hours)
                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Dự kiến</span>
                        <span>{{ number_format($task->estimated_hours, 1) }}h</span>
                    </div>
                    @endif

                    <div class="py-2.5 flex justify-between items-center gap-2">
                        <span class="text-base-content/50 shrink-0">Đã log</span>
                        <span>{{ number_format($task->logged_hours, 1) }}h</span>
                    </div>

                </div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-4 space-y-2">
                <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Lịch sử</p>
                <div class="text-xs text-base-content/50 space-y-1.5">
                    <div>Tạo bởi <span class="font-medium text-base-content/70">{{ $task->createdBy?->name ?? '—' }}</span></div>
                    <div>{{ $task->created_at?->format('d/m/Y H:i') }}</div>
                    @if($task->updatedBy)
                    <div class="pt-1 border-t border-base-200">Cập nhật bởi <span class="font-medium text-base-content/70">{{ $task->updatedBy->name }}</span></div>
                    <div>{{ $task->updated_at?->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

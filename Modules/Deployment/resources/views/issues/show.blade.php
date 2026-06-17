@extends('layouts.backend')
@section('title', $issue->title)

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <div class="text-sm text-base-content/50 mb-1">
            <a href="{{ route('deployment.issues.index', ['vertical' => $vertical->code()]) }}" class="hover:underline">Issues</a> /
        </div>
        <h1 class="text-2xl font-bold">{{ $issue->title }}</h1>
        <div class="flex gap-2 mt-2">
            <span class="badge {{ $issue->severity?->badgeClass() }}">{{ $issue->severity?->label() }}</span>
            <span class="badge {{ $issue->status?->badgeClass() }}">{{ $issue->status?->label() }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4"><span>{{ session('success') }}</span></div>
    @endif

    <div class="card bg-base-100 border border-base-200 shadow-sm mb-4">
        <div class="card-body">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-base-content/50 text-xs">Mô tả</dt>
                    <dd class="mt-1 whitespace-pre-wrap">{{ $issue->description ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-base-content/50 text-xs">Đối tượng</dt>
                        <dd>{{ $issue->target?->targetOrganization?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/50 text-xs">Người phụ trách</dt>
                        <dd>{{ $issue->owner?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/50 text-xs">Tạo bởi</dt>
                        <dd>{{ $issue->createdBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/50 text-xs">Ngày tạo</dt>
                        <dd>{{ $issue->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($issue->resolved_at)
                    <div>
                        <dt class="text-base-content/50 text-xs">Giải quyết lúc</dt>
                        <dd>{{ $issue->resolved_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </div>
            </dl>
        </div>
    </div>

    @if($issue->isActive())
    @can('resolve', $issue)
    <form method="POST"
          action="{{ route('deployment.issues.resolve', ['vertical' => $vertical->code(), 'issue' => $issue->id]) }}">
        @csrf
        <button type="submit" class="btn btn-success btn-sm"
                onclick="return confirm('Đánh dấu issue đã giải quyết?')">
            Đánh dấu đã giải quyết
        </button>
    </form>
    @endcan
    @endif
</div>
@endsection

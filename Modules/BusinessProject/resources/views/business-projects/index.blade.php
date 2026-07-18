@extends('layouts.backend')

@section('title', 'Dự án Tư vấn')

@section('content')
<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Danh sách Dự án Tư vấn</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Business Consulting OS — quản lý dự án tư vấn theo Business Project.</p>
        </div>
        <div class="flex items-center gap-2">
            @can('viewAny', \Modules\BusinessProject\Models\DeliverableTemplate::class)
            <a href="{{ route('backend.template-library.index') }}" class="btn btn-outline btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Thư viện Mẫu (Template Library)
            </a>
            @endcan
            @can('viewBcosDashboard', \Modules\BusinessProject\Models\BusinessProject::class)
            <a href="{{ route('backend.business-projects.bcos-dashboard.show') }}" class="btn btn-outline btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Bảng điều khiển BCOS (BCOS Dashboard)
            </a>
            @endcan
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên dự án</th>
                    <th>Khách hàng</th>
                    <th>Giai đoạn</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td class="font-mono text-xs">{{ $project->code }}</td>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->customer?->display_name ?? '—' }}</td>
                    <td><span class="badge badge-sm badge-primary">{{ $project->current_stage->label() }}</span></td>
                    <td>{{ $project->status }}</td>
                    <td>
                        <a href="{{ route('backend.business-projects.show', $project) }}" class="btn btn-ghost btn-xs">
                            Mở
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-base-content/40 py-8">
                        Chưa có Business Project nào. Chuyển đổi từ 1 Lead để bắt đầu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $projects->links() }}
</div>
@endsection

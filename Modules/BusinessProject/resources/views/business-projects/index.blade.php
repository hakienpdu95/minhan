@extends('layouts.backend')

@section('title', 'Business Projects')

@section('content')
<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Business Projects</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Business Consulting OS — quản lý dự án tư vấn theo Business Project.</p>
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

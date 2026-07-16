@extends('layouts.backend')

@section('title', 'Template Library')

@section('content')
<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Template Library</h1>
            <p class="text-sm text-base-content/50 mt-0.5">
                BCOS — mẫu chuẩn cho từng loại Deliverable (Giai đoạn 7 "Template cải tiến quay lại chính công cụ tạo deliverable").
            </p>
        </div>
        <a href="{{ route('backend.template-library.create') }}" class="btn btn-primary btn-sm">+ Tạo Template</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4 text-sm">{{ session('success') }}</div>
    @endif

    @forelse($templatesByType as $type => $templates)
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
        <div class="card-body">
            <h2 class="font-semibold mb-2">{{ \Modules\BusinessProject\Enums\DeliverableType::tryFrom($type)?->label() ?? $type }}</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Mô tả</th>
                            <th>Phạm vi</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td class="text-xs text-base-content/50">{{ $template->description ?? '—' }}</td>
                            <td>
                                <span class="badge badge-xs {{ $template->organization_id ? 'badge-outline' : 'badge-primary' }}">
                                    {{ $template->organization_id ? 'Tổ chức' : 'Dùng chung' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-xs {{ $template->is_active ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $template->is_active ? 'Đang dùng' : 'Ngừng dùng' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('backend.template-library.edit', $template) }}" class="btn btn-ghost btn-xs">Sửa</a>
                                <form action="{{ route('backend.template-library.destroy', $template) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Xóa template này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <p class="text-sm text-base-content/40">Chưa có Template nào — bấm "Tạo Template" để bắt đầu.</p>
    @endforelse
</div>
@endsection

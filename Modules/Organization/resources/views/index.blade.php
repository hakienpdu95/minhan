@extends('layouts.backend')
@section('title', 'Danh sách tổ chức')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Tổ chức</span>
</nav>
@endsection

@section('content')
{{-- Page header --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tổ chức</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Quản lý tất cả tổ chức trong hệ thống</p>
    </div>
    <a href="{{ route('backend.organizations.create') }}" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Thêm tổ chức
    </a>
</div>

{{-- Table --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead class="bg-base-200/50">
                    <tr>
                        <th>Tên tổ chức</th>
                        <th>Ngành nghề</th>
                        <th>Email / SĐT</th>
                        <th class="text-center">Thành viên</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center w-32">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($organizations as $org)
                <tr class="hover">
                    <td>
                        <div>
                            <a href="{{ route('backend.organizations.show', $org) }}"
                               class="font-semibold text-sm hover:text-primary transition-colors">
                                {{ $org->name }}
                            </a>
                            <p class="text-xs text-base-content/40 font-mono">{{ $org->slug }}</p>
                        </div>
                    </td>
                    <td class="text-sm text-base-content/70">{{ $org->industry ?? '—' }}</td>
                    <td>
                        <div class="text-sm">
                            @if ($org->email)<p>{{ $org->email }}</p>@endif
                            @if ($org->phone)<p class="text-base-content/60">{{ $org->phone }}</p>@endif
                            @if (!$org->email && !$org->phone)<span class="text-base-content/40">—</span>@endif
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('backend.users.index', ['organization_id' => $org->id]) }}"
                           class="badge badge-ghost badge-sm hover:badge-primary transition-colors cursor-pointer">
                            {{ $org->members_count }} người
                        </a>
                    </td>
                    <td class="text-center">
                        @if ($org->status->value === 'active')
                            <span class="badge badge-success badge-sm gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Hoạt động
                            </span>
                        @elseif ($org->status->value === 'suspended')
                            <span class="badge badge-error badge-sm">Tạm khóa</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Không hoạt động</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('backend.organizations.show', $org) }}"
                               class="btn btn-ghost btn-xs btn-square" title="Xem">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('backend.organizations.edit', $org) }}"
                               class="btn btn-ghost btn-xs btn-square" title="Sửa">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="btn btn-ghost btn-xs btn-square text-error" title="Xóa"
                                    onclick="confirmDelete('{{ route('backend.organizations.destroy', $org) }}', '{{ addslashes($org->name) }}')">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-base-content/40">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                        Chưa có tổ chức nào. <a href="{{ route('backend.organizations.create') }}" class="link link-primary">Tạo ngay</a>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($organizations->hasPages())
        <div class="flex justify-between items-center px-4 py-3 border-t border-base-200">
            <span class="text-sm text-base-content/50">
                Hiển thị {{ $organizations->firstItem() }}–{{ $organizations->lastItem() }} / {{ $organizations->total() }} tổ chức
            </span>
            {{ $organizations->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Delete modal --}}
<dialog id="deleteModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Xác nhận xóa</h3>
        <p class="py-3 text-sm text-base-content/70">
            Bạn có chắc muốn xóa tổ chức <strong id="deleteItemName" class="text-base-content"></strong>?
        </p>
        <p class="text-xs text-error/70">Toàn bộ thành viên và dữ liệu liên quan sẽ bị xóa theo.</p>
        <div class="modal-action mt-4">
            <form method="POST" id="deleteForm">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-error btn-sm">Xóa</button>
            </form>
            <button class="btn btn-ghost btn-sm" onclick="deleteModal.close()">Hủy</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endsection

@push('scripts')
<script>
function confirmDelete(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    deleteModal.showModal();
}
</script>
@endpush

@extends('layouts.backend')
@section('title', 'Quản lý tài khoản')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Tài khoản</span>
</nav>
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tài khoản</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Quản lý tài khoản toàn bộ tổ chức</p>
    </div>
    <a href="{{ route('backend.users.create') }}" class="btn btn-primary btn-sm gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Thêm tài khoản
    </a>
</div>

{{-- Filters --}}
<div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('backend.users.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-control flex-1 min-w-[180px]">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Tìm kiếm</span></label>
                <label class="input input-bordered input-sm flex items-center gap-2">
                    <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên, email..." class="grow"/>
                </label>
            </div>
            <div class="form-control min-w-[160px]">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Tổ chức</span></label>
                <select name="organization_id" class="select select-bordered select-sm">
                    <option value="">Tất cả tổ chức</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-control min-w-[130px]">
                <label class="label py-0 pb-1"><span class="label-text text-xs font-medium">Trạng thái</span></label>
                <select name="is_active" class="select select-bordered select-sm">
                    <option value="">Tất cả</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Vô hiệu</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">Đặt lại</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead class="bg-base-200/50">
                    <tr>
                        <th>Tài khoản</th>
                        <th>Tổ chức</th>
                        <th>Phòng ban</th>
                        <th class="text-center">Vai trò</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                <tr class="hover">
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full">
                                    <img src="https://api.dicebear.com/9.x/initials/svg?seed={{ urlencode($user->name) }}&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700" alt="{{ $user->name }}">
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $user->name }}</p>
                                <p class="text-xs text-base-content/50">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-base-content/70">
                        @if ($user->organization)
                            <a href="{{ route('backend.organizations.show', $user->organization) }}"
                               class="hover:text-primary transition-colors">
                                {{ $user->organization->name }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-sm">{{ $user->department ?? '—' }}</td>
                    <td class="text-center">
                        @php $role = $user->organizationMembership?->role ?? '—'; @endphp
                        @if ($role === 'owner')
                            <span class="badge badge-primary badge-sm">{{ $role }}</span>
                        @elseif ($role === 'admin')
                            <span class="badge badge-warning badge-sm">{{ $role }}</span>
                        @elseif ($role === 'manager')
                            <span class="badge badge-info badge-sm">{{ $role }}</span>
                        @else
                            <span class="badge badge-ghost badge-sm">{{ $role }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($user->is_active)
                            <span class="badge badge-success badge-sm gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Hoạt động
                            </span>
                        @else
                            <span class="badge badge-ghost badge-sm">Vô hiệu</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('backend.users.edit', $user) }}"
                               class="btn btn-ghost btn-xs btn-square" title="Sửa">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="btn btn-ghost btn-xs btn-square text-error" title="Xóa"
                                    onclick="confirmDelete('{{ route('backend.users.destroy', $user) }}', '{{ addslashes($user->name) }}')">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-base-content/40">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Chưa có tài khoản nào.
                        <a href="{{ route('backend.users.create') }}" class="link link-primary">Thêm ngay</a>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="flex justify-between items-center px-4 py-3 border-t border-base-200">
            <span class="text-sm text-base-content/50">
                Hiển thị {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }} tài khoản
            </span>
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<dialog id="deleteModal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-lg text-error">Xác nhận xóa</h3>
        <p class="py-3 text-sm text-base-content/70">Bạn có chắc muốn xóa tài khoản <strong id="deleteItemName" class="text-base-content"></strong>?</p>
        <p class="text-xs text-error/70">Tài khoản sẽ bị xóa hoàn toàn khỏi hệ thống.</p>
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

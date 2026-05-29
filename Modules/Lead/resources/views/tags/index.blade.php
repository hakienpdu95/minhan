@extends('layouts.backend')
@section('title', 'Quản lý Tags')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <span class="current">Quản lý Tags</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Quản lý Tags</h1>
    @can('create', \Modules\Lead\Models\LeadTagDefinition::class)
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('create-modal').showModal()">
        + Thêm tag
    </button>
    @endcan
</div>

@if(session('success'))
<div class="alert alert-success mb-4 py-2 text-sm">{{ session('success') }}</div>
@endif

{{-- Tag list --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body p-0">
        @if($tags->isEmpty())
        <div class="py-16 text-center text-base-content/40">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p class="text-sm">Chưa có tag nào. Tạo tag đầu tiên để phân loại cơ hội.</p>
        </div>
        @else
        <table class="table table-sm">
            <thead>
                <tr class="text-xs text-base-content/50 uppercase">
                    <th class="w-1/2">Tên tag</th>
                    <th>Màu</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tags as $tag)
                <tr class="hover:bg-base-200/30">
                    <td>
                        <span class="badge badge-sm font-medium text-white"
                              style="background: {{ $tag->color }}">
                            {{ $tag->name }}
                        </span>
                    </td>
                    <td>
                        <span class="inline-flex items-center gap-1.5 text-xs text-base-content/60">
                            <span class="w-4 h-4 rounded-full border border-base-300"
                                  style="background: {{ $tag->color }}"></span>
                            {{ $tag->color }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            @can('update', $tag)
                            <a href="{{ route('lead.tags.edit', $tag) }}"
                               class="btn btn-ghost btn-xs">Sửa</a>
                            @endcan
                            @can('delete', $tag)
                            <form method="POST" action="{{ route('lead.tags.destroy', $tag) }}"
                                  onsubmit="return confirm('Xóa tag này? Tất cả lead đang dùng tag sẽ bị gỡ.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs text-error">Xóa</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Create modal --}}
@can('create', \Modules\Lead\Models\LeadTagDefinition::class)
<dialog id="create-modal" class="modal">
    <div class="modal-box max-w-sm">
        <h3 class="font-bold text-base mb-4">Thêm tag mới</h3>
        <form method="POST" action="{{ route('lead.tags.store') }}" x-data>
            @csrf
            @include('lead::tags._form', ['tag' => null])
            <div class="modal-action mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Tạo tag</button>
                <button type="button" class="btn btn-ghost btn-sm"
                        onclick="document.getElementById('create-modal').close()">Hủy</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endcan

@if($errors->any())
<script>document.getElementById('create-modal')?.showModal();</script>
@endif
@endsection

@push('scripts')
<script>
// Sync radio → preview badge text on name input
document.querySelectorAll('[name="name"]').forEach(function(input) {
    input.addEventListener('input', function() {
        var preview = input.closest('form').querySelector('[x-bind\\:style]');
        if (preview) preview.textContent = input.value || 'Preview';
    });
});
// Sync radio value to hidden input
document.querySelectorAll('[name="color"][type="radio"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var hidden = r.closest('form').querySelector('#colorHidden');
        if (hidden) hidden.value = r.value;
    });
});
</script>
@endpush

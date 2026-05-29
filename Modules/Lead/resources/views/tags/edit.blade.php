@extends('layouts.backend')
@section('title', 'Sửa tag')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.tags.index') }}">Tags</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Sửa tag</h1>
    <a href="{{ route('lead.tags.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('lead.tags.update', $tag) }}"
      class="max-w-sm card bg-base-100 shadow-sm border border-base-200" x-data>
    @csrf @method('PUT')
    <div class="card-body">
        @include('lead::tags._form')
        <div class="flex gap-3 mt-2">
            <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
            <a href="{{ route('lead.tags.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[name="color"][type="radio"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var hidden = r.closest('form').querySelector('#colorHidden');
        if (hidden) hidden.value = r.value;
    });
});
document.querySelectorAll('[name="name"]').forEach(function(input) {
    input.addEventListener('input', function() {
        var preview = input.closest('form').querySelector('[x-bind\\:style]');
        if (preview) preview.textContent = input.value || 'Preview';
    });
});
</script>
@endpush

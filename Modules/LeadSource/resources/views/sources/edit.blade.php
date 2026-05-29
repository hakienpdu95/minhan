@extends('layouts.backend')
@section('title', 'Sửa nguồn cơ hội')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <a href="{{ route('lead-source.index') }}">Nguồn cơ hội</a>
    <span class="sep">›</span>
    <span class="current">{{ $source->label }}</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Sửa: {{ $source->label }}</h1>
    <a href="{{ route('lead-source.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

@if($source->is_global)
<div class="alert alert-info mb-4 py-2">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="text-sm">Đây là nguồn toàn hệ thống. Thay đổi sẽ ảnh hưởng đến tất cả tổ chức.</span>
</div>
@endif

<form method="POST" action="{{ route('lead-source.update', $source) }}" class="max-w-2xl space-y-4">
    @csrf @method('PUT')

    <div class="form-control">
        <label class="label py-0 pb-1.5"><span class="label-text font-medium">Mã nguồn</span></label>
        <input type="text" value="{{ $source->code }}" disabled
               class="input input-bordered input-sm bg-base-200/50 text-base-content/50">
    </div>

    @include('lead-source::sources._form')

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
        <a href="{{ route('lead-source.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
@endsection

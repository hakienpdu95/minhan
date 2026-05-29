@extends('layouts.backend')
@section('title', 'Thêm nguồn cơ hội')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('lead.index') }}">Cơ hội</a>
    <span class="sep">›</span>
    <a href="{{ route('lead-source.index') }}">Nguồn cơ hội</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Thêm nguồn cơ hội</h1>
    <a href="{{ route('lead-source.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('lead-source.store') }}" class="max-w-2xl space-y-4">
    @csrf
    @include('lead-source::sources._form')
    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Tạo nguồn</button>
        <a href="{{ route('lead-source.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
@endsection

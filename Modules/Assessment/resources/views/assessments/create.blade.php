@extends('layouts.backend')
@section('title', 'Thêm Assessment')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('assessments.index') }}">Assessment</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<h1 class="text-2xl font-bold text-base-content mb-6">Thêm Assessment mới</h1>

@if($errors->any())
<div class="alert alert-error mb-4 py-2 text-sm">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('assessments.store') }}">
    @csrf
    @include('assessment::assessments._form')
</form>
@endsection

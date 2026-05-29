@extends('layouts.backend')
@section('title', 'Sửa Assessment — ' . $assessment->assessment_code)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('assessments.index') }}">Assessment</a>
    <span class="sep">›</span>
    <a href="{{ route('assessments.show', $assessment->assessment_code) }}">{{ $assessment->assessment_code }}</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
<h1 class="text-2xl font-bold text-base-content mb-6">Sửa Assessment</h1>

@if($errors->any())
<div class="alert alert-error mb-4 py-2 text-sm">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('assessments.update', $assessment->assessment_code) }}">
    @csrf @method('PUT')
    @include('assessment::assessments._form')
</form>
@endsection

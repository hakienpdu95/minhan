@extends('layouts.backend')
@section('title', 'Tạo Workflow')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('workflows.index') }}">Workflow</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
@include('workflowautomation::workflows._form', ['workflow' => null, 'formAction' => route('workflows.store'), 'method' => 'POST'])
@endsection

@extends('layouts.backend')
@section('title', 'Sửa: ' . $workflow->name)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('workflows.index') }}">Workflow</a>
    <span class="sep">›</span>
    <a href="{{ route('workflows.show', $workflow) }}">{{ $workflow->name }}</a>
    <span class="sep">›</span>
    <span class="current">Sửa</span>
</nav>
@endsection

@section('content')
@include('workflowautomation::workflows._form', ['workflow' => $workflow, 'formAction' => route('workflows.update', $workflow), 'method' => 'PUT'])
@endsection

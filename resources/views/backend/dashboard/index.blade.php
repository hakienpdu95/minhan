{{-- resources/views/backend/dashboard/index.blade.php --}}
@extends('layouts.backend')

@section('title', 'Dashboard')

@section('breadcrumb')
<div class="breadcrumb-nav">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    <a href="#">Trang chủ</a>
    <span class="sep">›</span>
    <span class="current">Dashboard</span>
</div>
@endsection

@section('content')
<h1 style="font-size:24px;font-weight:700;color:#1e293b;margin:0">Dashboard</h1>
@endsection

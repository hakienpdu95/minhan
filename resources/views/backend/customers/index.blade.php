{{-- resources/views/backend/customers/index.blade.php --}}
@extends('layouts.backend')

@section('title', 'Danh sách Khách hàng')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.customers.index') }}">Khách hàng</a></li>
            <li>Danh sách</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Danh sách Khách hàng</h1>
@endsection

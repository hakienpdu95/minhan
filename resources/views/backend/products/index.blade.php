{{-- resources/views/backend/products/index.blade.php --}}
@extends('layouts.backend')

@section('title', 'Danh sách Sản phẩm')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.products.index') }}">Sản phẩm</a></li>
            <li>Danh sách</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Danh sách Sản phẩm</h1>
@endsection

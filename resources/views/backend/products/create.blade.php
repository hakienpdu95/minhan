{{-- resources/views/backend/products/create.blade.php --}}
@extends('layouts.backend')

@section('title', 'Thêm mới Sản phẩm')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.products.index') }}">Sản phẩm</a></li>
            <li>Thêm mới</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Thêm mới Sản phẩm</h1>
@endsection

{{-- resources/views/backend/products/edit.blade.php --}}
@extends('layouts.backend')

@section('title', 'Chỉnh sửa Sản phẩm')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.products.index') }}">Sản phẩm</a></li>
            <li>Chỉnh sửa</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Chỉnh sửa Sản phẩm</h1>
@endsection

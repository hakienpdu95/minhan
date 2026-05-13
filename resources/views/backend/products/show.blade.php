{{-- resources/views/backend/products/show.blade.php --}}
@extends('layouts.backend')

@section('title', 'Chi tiết Sản phẩm')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.products.index') }}">Sản phẩm</a></li>
            <li>Chi tiết</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Chi tiết Sản phẩm</h1>
@endsection

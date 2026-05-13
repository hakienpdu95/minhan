{{-- resources/views/backend/categories/create.blade.php --}}
@extends('layouts.backend')

@section('title', 'Thêm mới Danh mục')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.categories.index') }}">Danh mục</a></li>
            <li>Thêm mới</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Thêm mới Danh mục</h1>
@endsection

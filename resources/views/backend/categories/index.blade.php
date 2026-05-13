{{-- resources/views/backend/categories/index.blade.php --}}
@extends('layouts.backend')

@section('title', 'Danh sách Danh mục')

@section('breadcrumb')
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('backend.dashboard') }}">Trang chủ</a></li>
            <li><a href="{{ route('backend.categories.index') }}">Danh mục</a></li>
            <li>Danh sách</li>
        </ul>
    </div>
@endsection

@section('content')
    <h1 class="text-2xl font-bold">Danh sách Danh mục</h1>
@endsection

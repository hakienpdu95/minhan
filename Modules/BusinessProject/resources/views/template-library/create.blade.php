@extends('layouts.backend')

@section('title', 'Tạo Template')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Tạo Template mới</h1>

    <form action="{{ route('backend.template-library.store') }}" method="POST">
        @csrf
        @include('businessproject::template-library._form')
    </form>
</div>
@endsection

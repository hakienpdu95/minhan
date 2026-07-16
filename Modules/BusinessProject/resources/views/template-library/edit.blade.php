@extends('layouts.backend')

@section('title', 'Sửa Template: '.$template->name)

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Sửa Template: {{ $template->name }}</h1>

    <form action="{{ route('backend.template-library.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        @include('businessproject::template-library._form')
    </form>
</div>
@endsection

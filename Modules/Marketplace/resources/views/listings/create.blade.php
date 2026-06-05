@extends('layouts.backend')

@section('title', 'Tạo tin đăng mới — Marketplace')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.marketplace.listings.index') }}">Marketplace</a></li>
        <li>Tạo tin mới</li>
    </ul>
</div>
@endsection

@section('content')
<div class="px-6 py-4 max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold">Tạo tin đăng mới</h1>
        <p class="text-sm opacity-60 mt-0.5">Đăng tin tuyển dụng, dự án trực tiếp lên Marketplace</p>
    </div>

    <form action="{{ route('backend.marketplace.listings.store') }}" method="POST" x-data="listingForm">
        @csrf

        @include('marketplace::listings._form')

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('backend.marketplace.listings.index') }}" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Đăng tin
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('listingForm', function() {
        return {
            listingType: '{{ old('listing_type', 'job') }}',
        };
    });
});
</script>
@endpush

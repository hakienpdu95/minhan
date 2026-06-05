@extends('layouts.backend')

@section('title', 'Sửa tin đăng — Marketplace')

@section('breadcrumb')
<div class="breadcrumbs text-sm px-6 pt-4">
    <ul>
        <li><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('backend.marketplace.listings.index') }}">Marketplace</a></li>
        <li><a href="{{ route('backend.marketplace.listings.show', $listing) }}">{{ Str::limit($listing->title, 40) }}</a></li>
        <li>Sửa</li>
    </ul>
</div>
@endsection

@section('content')
<div class="px-6 py-4 max-w-4xl mx-auto">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold">Sửa tin đăng</h1>
            <p class="text-sm opacity-60 mt-0.5">{{ $listing->title }}</p>
        </div>
        @if($listing->isActive())
        <form action="{{ route('backend.marketplace.listings.close', $listing) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-warning btn-sm"
                    onclick="return confirm('Đóng tin này?')">
                Đóng tin
            </button>
        </form>
        @endif
    </div>

    <form action="{{ route('backend.marketplace.listings.update', $listing) }}" method="POST"
          x-data="listingForm">
        @csrf
        @method('PUT')

        @include('marketplace::listings._form')

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('backend.marketplace.listings.show', $listing) }}" class="btn btn-ghost">Hủy</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Lưu thay đổi
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
            listingType: '{{ old('listing_type', $listing->listing_type?->value ?? 'job') }}',
        };
    });
});
</script>
@endpush

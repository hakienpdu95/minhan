@extends('layouts.backend')
@section('title', 'Đã hoàn thành khảo sát')

@section('content')
<div class="max-w-xl mx-auto py-12 px-4 text-center">

    {{-- Icon check --}}
    <div class="flex justify-center mb-6">
        <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center">
            <svg class="w-10 h-10 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
    </div>

    <h1 class="text-2xl font-bold text-base-content mb-2">Cảm ơn bạn!</h1>
    <p class="text-base-content/60 text-sm mb-6">
        Bạn đã hoàn thành khảo sát <span class="font-medium text-base-content">{{ $survey->title }}</span>.
    </p>

    {{-- Score / result nếu có --}}
    @if($response && $response->result)
    @php $result = $response->result; @endphp
    <div class="card bg-base-200 border border-base-300 mb-6 text-left">
        <div class="card-body py-4 px-5">
            <h2 class="card-title text-base mb-2">Kết quả của bạn</h2>

            @if($result->total_score !== null)
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl font-bold text-primary">{{ number_format($result->total_score, 1) }}</span>
                @if($result->max_score)
                <span class="text-base-content/40 text-sm">/ {{ number_format($result->max_score, 1) }}</span>
                @endif
            </div>
            @endif

            @if($result->band_label)
            <div class="badge badge-primary">{{ $result->band_label }}</div>
            @endif

            @if($result->feedback)
            <p class="text-sm text-base-content/70 mt-2">{{ $result->feedback }}</p>
            @endif
        </div>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('backend.dashboard') }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Về trang chủ
        </a>
        @if($survey->allow_multiple_responses)
        <a href="{{ route('backend.surveys.take', $survey->slug) }}" class="btn btn-outline btn-sm">
            Làm lại khảo sát
        </a>
        @endif
    </div>

</div>
@endsection

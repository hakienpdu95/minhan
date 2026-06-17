@extends('layouts.backend')
@section('title', 'Triển khai')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-base-content">Triển khai</h1>
        <p class="text-sm text-base-content/50 mt-1">Chọn lĩnh vực để xem tổng quan và quản lý dữ liệu</p>
    </div>

    @if($verticals->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-16 h-16 text-base-content/20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-base-content/40 text-sm font-medium">Chưa có lĩnh vực nào được kích hoạt</p>
            <p class="text-base-content/30 text-xs mt-1">Liên hệ quản trị viên để kích hoạt dịch vụ triển khai</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($verticals as $vertical)
            @php
                $dashboardUrl = null;
                try {
                    if (\Illuminate\Support\Facades\Route::has('deployment.dashboard')) {
                        $dashboardUrl = route('deployment.dashboard', ['vertical' => $vertical->code()]);
                    }
                } catch (\Throwable) {}
            @endphp
            <a href="{{ $dashboardUrl ?? '#' }}"
               class="group block bg-base-100 border border-base-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-150">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-base-content text-sm leading-snug">{{ $vertical->label() }}</p>
                        <p class="text-xs text-base-content/40 mt-0.5 uppercase tracking-wide">{{ $vertical->code() }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-primary font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Xem tổng quan</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    @endif

</div>
@endsection

@extends('layouts.backend')
@section('title', 'Mẫu đánh giá')


@section('content')

@if(session('success'))
<div class="alert alert-success py-3 px-4 mb-5 text-sm">{{ session('success') }}</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Mẫu đánh giá</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Quản lý bộ tiêu chí đánh giá hiệu suất</p>
    </div>
    @can('create', \Modules\PerformanceReview\Models\PerformanceReview::class)
    <a href="{{ route('backend.review-templates.create') }}" class="btn btn-primary btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo mẫu mới
    </a>
    @endcan
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($templates as $tpl)
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-base-content">{{ $tpl->name }}</h3>
                    <div class="flex items-center gap-2 mt-1.5">
                        @if($tpl->is_system)
                        <span class="badge badge-ghost badge-xs">Hệ thống</span>
                        @endif
                        @if($tpl->is_locked)
                        <span class="badge badge-warning badge-xs">Đã khóa</span>
                        @endif
                        @if(!$tpl->is_active)
                        <span class="badge badge-error badge-xs">Ẩn</span>
                        @endif
                    </div>
                </div>
                <span class="text-2xl font-bold text-primary">{{ $tpl->rating_scale }}</span>
            </div>

            <div class="mt-3 space-y-1 text-xs text-base-content/50">
                <div class="flex justify-between">
                    <span>Chu kỳ</span>
                    <span class="font-medium">{{ $tpl->period_type->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tiêu chí</span>
                    <span class="font-medium">{{ $tpl->criteria_count }}</span>
                </div>
                @if($tpl->apply_to_function)
                <div class="flex justify-between">
                    <span>Áp dụng</span>
                    <span class="font-medium">{{ $tpl->apply_to_function }}</span>
                </div>
                @endif
            </div>

            <div class="card-actions justify-end mt-4 pt-3 border-t border-base-200">
                <a href="{{ route('backend.review-templates.show', $tpl) }}" class="btn btn-ghost btn-xs">
                    Xem chi tiết
                </a>
                @if(!$tpl->is_locked)
                @can('delete', \Modules\PerformanceReview\Models\PerformanceReview::class)
                <form method="POST" action="{{ route('backend.review-templates.destroy', $tpl) }}"
                      onsubmit="return confirm('Xóa mẫu {{ addslashes($tpl->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-xs text-error">Xóa</button>
                </form>
                @endcan
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center opacity-40">
        <svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">Chưa có mẫu đánh giá nào</p>
    </div>
    @endforelse
</div>
@endsection

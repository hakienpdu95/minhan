@extends('layouts.backend')
@section('title', 'Workforce — Tổng quan năng lực')

@push('styles')
    @vite(['Modules/Assessment/resources/assets/sass/assessment.scss'], 'build/backend')
@endpush

@section('content')
<div x-data="workforceAdminPage({{ Js::from([
    'apiUrl'        => route('backend.workforce.api'),
    'maturityLevels'=> $maturityLevels,
]) }})">

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Workforce — Năng lực số</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tổng quan Digital Twin của toàn tổ chức</p>
    </div>
    <a href="{{ route('backend.workforce.me') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Hồ sơ của tôi
    </a>
</div>

{{-- ── Stat cards ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-5">
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-3 shadow-sm sm:col-span-2 md:col-span-1">
        <div class="stat-title text-xs">Tổng hồ sơ</div>
        <div class="stat-value text-xl">{{ number_format($total) }}</div>
    </div>
    @foreach($maturityLevels as $lvl)
    @php
        $labels = ['DIGITAL_BEGINNER'=>'Khởi đầu','DIGITAL_AWARE'=>'Nhận thức','DIGITAL_PRACTITIONER'=>'Thực hành','DIGITAL_PROFESSIONAL'=>'Chuyên nghiệp','DIGITAL_LEADER'=>'Dẫn dắt'];
        $colors = ['DIGITAL_BEGINNER'=>'text-base-content/50','DIGITAL_AWARE'=>'text-info','DIGITAL_PRACTITIONER'=>'text-warning','DIGITAL_PROFESSIONAL'=>'text-success','DIGITAL_LEADER'=>'text-accent'];
    @endphp
    <div class="stat bg-base-100 border border-base-200 rounded-xl py-3 px-3 shadow-sm">
        <div class="stat-title text-xs">{{ $labels[$lvl] ?? $lvl }}</div>
        <div class="stat-value text-xl {{ $colors[$lvl] ?? '' }}">{{ $byLevel[$lvl] ?? 0 }}</div>
    </div>
    @endforeach
</div>

{{-- ── Filter bar ───────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200 mb-4">
    <div class="card-body py-3 px-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="form-control flex-1 min-w-52">
                <label class="label py-0.5">
                    <span class="label-text text-xs font-medium">Tìm kiếm nhân viên</span>
                </label>
                <input type="text" x-model.debounce.400ms="filters.search"
                       class="input input-bordered input-sm w-full"
                       placeholder="VD: Nguyễn Văn A">
            </div>
            <div class="form-control w-48">
                <label class="label py-0.5">
                    <span class="label-text text-xs font-medium">Cấp độ</span>
                </label>
                <select x-model="filters.maturity_level" class="select select-bordered select-sm w-full">
                    <option value="">— Tất cả cấp độ —</option>
                    @foreach($maturityLevels as $lvl)
                    <option value="{{ $lvl }}">{{ $labels[$lvl] ?? $lvl }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" @click="resetFilters()" class="btn btn-ghost btn-sm self-end">Xóa lọc</button>
        </div>
    </div>
</div>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 shadow-sm border border-base-200">
    <div class="card-body p-0">
        <div id="workforce-table"></div>
    </div>
</div>

</div>
@endsection

@push('scripts')
    @vite([
        'resources/js/modules/tabulator.js',
        'Modules/Assessment/resources/assets/js/assessment.js',
    ], 'build/backend')
@endpush

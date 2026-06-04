@extends('layouts.backend')
@section('title', 'Tạo tag mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-tags.index') }}">Tags KC</a>
    <span class="sep">›</span>
    <span class="current">Tạo tag</span>
</nav>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tạo tag mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tag dùng để gắn nhãn cho tài liệu trong kho tri thức</p>
    </div>
    <a href="{{ route('backend.kc-tags.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-semibold">Có {{ $errors->count() }} lỗi:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('backend.kc-tags.store') }}" novalidate
      x-data="kcTagForm()">
    @csrf

    <div class="max-w-xl">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">

                <h2 class="card-title text-base">Thông tin tag</h2>

                {{-- Tên --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên tag <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           @input="onNameInput($event.target.value)"
                           class="input input-bordered input-sm w-full @error('name') input-error @enderror"
                           placeholder="VD: ISO 9001, Onboarding, HR Policy...">
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Slug --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Slug <span class="text-error">*</span></span>
                        <span class="label-text-alt text-xs text-base-content/40">Tự động tạo từ tên</span>
                    </label>
                    <input type="text" name="slug" x-model="slug"
                           class="input input-bordered input-sm w-full font-mono @error('slug') input-error @enderror"
                           placeholder="iso-9001">
                    @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Màu --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Màu hiển thị</span>
                        <span class="label-text-alt text-xs text-base-content/40">Mã hex, VD: #534AB7</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_hex" value="{{ old('color_hex', '#6366f1') }}"
                               x-model="color"
                               class="w-10 h-10 rounded-lg border border-base-300 cursor-pointer p-0.5">
                        <input type="text" x-model="color" placeholder="#6366f1"
                               @input="syncColor($event.target.value)"
                               class="input input-bordered input-sm w-32 font-mono @error('color_hex') input-error @enderror">
                        <span class="badge badge-sm font-medium px-3"
                              :style="{ backgroundColor: color, color: '#fff' }"
                              x-text="namePreview || 'Preview'"></span>
                    </div>
                    @error('color_hex')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <a href="{{ route('backend.kc-tags.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
            <button type="submit" class="btn btn-primary btn-sm gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Tạo tag
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
    @vite([
        'Modules/KcItem/resources/assets/js/kc-item.js',
    ], 'build/backend')
@endpush

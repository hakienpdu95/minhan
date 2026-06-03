@extends('layouts.backend')
@section('title', 'Chỉnh sửa chức danh')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.job-titles.index') }}">Chức danh</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.job-titles.show', $jobTitle) }}">{{ $jobTitle->name }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')
<div>

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa chức danh</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ $jobTitle->name }} · <span class="font-mono">{{ $jobTitle->code }}</span></p>
    </div>
    <a href="{{ route('backend.job-titles.show', $jobTitle) }}" class="btn btn-ghost btn-sm gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Quay lại
    </a>
</div>

@if($jobTitle->is_locked)
<div class="alert alert-warning py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <p>Chức danh này đã bị khóa (<code>is_locked = true</code>). Không thể chỉnh sửa.</p>
</div>
@endif

@if($errors->any())
<div class="alert alert-error py-3 px-4 mb-5 flex items-start gap-3 text-sm">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-semibold">Có {{ $errors->count() }} lỗi cần kiểm tra:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('backend.job-titles.update', $jobTitle) }}" novalidate>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_260px] gap-6 items-start">

        {{-- ── Card chính ──────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body gap-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Tên chức danh <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name', $jobTitle->name) }}"
                               class="input input-bordered @error('name') input-error @enderror"
                               {{ $jobTitle->is_locked ? 'disabled' : '' }}
                               placeholder="VD: Trưởng phòng Kinh doanh"/>
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Mã chức danh <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs opacity-50">Tự động uppercase</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', $jobTitle->code) }}"
                               class="input input-bordered font-mono @error('code') input-error @enderror"
                               {{ $jobTitle->is_locked ? 'disabled' : '' }}
                               placeholder="VD: MGR"/>
                        @error('code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Nhóm chức danh <span class="text-error">*</span></span></label>
                        <select name="category" class="select select-bordered @error('category') select-error @enderror"
                                {{ $jobTitle->is_locked ? 'disabled' : '' }}>
                            @foreach($categories as $cat)
                            <option value="{{ $cat['value'] }}"
                                    {{ old('category', $jobTitle->category->value) === $cat['value'] ? 'selected' : '' }}>
                                {{ $cat['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('category')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Cấp bậc <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs opacity-50">1–20</span>
                        </label>
                        <input type="number" name="level" value="{{ old('level', $jobTitle->level) }}"
                               min="1" max="20"
                               class="input input-bordered @error('level') input-error @enderror"
                               {{ $jobTitle->is_locked ? 'disabled' : '' }}/>
                        @error('level')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Mô tả</span></label>
                        <textarea name="description" rows="4"
                                  class="textarea textarea-bordered @error('description') textarea-error @enderror"
                                  {{ $jobTitle->is_locked ? 'disabled' : '' }}
                                  placeholder="Mô tả vai trò và trách nhiệm...">{{ old('description', $jobTitle->description) }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

            </div>
        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            @if($jobTitle->is_system)
            <div class="alert alert-info py-2 px-3 text-xs gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Chức danh do hệ thống tạo sẵn
            </div>
            @endif

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-3">
                    <h3 class="font-semibold text-sm">Trạng thái</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0"/>
                        <input type="checkbox" name="is_active" value="1"
                               class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', $jobTitle->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                               {{ $jobTitle->is_locked ? 'disabled' : '' }}/>
                        <div>
                            <p class="text-sm font-medium">Đang dùng</p>
                            <p class="text-xs text-base-content/50">Hiển thị trong danh sách chọn</p>
                        </div>
                    </label>
                </div>
            </div>

            @if(! $jobTitle->is_locked)
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-3">
                    <h3 class="font-semibold text-sm">Thao tác</h3>
                    <button type="submit" class="btn btn-primary w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('backend.job-titles.show', $jobTitle) }}" class="btn btn-ghost w-full">Hủy</a>
                </div>
            </div>
            @endif

        </div>

    </div>
</form>
</div>
@endsection

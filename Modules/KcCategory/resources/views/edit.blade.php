@extends('layouts.backend')
@section('title', 'Chỉnh sửa danh mục')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-categories.index') }}">Danh mục tài liệu KC</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-categories.show', $kcCategory) }}">{{ Str::limit($kcCategory->name, 30) }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa danh mục</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ $kcCategory->name }}</p>
    </div>
    <a href="{{ route('backend.kc-categories.show', $kcCategory) }}" class="btn btn-ghost btn-sm gap-1.5">
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
        <p class="font-semibold">Có {{ $errors->count() }} lỗi cần kiểm tra:</p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs opacity-90">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('backend.kc-categories.update', $kcCategory) }}" novalidate
      x-data="kcCategoryForm('{{ old('color_hex', $kcCategory->color_hex) }}')">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_268px] gap-6 items-start">

        {{-- ── Card chính ──────────────────────────────────────────────── --}}
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body space-y-4">

                <h2 class="card-title text-base">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Thông tin danh mục
                </h2>

                {{-- Tên danh mục --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên danh mục <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $kcCategory->name) }}"
                           data-req="Vui lòng nhập tên danh mục"
                           class="input input-bordered input-sm w-full @error('name') input-error @enderror"
                           placeholder="VD: Quy trình vận hành...">
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Slug --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Slug <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug', $kcCategory->slug) }}"
                           class="input input-bordered input-sm w-full font-mono @error('slug') input-error @enderror"
                           placeholder="vd-quy-trinh-van-hanh">
                    <p class="mt-1 text-xs text-base-content/40">Chỉ dùng chữ thường, số và dấu <code class="bg-base-200 px-1 rounded">-</code></p>
                    @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Danh mục cha --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Danh mục cha</span>
                        <span class="label-text-alt text-xs text-base-content/40">Để trống = danh mục gốc</span>
                    </label>
                    <select id="ts-parent" name="parent_id"
                            class="select select-bordered select-sm w-full ts-init"
                            data-ts-placeholder="— Danh mục gốc (cấp 1) —">
                        <option value="">— Danh mục gốc (cấp 1) —</option>
                        @foreach($parentOptions as $opt)
                        <option value="{{ $opt['value'] }}"
                                {{ old('parent_id', $kcCategory->parent_id) == $opt['value'] ? 'selected' : '' }}>
                            {{ $opt['text'] }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Mô tả --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mô tả</span>
                    </label>
                    <textarea name="description" rows="3"
                              class="textarea textarea-bordered textarea-sm w-full @error('description') textarea-error @enderror"
                              placeholder="Mô tả ngắn về nội dung danh mục...">{{ old('description', $kcCategory->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                {{-- Icon + Màu --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Icon</span>
                            <span class="label-text-alt text-xs text-base-content/40">Tabler Icon</span>
                        </label>
                        <input type="text" name="icon" value="{{ old('icon', $kcCategory->icon) }}"
                               class="input input-bordered input-sm w-full font-mono @error('icon') input-error @enderror"
                               placeholder="ti-folder">
                        @error('icon')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Màu sắc</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="colorHex"
                                   class="w-9 h-9 rounded cursor-pointer border border-base-300 p-0.5 bg-base-100">
                            <input type="text" name="color_hex" x-model="colorHex"
                                   class="input input-bordered input-sm flex-1 font-mono @error('color_hex') input-error @enderror"
                                   placeholder="#534AB7">
                        </div>
                        @error('color_hex')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                </div>

                {{-- Thứ tự --}}
                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Thứ tự hiển thị</span>
                        <span class="label-text-alt text-xs text-base-content/40">Số nhỏ hiển thị trước</span>
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $kcCategory->sort_order) }}"
                           min="0"
                           class="input input-bordered input-sm w-32 @error('sort_order') input-error @enderror">
                    @error('sort_order')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">

                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Xuất bản</p>

                    <div class="form-control mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none group">
                            <input type="checkbox" name="is_active" value="1"
                                   class="checkbox checkbox-sm checkbox-primary mt-0.5 shrink-0"
                                   {{ old('is_active', $kcCategory->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <div>
                                <span class="text-sm font-medium group-hover:text-primary transition-colors">Đang hiển thị</span>
                                <p class="text-xs text-base-content/50 mt-0.5">Hiển thị trong danh sách tài liệu</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-between text-xs text-base-content/40 mb-4 px-0.5">
                        <span>Tạo {{ $kcCategory->created_at->format('d/m/Y') }}</span>
                        <span>Sửa {{ $kcCategory->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('backend.kc-categories.show', $kcCategory) }}" class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Lưu lại
                        </button>
                    </div>

                    <p class="text-center text-xs text-base-content/30 mt-2.5">
                        <span class="text-error">*</span> là trường bắt buộc
                    </p>

                </div>
            </div>

        </div>

    </div>
</form>

@endsection

@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/KcCategory/resources/assets/js/kc-category.js',
    ], 'build/backend')
@endpush

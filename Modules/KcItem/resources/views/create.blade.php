@extends('layouts.backend')
@section('title', 'Tạo tài liệu mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-items.index') }}">Kho tri thức</a>
    <span class="sep">›</span>
    <span class="current">Tạo tài liệu</span>
</nav>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Tạo tài liệu mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Tài liệu sẽ được lưu ở trạng thái Bản nháp</p>
    </div>
    <a href="{{ route('backend.kc-items.index') }}" class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST" action="{{ route('backend.kc-items.store') }}" novalidate
      x-data="kcItemForm()">
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

        {{-- ── Nội dung chính ──────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Card tiêu đề & slug --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body space-y-4">

                    <h2 class="card-title text-base">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Thông tin cơ bản
                    </h2>

                    {{-- Tiêu đề --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tiêu đề <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               @input="onTitleInput($event.target.value)"
                               class="input input-bordered input-sm w-full @error('title') input-error @enderror"
                               placeholder="VD: Quy trình onboarding nhân viên mới...">
                        @error('title')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Slug --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Slug <span class="text-error">*</span></span>
                            <span class="label-text-alt text-xs text-base-content/40">Tự động tạo từ tiêu đề</span>
                        </label>
                        <input type="text" name="slug" x-model="slug"
                               class="input input-bordered input-sm w-full font-mono @error('slug') input-error @enderror"
                               placeholder="quy-trinh-onboarding-nhan-vien-moi">
                        @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tóm tắt --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tóm tắt</span>
                            <span class="label-text-alt text-xs text-base-content/40">Hiển thị trong danh sách</span>
                        </label>
                        <textarea name="summary" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full @error('summary') textarea-error @enderror"
                                  placeholder="Mô tả ngắn gọn về nội dung tài liệu...">{{ old('summary') }}</textarea>
                        @error('summary')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Card nội dung --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body space-y-3">
                    <h2 class="card-title text-base">Nội dung</h2>
                    <div class="form-control">
                        <textarea name="content" rows="16"
                                  class="textarea textarea-bordered w-full font-mono text-sm @error('content') textarea-error @enderror"
                                  placeholder="Nhập nội dung Markdown hoặc HTML...">{{ old('content') }}</textarea>
                        @error('content')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-base-content/40">Hỗ trợ Markdown. Video: nhập embed URL vào đây.</p>
                </div>
            </div>

        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            {{-- Xuất bản --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Xuất bản</p>

                    <div class="flex gap-2">
                        <a href="{{ route('backend.kc-items.index') }}" class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Lưu nháp
                        </button>
                    </div>
                    <p class="text-center text-xs text-base-content/30 mt-2.5">
                        <span class="text-error">*</span> là trường bắt buộc
                    </p>
                </div>
            </div>

            {{-- Loại & Danh mục --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Phân loại</p>

                    {{-- Loại tài liệu --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Loại tài liệu <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-type" name="type"
                                class="select select-bordered select-sm w-full">
                            <option value="">— Chọn loại —</option>
                            @foreach($types as $t)
                            <option value="{{ $t['value'] }}" {{ old('type') === $t['value'] ? 'selected' : '' }}>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Danh mục --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Danh mục <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-category" name="category_id"
                                class="select select-bordered select-sm w-full">
                            <option value="">— Chọn danh mục —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat['value'] }}" {{ old('category_id') == $cat['value'] ? 'selected' : '' }}>
                                {{ $cat['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Phạm vi hiển thị --}}
                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Phạm vi hiển thị</span>
                        </label>
                        <select id="ts-visibility" name="visibility"
                                class="select select-bordered select-sm w-full">
                            @foreach($visibilities as $v)
                            <option value="{{ $v['value'] }}" {{ old('visibility', 'internal') === $v['value'] ? 'selected' : '' }}>
                                {{ $v['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('visibility')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Tags --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-2">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Tags</p>
                    <div class="form-control">
                        <select id="ts-tags" name="tags[]" multiple
                                data-api-url="{{ $tagsApiUrl }}"
                                class="select select-bordered select-sm w-full"
                                placeholder="Chọn hoặc tạo tag mới...">
                            @foreach(old('tags', []) as $tagId)
                            <option value="{{ $tagId }}" selected>{{ $tagId }}</option>
                            @endforeach
                        </select>
                        @error('tags')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-base-content/40 mt-1.5">Nhập tên tag và Enter để tạo mới</p>
                    </div>
                </div>
            </div>

            {{-- Cấu hình thêm --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Tùy chọn</p>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_featured') ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium">Nổi bật</span>
                            <p class="text-xs text-base-content/50">Hiển thị trên trang chủ KC</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="hidden" name="is_pinned" value="0">
                        <input type="checkbox" name="is_pinned" value="1"
                               class="checkbox checkbox-sm checkbox-secondary"
                               {{ old('is_pinned') ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium">Ghim đầu</span>
                            <p class="text-xs text-base-content/50">Ghim đầu danh sách trong danh mục</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Ngày hiệu lực --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Hiệu lực</p>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs">Ngày có hiệu lực</span>
                        </label>
                        <input type="date" name="effective_date" value="{{ old('effective_date') }}"
                               class="input input-bordered input-sm w-full @error('effective_date') input-error @enderror">
                        @error('effective_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs">Ngày hết hiệu lực</span>
                        </label>
                        <input type="date" name="expired_date" value="{{ old('expired_date') }}"
                               class="input input-bordered input-sm w-full @error('expired_date') input-error @enderror">
                        @error('expired_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>

@endsection

{{-- Note: Upload file đính kèm chỉ khả dụng sau khi tài liệu được lưu (trang edit). --}}

@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/KcItem/resources/assets/js/kc-item.js',
    ], 'build/backend')
@endpush

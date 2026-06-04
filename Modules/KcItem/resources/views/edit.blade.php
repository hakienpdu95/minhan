@extends('layouts.backend')
@section('title', 'Sửa: ' . $kcItem->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-items.index') }}">Kho tri thức</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.kc-items.show', $kcItem) }}">{{ Str::limit($kcItem->title, 30) }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa tài liệu</h1>
        <p class="text-sm text-base-content/50 mt-0.5">{{ Str::limit($kcItem->title, 60) }}</p>
    </div>
    <a href="{{ route('backend.kc-items.show', $kcItem) }}" class="btn btn-ghost btn-sm gap-1.5">
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

<form method="POST" action="{{ route('backend.kc-items.update', $kcItem) }}" novalidate
      x-data="kcItemForm()">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

        {{-- ── Nội dung chính ──────────────────────────────────────────── --}}
        <div class="space-y-5">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body space-y-4">

                    <h2 class="card-title text-base">Thông tin cơ bản</h2>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tiêu đề <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $kcItem->title) }}"
                               @input="onTitleInput($event.target.value)"
                               class="input input-bordered input-sm w-full @error('title') input-error @enderror">
                        @error('title')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Slug <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="slug" x-model="slug"
                               x-init="slug = '{{ old('slug', $kcItem->slug) }}'"
                               class="input input-bordered input-sm w-full font-mono @error('slug') input-error @enderror">
                        @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1.5">
                            <span class="label-text font-medium">Tóm tắt</span>
                        </label>
                        <textarea name="summary" rows="2"
                                  class="textarea textarea-bordered textarea-sm w-full @error('summary') textarea-error @enderror"
                                  >{{ old('summary', $kcItem->summary) }}</textarea>
                        @error('summary')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body space-y-3">
                    <h2 class="card-title text-base">Nội dung</h2>
                    <textarea name="content" rows="16"
                              class="textarea textarea-bordered w-full font-mono text-sm @error('content') textarea-error @enderror"
                              >{{ old('content', $kcItem->content) }}</textarea>
                    @error('content')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- File đính kèm --}}
            @can('update', $kcItem)
            <div class="card bg-base-100 shadow-sm border border-base-200"
                 x-data="kcAttachmentManager({
                     uploadUrl: '{{ route('backend.api.kc-items.attachments.store', $kcItem) }}',
                     maxMb: {{ config('kc.attachments.max_file_size_mb', 50) }},
                     maxTotalMb: {{ config('kc.attachments.max_item_total_mb', 200) }},
                 })">
                <div class="card-body space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">File đính kèm</h2>
                        <span class="text-xs text-base-content/40" x-text="totalLabel + ' / {{ config('kc.attachments.max_item_total_mb', 200) }}MB'"></span>
                    </div>

                    {{-- Existing files --}}
                    <div id="kc-attach-existing"
                         data-files="{{ json_encode($kcItem->attachments->map(fn($a) => [
                             'id' => $a->id,
                             'file_name' => $a->file_name,
                             'file_url' => $a->file_url,
                             'file_type' => $a->file_type,
                             'file_size_kb' => $a->file_size_kb,
                             'delete_url' => route('backend.api.kc-items.attachments.destroy', [$kcItem, $a]),
                         ])) }}">
                    </div>

                    <div x-show="files.length > 0" class="space-y-1.5">
                        <template x-for="(f, i) in files" :key="f.id || i">
                            <div class="flex items-center gap-2 p-2 rounded-lg border border-base-200 text-xs">
                                <svg class="w-4 h-4 shrink-0 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <a :href="f.file_url" target="_blank" class="flex-1 truncate hover:text-primary" x-text="f.file_name"></a>
                                <span class="text-base-content/30 shrink-0" x-text="formatSize(f.file_size_kb)"></span>
                                <button type="button" @click="deleteFile(f, i)"
                                        class="btn btn-ghost btn-xs btn-square text-error/40 hover:text-error shrink-0" title="Xóa file">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Upload zone --}}
                    <label class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border-2 border-dashed border-base-300 hover:border-primary/50 hover:bg-base-200/50 transition-colors cursor-pointer text-center"
                           @dragover.prevent @drop.prevent="onFileDrop($event)">
                        <input type="file" multiple class="hidden" @change="onFileSelect($event)"
                               accept="{{ implode(',', array_map(fn($e) => '.'.$e, config('kc.attachments.allowed_extensions', []))) }}">
                        <svg class="w-7 h-7 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-primary">Chọn file</span>
                            <span class="text-sm text-base-content/50"> hoặc kéo thả vào đây</span>
                        </div>
                        <p class="text-xs text-base-content/30">PDF, DOCX, XLSX, PNG, JPG, MP4, ZIP — tối đa {{ config('kc.attachments.max_file_size_mb', 50) }}MB/file</p>
                    </label>

                    <div x-show="uploading" class="flex items-center gap-2 text-xs text-base-content/50">
                        <span class="loading loading-spinner loading-xs"></span> Đang upload...
                    </div>

                    <p x-show="error" x-text="error" class="text-xs text-error"></p>
                </div>
            </div>
            @endcan

        </div>

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-4 space-y-4">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide mb-3">Xuất bản</p>
                    <div class="flex gap-2">
                        <a href="{{ route('backend.kc-items.show', $kcItem) }}" class="btn btn-ghost btn-sm flex-1">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm flex-1">Lưu</button>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Phân loại</p>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Loại tài liệu <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-type" name="type" class="select select-bordered select-sm w-full">
                            @foreach($types as $t)
                            <option value="{{ $t['value'] }}" {{ old('type', $kcItem->type instanceof \Modules\KcItem\Enums\KcItemType ? $kcItem->type->value : $kcItem->type) === $t['value'] ? 'selected' : '' }}>
                                {{ $t['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Danh mục <span class="text-error">*</span></span>
                        </label>
                        <select id="ts-category" name="category_id" class="select select-bordered select-sm w-full">
                            @foreach($categories as $cat)
                            <option value="{{ $cat['value'] }}" {{ old('category_id', $kcItem->category_id) == $cat['value'] ? 'selected' : '' }}>
                                {{ $cat['text'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-sm font-medium">Phạm vi hiển thị</span>
                        </label>
                        <select id="ts-visibility" name="visibility" class="select select-bordered select-sm w-full">
                            @foreach($visibilities as $v)
                            <option value="{{ $v['value'] }}" {{ old('visibility', $kcItem->visibility instanceof \Modules\KcItem\Enums\KcItemVisibility ? $kcItem->visibility->value : $kcItem->visibility) === $v['value'] ? 'selected' : '' }}>
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
                                class="select select-bordered select-sm w-full">
                            @foreach(old('tags', $selectedTags) as $tagId)
                            <option value="{{ $tagId }}" selected>{{ $tagId }}</option>
                            @endforeach
                        </select>
                        @error('tags')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Tùy chọn</p>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_featured', $kcItem->is_featured) ? 'checked' : '' }}>
                        <span class="text-sm font-medium">Nổi bật</span>
                    </label>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="hidden" name="is_pinned" value="0">
                        <input type="checkbox" name="is_pinned" value="1"
                               class="checkbox checkbox-sm checkbox-secondary"
                               {{ old('is_pinned', $kcItem->is_pinned) ? 'checked' : '' }}>
                        <span class="text-sm font-medium">Ghim đầu</span>
                    </label>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 space-y-3">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">Hiệu lực</p>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs">Ngày có hiệu lực</span>
                        </label>
                        <input type="date" name="effective_date"
                               value="{{ old('effective_date', $kcItem->effective_date?->format('Y-m-d')) }}"
                               class="input input-bordered input-sm w-full @error('effective_date') input-error @enderror">
                        @error('effective_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label py-0 pb-1">
                            <span class="label-text text-xs">Ngày hết hiệu lực</span>
                        </label>
                        <input type="date" name="expired_date"
                               value="{{ old('expired_date', $kcItem->expired_date?->format('Y-m-d')) }}"
                               class="input input-bordered input-sm w-full @error('expired_date') input-error @enderror">
                        @error('expired_date')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

        </div>

    </div>
</form>

@endsection

@push('scripts')
    @vite([
        'resources/js/modules/tom-select.js',
        'Modules/KcItem/resources/assets/js/kc-item.js',
    ], 'build/backend')
@endpush

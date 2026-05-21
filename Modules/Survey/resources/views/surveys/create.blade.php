@extends('layouts.backend')

@section('title', 'Tạo khảo sát mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <span class="current">Tạo mới</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-base-content mb-6">Tạo khảo sát mới</h1>

    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-6">
            <form method="POST" action="{{ route('backend.surveys.store') }}"
                  x-data="surveyForm()">
                @csrf

                {{-- Title --}}
                <div class="form-control mb-4">
                    <label class="label pb-1">
                        <span class="label-text font-semibold">Tiêu đề <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title') }}"
                           class="input input-bordered @error('title') input-error @enderror"
                           placeholder="VD: Khảo sát độ hài lòng khách hàng 2024"
                           x-model="title"
                           @input="generateSlug()"
                           required>
                    @error('title')
                    <span class="label-text-alt text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Slug --}}
                <div class="form-control mb-4">
                    <label class="label pb-1">
                        <span class="label-text font-semibold">Slug URL</span>
                        <span class="label-text-alt text-base-content/40">Tự động tạo từ tiêu đề</span>
                    </label>
                    <div class="flex gap-2 items-center">
                        <span class="text-sm text-base-content/40 shrink-0">/surveys/</span>
                        <input type="text" name="slug"
                               class="input input-bordered input-sm flex-1 font-mono @error('slug') input-error @enderror"
                               placeholder="ten-khao-sat"
                               x-model="slug">
                    </div>
                    @error('slug')
                    <span class="label-text-alt text-error mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Version --}}
                <div class="form-control mb-6">
                    <label class="label pb-1">
                        <span class="label-text font-semibold">Version</span>
                        <span class="label-text-alt text-base-content/40">Mặc định: 1</span>
                    </label>
                    <input type="number" name="version"
                           value="{{ old('version', 1) }}"
                           class="input input-bordered input-sm w-32"
                           min="1" max="9999">
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('backend.surveys.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
                    <button type="submit" class="btn btn-primary btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Tạo khảo sát
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function surveyForm() {
    return {
        title: @js(old('title', '')),
        slug:  @js(old('slug', '')),
        userEditedSlug: {{ old('slug') ? 'true' : 'false' }},

        generateSlug() {
            if (this.userEditedSlug) return;
            this.slug = this.title
                .toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/đ/g, 'd').replace(/Đ/g, 'd')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .substring(0, 160);
        },
    }
}
</script>
@endpush

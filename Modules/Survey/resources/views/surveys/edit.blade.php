@extends('layouts.backend')

@section('title', 'Chỉnh sửa: ' . $survey->title)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.surveys.index') }}">Khảo sát</a>
    <span class="sep">›</span>
    <span class="current">{{ Str::limit($survey->title, 40) }}</span>
</nav>
@endsection

@section('content')
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">{{ $survey->title }}</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="badge badge-sm {{ $survey->status->badgeClass() }}">{{ $survey->status->label() }}</span>
            <span class="text-xs text-base-content/40 font-mono">/surveys/{{ $survey->slug }}</span>
            <span class="text-xs text-base-content/40">· v{{ $survey->version }}</span>
        </div>
    </div>

    {{-- Activate button --}}
    @can('survey.update')
    @if($survey->status->value === 0)
    <form method="POST" action="{{ route('backend.surveys.activate', $survey) }}"
          onsubmit="return confirm('Kích hoạt survey? Sau khi active, slug sẽ bị khóa.')">
        @csrf
        <button type="submit" class="btn btn-success btn-sm gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Kích hoạt (Active)
        </button>
    </form>
    @endif
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: General info form --}}
    <div class="lg:col-span-1">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-5">
                <h2 class="font-bold text-base mb-4">Thông tin chung</h2>

                <form method="POST" action="{{ route('backend.surveys.update', $survey) }}">
                    @csrf @method('PUT')

                    {{-- Title --}}
                    <div class="form-control mb-4">
                        <label class="label pb-1">
                            <span class="label-text font-semibold text-sm">Tiêu đề <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title"
                               value="{{ old('title', $survey->title) }}"
                               class="input input-bordered input-sm @error('title') input-error @enderror"
                               required>
                        @error('title')
                        <span class="label-text-alt text-error mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Slug (locked when active) --}}
                    <div class="form-control mb-4">
                        <label class="label pb-1">
                            <span class="label-text font-semibold text-sm">Slug</span>
                            @if($survey->status->value === 1)
                            <span class="label-text-alt text-warning text-xs">🔒 Đã khóa</span>
                            @endif
                        </label>
                        <input type="text" name="slug"
                               value="{{ old('slug', $survey->slug) }}"
                               class="input input-bordered input-sm font-mono @error('slug') input-error @enderror"
                               {{ $survey->status->value === 1 ? 'readonly' : '' }}>
                        @error('slug')
                        <span class="label-text-alt text-error mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Version --}}
                    <div class="form-control mb-5">
                        <label class="label pb-1">
                            <span class="label-text font-semibold text-sm">Version</span>
                        </label>
                        <input type="number" name="version"
                               value="{{ old('version', $survey->version) }}"
                               class="input input-bordered input-sm w-24"
                               min="1" max="9999">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats card --}}
        <div class="card bg-base-100 shadow-sm border border-base-200 mt-4"
             x-data="{
                 totalResponses: {{ $survey->responses()->complete()->count() }},
                 totalSections:  {{ $survey->sections->count() }},
                 activeFields:   {{ $survey->sections->sum(fn($s) => $s->fields->where('is_active', true)->count()) }}
             }"
             @survey-stats-updated.window="
                 totalSections = $event.detail.sections;
                 activeFields  = $event.detail.activeFields;
             ">
            <div class="card-body p-5">
                <h2 class="font-bold text-base mb-3">Thống kê nhanh</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Tổng responses</span>
                        <span class="font-semibold" x-text="totalResponses"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Số sections</span>
                        <span class="font-semibold" x-text="totalSections"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Số fields active</span>
                        <span class="font-semibold" x-text="activeFields"></span>
                    </div>
                </div>
                @can('survey.view_responses')
                <div class="divider my-3"></div>
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <a href="{{ route('backend.surveys.responses.index', $survey) }}"
                           class="btn btn-ghost btn-xs flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Responses
                        </a>
                        <a href="{{ route('backend.surveys.stats.index', $survey) }}"
                           class="btn btn-ghost btn-xs flex-1 gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Thống kê
                        </a>
                    </div>
                    @can('survey.export')
                    <a href="{{ route('backend.surveys.responses.export', $survey) }}"
                       class="btn btn-ghost btn-xs w-full gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel
                    </a>
                    @endcan
                </div>
                @endcan

                @can('survey.manage_tokens')
                <div class="divider my-3"></div>
                <a href="{{ route('backend.surveys.tokens.index', $survey) }}"
                   class="btn btn-outline btn-sm w-full gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Quản lý API Tokens
                    <span class="badge badge-sm badge-ghost">{{ $survey->tokens()->active()->count() }}</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Right: Survey Builder --}}
    <div class="lg:col-span-2">
        @include('survey::builder.index')
    </div>

</div>
@endsection

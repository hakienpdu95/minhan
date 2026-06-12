@extends('layouts.backend')
@section('title', 'Tạo AI Agent mới')

@section('content')
<script>
    window._aiAgentModels = {!! Js::from($modelsByProvider) !!};
</script>
<div x-data="{
    provider: '{{ old('provider', 'claude') }}',
    modelsByProvider: window._aiAgentModels,
    get models() { return this.modelsByProvider[this.provider] ?? [] }
}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Tạo AI Agent mới</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Custom agent cho tổ chức</p>
        </div>
        <a href="{{ route('ai.agents.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Quay lại
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-error py-3 px-4 mb-5 text-sm flex gap-3">
        <div>
            <p class="font-semibold">{{ $errors->count() }} lỗi:</p>
            <ul class="list-disc list-inside mt-1 text-xs space-y-0.5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('ai.agents.store') }}" novalidate>
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-5">

                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Tên agent <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="input input-bordered input-sm @error('name') input-error @enderror"
                               placeholder="VD: Custom KPI Reviewer">
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium">Slug <span class="text-error">*</span></span>
                            <span class="label-text-alt text-base-content/50">Dùng định dạng: category.action</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug') }}"
                               class="input input-bordered input-sm font-mono @error('slug') input-error @enderror"
                               placeholder="vd: kpi.custom_review">
                        @error('slug')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Mô tả</span></label>
                        <textarea name="description" rows="2"
                                  class="textarea textarea-bordered textarea-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Provider <span class="text-error">*</span></span></label>
                            <select name="provider" x-model="provider" class="select select-bordered select-sm">
                                @foreach($providers as $p)
                                <option value="{{ $p }}" @selected(old('provider') === $p)>{{ strtoupper($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Model <span class="text-error">*</span></span></label>
                            <select name="model" class="select select-bordered select-sm">
                                <template x-for="m in models" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Loại task <span class="text-error">*</span></span></label>
                        <select name="task_type" class="select select-bordered select-sm">
                            @foreach($taskTypes as $t)
                            <option value="{{ $t }}" @selected(old('task_type') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div class="flex flex-col gap-4">

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-4">
                        <h3 class="font-semibold text-sm">Tham số</h3>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Temperature</span></label>
                            <input type="number" name="temperature" step="0.05" min="0" max="2" value="{{ old('temperature', 0.70) }}"
                                   class="input input-bordered input-sm">
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Max tokens</span></label>
                            <input type="number" name="max_tokens" min="64" max="16000" value="{{ old('max_tokens', 1024) }}"
                                   class="input input-bordered input-sm">
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Timeout (giây)</span></label>
                            <input type="number" name="timeout_seconds" min="5" max="120" value="{{ old('timeout_seconds', 30) }}"
                                   class="input input-bordered input-sm">
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <h3 class="font-semibold text-sm">Cài đặt</h3>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm">Active ngay</span>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="toggle toggle-sm toggle-success" checked>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm">Sync mode</span>
                                <p class="text-xs text-base-content/50">Không dùng queue</p>
                            </div>
                            <input type="hidden" name="sync_mode" value="0">
                            <input type="checkbox" name="sync_mode" value="1" class="toggle toggle-sm">
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-full">Tạo Agent</button>
            </div>

        </div>
    </form>

</div>
@endsection

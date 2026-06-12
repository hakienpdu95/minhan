@extends('layouts.backend')
@section('title', ($agent->is_system ? 'Cấu hình system agent' : 'Sửa agent') . ' — ' . $agent->name)

@section('content')
<script>
    window._aiAgentModels    = {!! Js::from($modelsByProvider) !!};
    window._aiCurrentModel   = {!! Js::from(old('model',    $agent->model)) !!};
    window._aiCurrentProvider = {!! Js::from(old('provider', $agent->provider)) !!};
</script>
<div x-data="{
    provider: window._aiCurrentProvider,
    modelsByProvider: window._aiAgentModels,
    currentModel: window._aiCurrentModel,
    get models() { return this.modelsByProvider[this.provider] ?? [] }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">
                {{ $agent->is_system ? 'Cấu hình system agent' : 'Sửa agent' }}
            </h1>
            <p class="font-mono text-sm text-base-content/50 mt-0.5">{{ $agent->slug }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ai.prompts.index', ['agent_id' => $agent->id]) }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Prompts
            </a>
            <a href="{{ route('ai.agents.index') }}" class="btn btn-ghost btn-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Quay lại
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-error py-3 px-4 mb-5 text-sm flex gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="font-semibold">{{ $errors->count() }} lỗi:</p>
            <ul class="list-disc list-inside mt-1 text-xs space-y-0.5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    @if($agent->is_system)
    <div class="alert alert-info py-3 px-4 mb-5 text-sm gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        System agent: chỉ có thể thay đổi tên, mô tả, nhiệt độ, token limit, timeout và trạng thái. Slug, provider, model là bất biến.
    </div>
    @endif

    <form method="POST" action="{{ route('ai.agents.update', $agent) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6 items-start">

            {{-- Main card --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body gap-5">

                    {{-- Name --}}
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Tên agent <span class="text-error">*</span></span></label>
                        <input type="text" name="name" value="{{ old('name', $agent->name) }}"
                               class="input input-bordered input-sm @error('name') input-error @enderror"
                               placeholder="VD: KPI Analyst (Custom)">
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Slug (locked for system) --}}
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Slug <span class="text-error">*</span></span></label>
                        @if($agent->is_system)
                        <input type="text" value="{{ $agent->slug }}" class="input input-bordered input-sm bg-base-200" readonly>
                        <input type="hidden" name="slug" value="{{ $agent->slug }}">
                        @else
                        <input type="text" name="slug" value="{{ old('slug', $agent->slug) }}"
                               class="input input-bordered input-sm font-mono @error('slug') input-error @enderror"
                               placeholder="vd: kpi.analysis">
                        @endif
                        @error('slug')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Mô tả</span></label>
                        <textarea name="description" rows="2"
                                  class="textarea textarea-bordered textarea-sm @error('description') textarea-error @enderror"
                                  placeholder="Mô tả ngắn về chức năng của agent...">{{ old('description', $agent->description) }}</textarea>
                        @error('description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Provider + Model --}}
                    @if(!$agent->is_system)
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Provider <span class="text-error">*</span></span></label>
                            <select name="provider" x-model="provider"
                                    class="select select-bordered select-sm @error('provider') select-error @enderror">
                                @foreach($providers as $p)
                                <option value="{{ $p }}" @selected(old('provider', $agent->provider) === $p)>{{ strtoupper($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Model <span class="text-error">*</span></span></label>
                            <select name="model" class="select select-bordered select-sm @error('model') select-error @enderror">
                                <template x-for="m in models" :key="m">
                                    <option :value="m" :selected="m === currentModel" x-text="m"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Task type --}}
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text font-medium">Loại task <span class="text-error">*</span></span></label>
                        <select name="task_type" class="select select-bordered select-sm @error('task_type') select-error @enderror">
                            @foreach($taskTypes as $t)
                            <option value="{{ $t }}" @selected(old('task_type', $agent->task_type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    {{-- Hidden fields for system agents (locked values) --}}
                    <input type="hidden" name="provider"  value="{{ $agent->provider }}">
                    <input type="hidden" name="model"     value="{{ $agent->model }}">
                    <input type="hidden" name="task_type" value="{{ $agent->task_type }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-base-content/50 font-medium uppercase">Provider</p>
                            <p class="font-semibold text-sm">{{ strtoupper($agent->provider) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/50 font-medium uppercase">Model</p>
                            <p class="font-mono text-sm">{{ $agent->model }}</p>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Sidebar --}}
            <div class="flex flex-col gap-4">

                {{-- Config params --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-4">
                        <h3 class="font-semibold text-sm">Tham số</h3>

                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Temperature</span></label>
                            <input type="number" name="temperature" step="0.05" min="0" max="2"
                                   value="{{ old('temperature', $agent->temperature) }}"
                                   class="input input-bordered input-sm @error('temperature') input-error @enderror">
                        </div>

                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Max tokens</span></label>
                            <input type="number" name="max_tokens" min="64" max="16000"
                                   value="{{ old('max_tokens', $agent->max_tokens) }}"
                                   class="input input-bordered input-sm @error('max_tokens') input-error @enderror">
                        </div>

                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm">Timeout (giây)</span></label>
                            <input type="number" name="timeout_seconds" min="5" max="120"
                                   value="{{ old('timeout_seconds', $agent->timeout_seconds) }}"
                                   class="input input-bordered input-sm @error('timeout_seconds') input-error @enderror">
                        </div>
                    </div>
                </div>

                {{-- Toggles --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <h3 class="font-semibold text-sm">Cài đặt</h3>

                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm">Active</span>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="toggle toggle-sm toggle-success"
                                   @checked(old('is_active', $agent->is_active) == '1' || old('is_active', $agent->is_active) === true)>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm">Sync mode</span>
                                <p class="text-xs text-base-content/50">Thực thi đồng bộ (không queue)</p>
                            </div>
                            <input type="hidden" name="sync_mode" value="0">
                            <input type="checkbox" name="sync_mode" value="1" class="toggle toggle-sm"
                                   @checked(old('sync_mode', $agent->sync_mode) == '1' || old('sync_mode', $agent->sync_mode) === true)>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-full">Lưu thay đổi</button>
            </div>

        </div>
    </form>

</div>
@endsection

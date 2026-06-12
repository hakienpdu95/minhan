@extends('layouts.backend')
@section('title', 'Sửa Prompt — ' . $prompt->name)

@section('content')
<div x-data="{
    variables: {{ Js::from(old('variables_schema', $prompt->variables_schema ?? [])) }},
    addVar()     { this.variables.push({ key: '', type: 'string', required: true }) },
    removeVar(i) { this.variables.splice(i, 1) },
}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Sửa Prompt</h1>
            <p class="text-sm text-base-content/50 mt-0.5">{{ $prompt->agent?->name }} · v{{ $prompt->version }}</p>
        </div>
        <a href="{{ route('ai.prompts.index', ['agent_id' => $prompt->agent_id]) }}" class="btn btn-ghost btn-sm gap-1.5">
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

    @if(is_null($prompt->organization_id))
    <div class="alert alert-warning py-3 px-4 mb-5 text-sm gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Đây là system prompt. Thay đổi sẽ áp dụng cho toàn bộ tổ chức dùng agent này.
    </div>
    @endif

    <form method="POST" action="{{ route('ai.prompts.update', $prompt) }}" novalidate>
        @csrf
        @method('PUT')
        {{-- agent_id must be passed --}}
        <input type="hidden" name="agent_id" value="{{ $prompt->agent_id }}">

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

            <div class="flex flex-col gap-5">

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-4">
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Tên prompt <span class="text-error">*</span></span></label>
                            <input type="text" name="name" value="{{ old('name', $prompt->name) }}"
                                   class="input input-bordered input-sm @error('name') input-error @enderror">
                            @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Mô tả</span></label>
                            <input type="text" name="description" value="{{ old('description', $prompt->description) }}"
                                   class="input input-bordered input-sm">
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <h3 class="font-semibold text-sm">System Prompt <span class="text-error">*</span></h3>
                        <textarea name="system_prompt" rows="6"
                                  class="textarea textarea-bordered text-sm font-mono @error('system_prompt') textarea-error @enderror">{{ old('system_prompt', $prompt->system_prompt) }}</textarea>
                        @error('system_prompt')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-sm">User Template <span class="text-error">*</span></h3>
                            <span class="text-xs text-base-content/50">Dùng <code class="font-mono">{{ '{' }}{{ '{' }}key{{ '}' }}{{ '}' }}</code></span>
                        </div>
                        <textarea name="user_template" rows="8"
                                  class="textarea textarea-bordered text-sm font-mono @error('user_template') textarea-error @enderror">{{ old('user_template', $prompt->user_template) }}</textarea>
                        @error('user_template')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-sm">Biến (Variables)</h3>
                            <button type="button" @click="addVar()" class="btn btn-ghost btn-xs gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Thêm biến
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(v, i) in variables" :key="i">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="`variables_schema[${i}][key]`" x-model="v.key"
                                           class="input input-bordered input-xs font-mono flex-1" placeholder="variable_key">
                                    <select :name="`variables_schema[${i}][type]`" x-model="v.type"
                                            class="select select-bordered select-xs w-24">
                                        <option value="string">string</option>
                                        <option value="text">text</option>
                                        <option value="integer">integer</option>
                                        <option value="boolean">boolean</option>
                                    </select>
                                    <label class="flex items-center gap-1 text-xs cursor-pointer">
                                        <input type="checkbox" :name="`variables_schema[${i}][required]`" value="1"
                                               x-model="v.required" class="checkbox checkbox-xs">
                                        Required
                                    </label>
                                    <button type="button" @click="removeVar(i)" class="btn btn-ghost btn-xs text-error shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <template x-if="variables.length === 0">
                            <p class="text-xs text-base-content/40 italic">Chưa có biến nào.</p>
                        </template>
                    </div>
                </div>

            </div>

            <div class="flex flex-col gap-4">
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <h3 class="font-semibold text-sm">Cài đặt</h3>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm">Active</span>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="toggle toggle-sm toggle-success"
                                   @checked(old('is_active', $prompt->is_active) == '1' || old('is_active', $prompt->is_active) === true)>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <span class="text-sm">Default</span>
                                <p class="text-xs text-base-content/50">Prompt mặc định cho agent</p>
                            </div>
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" class="toggle toggle-sm toggle-primary"
                                   @checked(old('is_default', $prompt->is_default))>
                        </label>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-2">
                        <p class="text-xs text-base-content/50 font-medium uppercase">Thông tin</p>
                        <div class="text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-base-content/50">Agent</span>
                                <span class="font-mono">{{ $prompt->agent?->slug }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/50">Version</span>
                                <span>v{{ $prompt->version }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/50">Scope</span>
                                <span>{{ is_null($prompt->organization_id) ? 'System' : 'Org' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-full">Lưu thay đổi</button>
            </div>

        </div>
    </form>

</div>
@endsection

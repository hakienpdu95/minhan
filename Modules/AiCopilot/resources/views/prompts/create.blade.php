@extends('layouts.backend')
@section('title', 'Tạo Prompt mới')

@section('content')
<div x-data="{
    variables: {{ Js::from(old('variables_schema', [])) }},
    addVar()  { this.variables.push({ key: '', type: 'string', required: true }) },
    removeVar(i) { this.variables.splice(i, 1) },
}">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Tạo Prompt mới</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Template prompt cho AI agent</p>
        </div>
        <a href="{{ route('ai.prompts.index') }}" class="btn btn-ghost btn-sm gap-1.5">
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

    <form method="POST" action="{{ route('ai.prompts.store') }}" novalidate>
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

            <div class="flex flex-col gap-5">

                {{-- Agent --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-4">
                        <h3 class="font-semibold text-sm">Thông tin cơ bản</h3>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Agent <span class="text-error">*</span></span></label>
                            <select name="agent_id" class="select select-bordered select-sm @error('agent_id') select-error @enderror">
                                <option value="">-- Chọn agent --</option>
                                @foreach($agents as $ag)
                                <option value="{{ $ag->id }}"
                                    @selected(old('agent_id', $selectedAgentId) == $ag->id)>
                                    {{ $ag->name }} ({{ $ag->slug }})
                                    @if($ag->is_system) [system]@endif
                                </option>
                                @endforeach
                            </select>
                            @error('agent_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Tên prompt <span class="text-error">*</span></span></label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="input input-bordered input-sm @error('name') input-error @enderror"
                                   placeholder="VD: KPI Analysis v2 — Vietnamese">
                            @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text font-medium">Mô tả</span></label>
                            <input type="text" name="description" value="{{ old('description') }}"
                                   class="input input-bordered input-sm"
                                   placeholder="Ngắn gọn về mục đích của prompt này">
                        </div>
                    </div>
                </div>

                {{-- System prompt --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-sm">System Prompt <span class="text-error">*</span></h3>
                            <span class="text-xs text-base-content/50">Vai trò AI, hướng dẫn tổng quát</span>
                        </div>
                        <textarea name="system_prompt" rows="6"
                                  class="textarea textarea-bordered text-sm font-mono @error('system_prompt') textarea-error @enderror"
                                  placeholder="Bạn là chuyên gia phân tích KPI. Hãy...">{{ old('system_prompt') }}</textarea>
                        @error('system_prompt')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- User template --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-sm">User Template <span class="text-error">*</span></h3>
                            <span class="text-xs text-base-content/50">Dùng <code class="font-mono">{{ '{' }}{{ '{' }}variable_name{{ '}' }}{{ '}' }}</code> cho biến động</span>
                        </div>
                        <textarea name="user_template" rows="8"
                                  class="textarea textarea-bordered text-sm font-mono @error('user_template') textarea-error @enderror"
                                  placeholder="Phân tích KPI sau:&#10;Tên: {{ '{' }}{{ '{' }}employee_name{{ '}' }}{{ '}' }}&#10;Mục tiêu: {{ '{' }}{{ '{' }}target_value{{ '}' }}{{ '}' }}">{{ old('user_template') }}</textarea>
                        @error('user_template')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Variables schema --}}
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
                                    <button type="button" @click="removeVar(i)"
                                            class="btn btn-ghost btn-xs text-error shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <template x-if="variables.length === 0">
                            <p class="text-xs text-base-content/40 italic">Chưa có biến nào. Nhấn "Thêm biến" để khai báo.</p>
                        </template>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="flex flex-col gap-4">
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
                                <span class="text-sm">Đặt làm Default</span>
                                <p class="text-xs text-base-content/50">Prompt mặc định cho agent này</p>
                            </div>
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" class="toggle toggle-sm toggle-primary"
                                   @checked(old('is_default'))>
                        </label>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body gap-2">
                        <h3 class="font-semibold text-sm">Cú pháp biến</h3>
                        <p class="text-xs text-base-content/60">Trong user template, dùng:</p>
                        <code class="text-xs bg-base-200 px-2 py-1 rounded block font-mono">{{ '{' }}{{ '{' }}variable_name{{ '}' }}{{ '}' }}</code>
                        <p class="text-xs text-base-content/60 mt-1">Ví dụ: <code class="font-mono text-primary">{{ '{' }}{{ '{' }}employee_name{{ '}' }}{{ '}' }}</code></p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-full">Tạo Prompt</button>
            </div>

        </div>
    </form>

</div>
@endsection

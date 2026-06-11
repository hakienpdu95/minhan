@extends('layouts.backend')
@section('title', $env ? 'Sửa môi trường' : 'Thêm môi trường Sandbox')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('backend.sandbox-admin.index') }}" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold">{{ $env ? 'Sửa: '.$env->name : 'Thêm môi trường Sandbox' }}</h1>
        @if($env)
        <p class="text-xs text-base-content/40 mt-0.5">
            @if($env->organization_id === null)
                <span class="badge badge-info badge-xs">Hệ thống — dùng chung</span>
            @else
                <span class="badge badge-ghost badge-xs">Riêng: {{ $currentOrg?->name ?? 'Tổ chức #'.$env->organization_id }}</span>
            @endif
        </p>
        @endif
    </div>
</div>

<div class="max-w-2xl">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <form method="POST"
                  action="{{ $env ? route('backend.sandbox-admin.env.update', $env) : route('backend.sandbox-admin.env.store') }}">
                @csrf
                @if($env) @method('PUT') @endif

                {{-- === Phạm vi tổ chức === --}}
                <div class="mb-5 p-4 rounded-lg {{ $isSuperAdmin ? 'bg-warning/5 border border-warning/20' : 'bg-base-200/50 border border-base-200' }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/50 mb-2">Phạm vi sử dụng</p>

                    @if($env)
                        {{-- Edit mode: phạm vi không thể thay đổi --}}
                        <div class="flex items-center gap-2">
                            @if($env->organization_id === null)
                                <svg class="w-4 h-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                <span class="text-sm font-medium text-info">Toàn hệ thống</span>
                                <span class="text-xs text-base-content/40">— Template dùng chung cho mọi tổ chức</span>
                            @else
                                <svg class="w-4 h-4 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-sm font-medium">Riêng cho: {{ $currentOrg?->name ?? 'tổ chức này' }}</span>
                                <span class="text-xs text-base-content/40">— Chỉ nhân viên tổ chức bạn thấy</span>
                            @endif
                        </div>
                        <p class="text-xs text-base-content/30 mt-1">Phạm vi không thể thay đổi sau khi tạo.</p>
                    @elseif($isSuperAdmin)
                        {{-- Create + super-admin: chọn scope + org cụ thể --}}
                        <div x-data="{ scope: '{{ old('scope', 'global') }}' }">
                            <div class="flex gap-4 flex-wrap mb-3">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="scope" value="global" class="radio radio-sm radio-info mt-0.5"
                                           x-model="scope" {{ old('scope', 'global') === 'global' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-medium">Toàn hệ thống</p>
                                        <p class="text-xs text-base-content/40">Template dùng chung cho tất cả tổ chức</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="radio" name="scope" value="org" class="radio radio-sm radio-primary mt-0.5"
                                           x-model="scope" {{ old('scope') === 'org' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-medium">Riêng cho tổ chức cụ thể</p>
                                        <p class="text-xs text-base-content/40">Chỉ tổ chức được chọn mới thấy</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Org selector: chỉ hiện khi scope = org --}}
                            <div x-show="scope === 'org'" x-cloak class="mt-2">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Chọn tổ chức <span class="text-error">*</span></span>
                                </label>
                                <select name="organization_id" class="select select-bordered select-sm w-full max-w-xs @error('organization_id') select-error @enderror">
                                    <option value="">-- Chọn tổ chức --</option>
                                    @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('organization_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                                @if($organizations->isEmpty())
                                <p class="text-xs text-warning mt-1">Chưa có tổ chức doanh nghiệp nào trong hệ thống.</p>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Create + org-admin: luôn là org mình --}}
                        <input type="hidden" name="scope" value="org">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="text-sm">Riêng cho: <strong>{{ $currentOrg?->name ?? 'Tổ chức của bạn' }}</strong></span>
                        </div>
                        <p class="text-xs text-base-content/40 mt-1">Môi trường sẽ chỉ hiển thị cho nhân viên trong tổ chức của bạn.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="form-control col-span-2">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Tên môi trường <span class="text-error">*</span></span></label>
                        <input type="text" name="name" class="input input-bordered input-sm @error('name') input-error @enderror"
                               value="{{ old('name', $env?->name) }}" placeholder="VD: AI Văn phòng — Foundation">
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Env Code --}}
                    @if(! $env)
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Mã (env_code) <span class="text-error">*</span></span></label>
                        <input type="text" name="env_code" class="input input-bordered input-sm font-mono @error('env_code') input-error @enderror"
                               value="{{ old('env_code') }}" placeholder="AI_OFFICE_F1">
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/40">Chỉ gồm CHỮ HOA, số và gạch dưới</span></label>
                        @error('env_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif

                    {{-- Type --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Loại kỹ năng <span class="text-error">*</span></span></label>
                        <select name="type" class="select select-bordered select-sm">
                            @foreach(['office' => 'Văn phòng số', 'data' => 'Phân tích dữ liệu', 'sales' => 'Kinh doanh', 'hr' => 'Nhân sự', 'workflow' => 'Quy trình làm việc', 'leadership' => 'Lãnh đạo & Chiến lược', 'custom' => 'Tuỳ chỉnh'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $env?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tier --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Cấp độ (Tier) <span class="text-error">*</span></span></label>
                        <select name="tier" class="select select-bordered select-sm">
                            <option value="1" {{ old('tier', $env?->tier) == 1 ? 'selected' : '' }}>Tier 1 — Cơ bản (Foundation)</option>
                            <option value="2" {{ old('tier', $env?->tier) == 2 ? 'selected' : '' }}>Tier 2 — Nâng cao (Intermediate)</option>
                            <option value="3" {{ old('tier', $env?->tier) == 3 ? 'selected' : '' }}>Tier 3 — Chuyên sâu (Advanced)</option>
                        </select>
                        @error('tier')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sort order --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Thứ tự hiển thị</span></label>
                        <input type="number" name="sort_order" min="0" class="input input-bordered input-sm"
                               value="{{ old('sort_order', $env?->sort_order ?? 0) }}">
                    </div>

                    {{-- Description --}}
                    <div class="form-control col-span-2">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Mô tả</span></label>
                        <textarea name="description" rows="3" class="textarea textarea-bordered text-sm"
                                  placeholder="Mô tả ngắn về môi trường này...">{{ old('description', $env?->description) }}</textarea>
                    </div>

                    {{-- Active --}}
                    <div class="form-control col-span-2">
                        <label class="label cursor-pointer justify-start gap-3 py-1">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-primary"
                                   {{ old('is_active', $env?->is_active ?? true) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Kích hoạt (hiện cho nhân viên)</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $env ? 'Cập nhật' : 'Tạo môi trường' }}
                    </button>
                    <a href="{{ route('backend.sandbox-admin.index') }}" class="btn btn-ghost btn-sm">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@extends('layouts.backend')
@section('title', $def ? 'Sửa định nghĩa chứng nhận' : 'Thêm định nghĩa chứng nhận')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('backend.certs-admin.index') }}" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <h1 class="text-xl font-bold">{{ $def ? 'Sửa: '.$def->name : 'Thêm định nghĩa chứng nhận' }}</h1>
</div>

<div class="max-w-2xl">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <form method="POST"
                  action="{{ $def ? route('backend.certs-admin.def.update', $def) : route('backend.certs-admin.def.store') }}">
                @csrf
                @if($def) @method('PUT') @endif

                {{-- Phạm vi --}}
                @if(! $def)
                <div class="mb-5 p-4 rounded-lg {{ $isSuperAdmin ? 'bg-warning/5 border border-warning/20' : 'bg-base-200/50 border border-base-200' }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/50 mb-2">Phạm vi</p>
                    @if($isSuperAdmin)
                    <div x-data="{ scope: '{{ old('scope', 'global') }}' }">
                        <div class="flex gap-4 mb-3">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="radio" name="scope" value="global" class="radio radio-sm radio-info mt-0.5" x-model="scope" {{ old('scope','global')==='global'?'checked':'' }}>
                                <div><p class="text-sm font-medium">Toàn hệ thống</p><p class="text-xs text-base-content/40">Template cho tất cả tổ chức</p></div>
                            </label>
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="radio" name="scope" value="org" class="radio radio-sm radio-primary mt-0.5" x-model="scope" {{ old('scope')==='org'?'checked':'' }}>
                                <div><p class="text-sm font-medium">Riêng tổ chức</p><p class="text-xs text-base-content/40">Chỉ tổ chức được chọn</p></div>
                            </label>
                        </div>
                        <div x-show="scope === 'org'" x-cloak>
                            <select name="organization_id" class="select select-bordered select-sm w-full max-w-xs @error('organization_id') select-error @enderror">
                                <option value="">-- Chọn tổ chức --</option>
                                @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id')==$org->id?'selected':'' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="scope" value="org">
                    <p class="text-sm">Riêng cho: <strong>{{ $currentOrg?->name }}</strong></p>
                    @endif
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    {{-- Tên --}}
                    <div class="form-control col-span-2">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Tên chứng nhận <span class="text-error">*</span></span></label>
                        <input type="text" name="name" class="input input-bordered input-sm @error('name') input-error @enderror"
                               value="{{ old('name', $def?->name) }}" placeholder="VD: AI Administrative Officer — Foundation">
                        @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if(! $def)
                    {{-- Cert code --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Cert code <span class="text-error">*</span></span></label>
                        <input type="text" name="cert_code" class="input input-bordered input-sm font-mono @error('cert_code') input-error @enderror"
                               value="{{ old('cert_code') }}" placeholder="AI_ADMIN_FOUNDATION">
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/30">CHỮ HOA, số, gạch dưới — duy nhất</span></label>
                        @error('cert_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Type code --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Type code <span class="text-error">*</span></span></label>
                        <input type="text" name="cert_type_code" class="input input-bordered input-sm font-mono @error('cert_type_code') input-error @enderror"
                               value="{{ old('cert_type_code') }}" placeholder="AI_ADMIN">
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/30">Nhóm cert (dùng để hiển thị lộ trình)</span></label>
                        @error('cert_type_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @else
                    <div class="form-control col-span-2">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Cert code / Type</span></label>
                        <div class="flex gap-2">
                            <span class="input input-bordered input-sm font-mono bg-base-200 flex-1">{{ $def->cert_code }}</span>
                            <span class="input input-bordered input-sm font-mono bg-base-200 flex-1">{{ $def->cert_type_code }}</span>
                        </div>
                        <p class="text-xs text-base-content/30 mt-1">Không thể thay đổi sau khi tạo.</p>
                    </div>
                    @endif

                    {{-- Level --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Cấp độ <span class="text-error">*</span></span></label>
                        <select name="level_code" class="select select-bordered select-sm {{ $def ? 'bg-base-200' : '' }}" {{ $def ? 'disabled' : '' }}>
                            @foreach(['FOUNDATION','PRACTITIONER','PROFESSIONAL','LEADER'] as $lvl)
                            <option value="{{ $lvl }}" {{ old('level_code', $def?->level_code) === $lvl ? 'selected':'' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                        @if($def)<p class="text-xs text-base-content/30 mt-1">Không thể thay đổi.</p>@endif
                    </div>

                    {{-- Validity --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Hiệu lực (tháng) <span class="text-error">*</span></span></label>
                        <input type="number" name="validity_months" min="1" max="120" class="input input-bordered input-sm"
                               value="{{ old('validity_months', $def?->validity_months ?? 24) }}">
                    </div>
                </div>

                {{-- Điều kiện cấp --}}
                <div class="mt-5 mb-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/50 mb-3">Điều kiện cấp chứng nhận</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-medium">TDWCF score tối thiểu</span></label>
                            <input type="number" name="min_workforce_score" min="0" max="100" step="0.01" class="input input-bordered input-sm"
                                   value="{{ old('min_workforce_score', $def?->min_workforce_score) }}" placeholder="VD: 61">
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-medium">KPI tối thiểu (%)</span></label>
                            <input type="number" name="min_kpi_achievement_pct" min="0" max="100" step="0.01" class="input input-bordered input-sm"
                                   value="{{ old('min_kpi_achievement_pct', $def?->min_kpi_achievement_pct) }}" placeholder="VD: 70">
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-medium">Sandbox tối thiểu (giờ)</span></label>
                            <input type="number" name="min_sandbox_hours" min="0" class="input input-bordered input-sm"
                                   value="{{ old('min_sandbox_hours', $def?->min_sandbox_hours) }}" placeholder="VD: 20">
                        </div>
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-xs font-medium">Điểm sandbox tối thiểu</span></label>
                            <input type="number" name="min_sandbox_score" min="0" max="100" step="0.01" class="input input-bordered input-sm"
                                   value="{{ old('min_sandbox_score', $def?->min_sandbox_score) }}" placeholder="VD: 70">
                        </div>
                    </div>
                    <div class="flex gap-6 mt-3">
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="requires_impact_score" value="1" class="checkbox checkbox-sm checkbox-primary"
                                   {{ old('requires_impact_score', $def?->requires_impact_score) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Yêu cầu có AI Impact Score</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" name="requires_portfolio_approval" value="1" class="checkbox checkbox-sm checkbox-primary"
                                   {{ old('requires_portfolio_approval', $def?->requires_portfolio_approval) ? 'checked' : '' }}>
                            <span class="label-text text-sm">Yêu cầu Portfolio được duyệt</span>
                        </label>
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Mô tả</span></label>
                    <textarea name="description" rows="2" class="textarea textarea-bordered text-sm"
                              placeholder="Mô tả chứng nhận này...">{{ old('description', $def?->description) }}</textarea>
                </div>

                {{-- Active --}}
                <div class="form-control mb-5">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', $def?->is_active ?? true) ? 'checked' : '' }}>
                        <span class="label-text text-sm">Kích hoạt (nhân viên có thể nhận chứng nhận này)</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">{{ $def ? 'Cập nhật' : 'Tạo định nghĩa' }}</button>
                    <a href="{{ route('backend.certs-admin.index') }}" class="btn btn-ghost btn-sm">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

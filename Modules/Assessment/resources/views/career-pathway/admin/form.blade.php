@extends('layouts.backend')
@section('title', $step ? 'Sửa bước lộ trình' : 'Thêm bước lộ trình')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('backend.career-pathway-admin.index') }}" class="btn btn-ghost btn-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold">{{ $step ? 'Sửa: '.$step->title : 'Thêm bước lộ trình' }}</h1>
        @if($step)
        <p class="text-xs text-base-content/40 mt-0.5">
            @if($step->organization_id === null)
                <span class="badge badge-info badge-xs">Hệ thống — dùng chung</span>
            @else
                <span class="badge badge-ghost badge-xs">Riêng: {{ $currentOrg?->name }}</span>
            @endif
        </p>
        @endif
    </div>
</div>

<div class="max-w-2xl">
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <form method="POST"
                  action="{{ $step ? route('backend.career-pathway-admin.update', $step) : route('backend.career-pathway-admin.store') }}">
                @csrf
                @if($step) @method('PUT') @endif

                {{-- === Phạm vi === --}}
                @if(! $step)
                <div class="mb-5 p-4 rounded-lg {{ $isSuperAdmin ? 'bg-warning/5 border border-warning/20' : 'bg-base-200/50 border border-base-200' }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-base-content/50 mb-2">Phạm vi</p>

                    @if($isSuperAdmin)
                    <div x-data="{ scope: '{{ old('scope', 'global') }}' }">
                        <div class="flex gap-4 flex-wrap mb-3">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="scope" value="global" class="radio radio-sm radio-info mt-0.5"
                                       x-model="scope" {{ old('scope', 'global') === 'global' ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-medium">Toàn hệ thống</p>
                                    <p class="text-xs text-base-content/40">Áp dụng cho tất cả tổ chức</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="scope" value="org" class="radio radio-sm radio-primary mt-0.5"
                                       x-model="scope" {{ old('scope') === 'org' ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-medium">Riêng tổ chức cụ thể</p>
                                    <p class="text-xs text-base-content/40">Chỉ tổ chức được chọn thấy</p>
                                </div>
                            </label>
                        </div>
                        <div x-show="scope === 'org'" x-cloak class="mt-2">
                            <select name="organization_id" class="select select-bordered select-sm w-full max-w-xs @error('organization_id') select-error @enderror">
                                <option value="">-- Chọn tổ chức --</option>
                                @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('organization_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="scope" value="org">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                        Riêng cho: <strong>{{ $currentOrg?->name }}</strong>
                    </div>
                    @endif
                </div>
                @endif

                {{-- === Cấp độ chuyển tiếp === --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Từ cấp độ <span class="text-error">*</span></span></label>
                        <select name="from_level" class="select select-bordered select-sm @error('from_level') select-error @enderror">
                            @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ old('from_level', $step?->from_level) === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                        @error('from_level')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Lên cấp độ <span class="text-error">*</span></span></label>
                        <select name="to_level" class="select select-bordered select-sm @error('to_level') select-error @enderror">
                            @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ old('to_level', $step?->to_level) === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                        <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/30">Bằng nhau = bước duy trì (không thăng cấp)</span></label>
                        @error('to_level')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Thứ tự + Thời gian ước tính --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Thứ tự bước</span></label>
                        <input type="number" name="step_order" min="0" class="input input-bordered input-sm"
                               value="{{ old('step_order', $step?->step_order ?? 0) }}">
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-xs font-medium">Thời gian ước tính (tuần)</span></label>
                        <input type="number" name="estimated_weeks" min="1" max="52" class="input input-bordered input-sm"
                               value="{{ old('estimated_weeks', $step?->estimated_weeks) }}" placeholder="VD: 8">
                    </div>
                </div>

                {{-- Tiêu đề --}}
                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Tiêu đề bước <span class="text-error">*</span></span></label>
                    <input type="text" name="title" class="input input-bordered input-sm @error('title') input-error @enderror"
                           value="{{ old('title', $step?->title) }}"
                           placeholder="VD: Thực hành và đạt chứng nhận Foundation">
                    @error('title')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Mô tả --}}
                <div class="form-control mb-4">
                    <label class="label py-1"><span class="label-text text-xs font-medium">Mô tả</span></label>
                    <textarea name="description" rows="3" class="textarea textarea-bordered text-sm"
                              placeholder="Mô tả những gì nhân viên cần làm ở bước này...">{{ old('description', $step?->description) }}</textarea>
                </div>

                {{-- Chứng nhận yêu cầu --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Chứng nhận yêu cầu</span>
                        <span class="label-text-alt text-xs text-base-content/30">Bắt buộc đạt cert này để thăng cấp</span>
                    </label>
                    <select name="required_cert_code" class="select select-bordered select-sm">
                        <option value="">-- Không yêu cầu --</option>
                        @foreach($certCodes as $code => $name)
                        <option value="{{ $code }}" {{ old('required_cert_code', $step?->required_cert_code) === $code ? 'selected' : '' }}>
                            {{ $code }} — {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/30">Nếu chọn, nhân viên phải có cert đang active với mã này mới đủ điều kiện thăng cấp tự động.</span></label>
                </div>

                {{-- Sandbox môi trường gợi ý --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Môi trường Sandbox gợi ý</span>
                        <span class="label-text-alt text-xs text-base-content/30">Gợi ý thực hành + điều kiện thăng cấp</span>
                    </label>
                    <select name="recommended_sandbox_env_code" class="select select-bordered select-sm">
                        <option value="">-- Không chỉ định --</option>
                        @foreach($envCodes as $code => $name)
                        <option value="{{ $code }}" {{ old('recommended_sandbox_env_code', $step?->recommended_sandbox_env_code) === $code ? 'selected' : '' }}>
                            {{ $code }} — {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    <label class="label py-0.5"><span class="label-text-alt text-xs text-base-content/30">Nếu chọn, nhân viên phải có ít nhất 1 phiên đạt điểm Pass trong môi trường này.</span></label>
                </div>

                {{-- KC Tag --}}
                <div class="form-control mb-4">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Tag nội dung học (KC)</span>
                        <span class="label-text-alt text-xs text-base-content/30">Hiển thị gợi ý tài liệu liên quan</span>
                    </label>
                    <input type="text" name="recommended_kc_tag" class="input input-bordered input-sm font-mono"
                           value="{{ old('recommended_kc_tag', $step?->recommended_kc_tag) }}"
                           placeholder="VD: ai-foundation,digital-skills">
                </div>

                {{-- Active --}}
                <div class="form-control mb-5">
                    <label class="label cursor-pointer justify-start gap-3 py-1">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', $step?->is_active ?? true) ? 'checked' : '' }}>
                        <span class="label-text text-sm">Kích hoạt (hiện cho nhân viên)</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $step ? 'Cập nhật bước' : 'Tạo bước' }}
                    </button>
                    <a href="{{ route('backend.career-pathway-admin.index') }}" class="btn btn-ghost btn-sm">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

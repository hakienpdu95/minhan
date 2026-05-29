@extends('layouts.backend')
@section('title', 'Thêm tổ chức mới')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.organizations.index') }}">Tổ chức</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-base-content">Thêm tổ chức mới</h1>
        <p class="text-sm text-base-content/50 mt-0.5">Điền đầy đủ thông tin để tạo tổ chức và kích hoạt hệ thống</p>
    </div>
    <a href="{{ route('backend.organizations.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

{{-- Validation error banner --}}
@if($errors->any())
<div class="alert alert-error text-sm py-2.5 px-4 mb-4 flex items-center gap-2">
    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    Vui lòng kiểm tra lại {{ $errors->count() }} lỗi bên dưới trước khi tiếp tục.
</div>
@endif

<form method="POST" action="{{ route('backend.organizations.store') }}"
      class="max-w-3xl space-y-4" novalidate data-org-form>
    @csrf

    {{-- ── Thông tin cơ bản ──────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="text-base font-semibold flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Thông tin cơ bản
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên tổ chức <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           data-req="Vui lòng nhập tên tổ chức"
                           class="input input-bordered input-sm w-full @error('name') input-error @enderror"
                           placeholder="VD: Công ty TNHH ABC" autofocus>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Slug (URL định danh)</span>
                        <span class="label-text-alt text-base-content/40">Tuỳ chọn</span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="input input-bordered input-sm w-full font-mono @error('slug') input-error @enderror"
                           placeholder="vd-cong-ty-tnhh-abc">
                    <p class="mt-1 text-xs text-base-content/40">Tự động tạo từ tên. Chỉ dùng chữ thường, số và dấu <code>-</code>.</p>
                    @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span>
                    </label>
                    <select name="status" class="select select-bordered select-sm w-full @error('status') select-error @enderror">
                        <option value="active"    {{ old('status', 'active') === 'active'    ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive"  {{ old('status') === 'inactive'            ? 'selected' : '' }}>Không hoạt động</option>
                        <option value="suspended" {{ old('status') === 'suspended'           ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Ngành nghề</span>
                    </label>
                    <input type="text" name="industry" value="{{ old('industry') }}"
                           class="input input-bordered input-sm w-full"
                           placeholder="VD: Công nghệ thông tin, Bán lẻ...">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mã số thuế <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="tax_code" value="{{ old('tax_code') }}"
                           data-req="Vui lòng nhập mã số thuế"
                           data-val-maxlength="20"
                           class="input input-bordered input-sm w-full font-mono @error('tax_code') input-error @enderror"
                           placeholder="VD: 0123456789"
                           maxlength="20">
                    <p class="mt-1 text-xs text-base-content/40">10 hoặc 13 chữ số. Tối đa 20 ký tự.</p>
                    @error('tax_code')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

            </div>

            <div class="form-control mt-2">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Mô tả</span>
                    <span class="label-text-alt text-base-content/40">Không bắt buộc</span>
                </label>
                <textarea name="description"
                          class="jodit-editor textarea textarea-bordered textarea-sm w-full"
                          data-jodit-preset="compact"
                          placeholder="Mô tả ngắn về lĩnh vực hoạt động, quy mô, đặc điểm nổi bật...">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── Thông tin liên hệ ──────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="text-base font-semibold flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Thông tin liên hệ
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Số điện thoại</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           data-val-maxlength="20"
                           class="input input-bordered input-sm w-full"
                           placeholder="VD: 028 1234 5678">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Email liên hệ</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           data-val-email="Email không đúng định dạng (VD: name@company.com)"
                           class="input input-bordered input-sm w-full @error('email') input-error @enderror"
                           placeholder="contact@company.com">
                    @error('email')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Website</span>
                    </label>
                    <input type="url" name="website" value="{{ old('website') }}"
                           data-val-url="URL không hợp lệ — phải bắt đầu bằng https://"
                           class="input input-bordered input-sm w-full @error('website') input-error @enderror"
                           placeholder="https://company.com">
                    <p class="mt-1 text-xs text-base-content/40">Phải bắt đầu bằng <code>https://</code></p>
                    @error('website')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ── Địa chỉ ────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="text-base font-semibold flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Địa chỉ
            </h2>

            <x-address-picker
                :province-value="old('province_code')"
                :ward-value="old('ward_code')"
                instance-id="org-c"
                :required="true"
            />

            <div class="form-control mt-4">
                <label class="label py-0 pb-1.5">
                    <span class="label-text font-medium">Địa chỉ cụ thể</span>
                    <span class="label-text-alt text-base-content/40">Số nhà, đường, phường...</span>
                </label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="input input-bordered input-sm w-full @error('address') input-error @enderror"
                       placeholder="VD: 123 Nguyễn Trãi, Phường Bến Thành, Quận 1">
                @error('address')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- ── Submit bar ──────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 pt-1">
        <button type="submit" class="btn btn-primary btn-sm gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo tổ chức
        </button>
        <a href="{{ route('backend.organizations.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
        <span class="text-xs text-base-content/40 ml-auto">
            Các trường có dấu <span class="text-error font-bold">*</span> là bắt buộc
        </span>
    </div>
</form>
@endsection

@push('styles')
    @vite(['Modules/Organization/resources/assets/sass/organization.scss'], 'build/backend')
@endpush

@push('scripts')
    @vite([
        'resources/js/modules/jodit.js',
        'Modules/Organization/resources/assets/js/organization.js',
    ], 'build/backend')
@endpush

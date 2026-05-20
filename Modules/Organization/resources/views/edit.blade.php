@extends('layouts.backend')
@section('title', 'Chỉnh sửa: ' . $organization->name)

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.organizations.index') }}">Tổ chức</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.organizations.show', $organization) }}">{{ $organization->name }}</a>
    <span class="sep">›</span>
    <span class="current">Chỉnh sửa</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Chỉnh sửa tổ chức</h1>
    <a href="{{ route('backend.organizations.show', $organization) }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.organizations.update', $organization) }}"
      class="max-w-3xl space-y-4" novalidate data-org-form>
    @csrf
    @method('PUT')

    {{-- Basic Info --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Thông tin cơ bản</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên tổ chức <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required
                           data-req="Vui lòng nhập tên tổ chức"
                           class="input input-bordered input-sm @error('name') input-error @enderror">
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Slug</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $organization->slug) }}"
                           class="input input-bordered input-sm @error('slug') input-error @enderror">
                    @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span>
                    </label>
                    <select name="status" class="select select-bordered select-sm">
                        <option value="active"    {{ old('status', $organization->status->value) === 'active'    ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive"  {{ old('status', $organization->status->value) === 'inactive'  ? 'selected' : '' }}>Không hoạt động</option>
                        <option value="suspended" {{ old('status', $organization->status->value) === 'suspended' ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ngành nghề</span></label>
                    <input type="text" name="industry" value="{{ old('industry', $organization->industry) }}"
                           class="input input-bordered input-sm">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Mã số thuế <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="tax_code" value="{{ old('tax_code', $organization->tax_code) }}"
                           data-req="Vui lòng nhập mã số thuế"
                           data-val-maxlength="20"
                           class="input input-bordered input-sm @error('tax_code') input-error @enderror">
                    @error('tax_code')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-control mt-2">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Mô tả</span></label>
                <textarea name="description"
                          class="jodit-editor textarea textarea-bordered textarea-sm w-full"
                          data-jodit-preset="compact">{{ old('description', $organization->description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Contact Info --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Thông tin liên hệ</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Điện thoại</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $organization->phone) }}"
                           data-val-maxlength="20"
                           class="input input-bordered input-sm">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Email liên hệ</span></label>
                    <input type="email" name="email" value="{{ old('email', $organization->email) }}"
                           data-val-email="Email không đúng định dạng (VD: name@company.com)"
                           class="input input-bordered input-sm @error('email') input-error @enderror">
                    @error('email')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Website</span></label>
                    <input type="url" name="website" value="{{ old('website', $organization->website) }}"
                           data-val-url="URL không hợp lệ — phải bắt đầu bằng https://"
                           class="input input-bordered input-sm @error('website') input-error @enderror">
                    @error('website')<p class="mt-1 text-xs text-error form-val-msg">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Địa chỉ</h2>
            <x-address-picker
                :province-value="old('province_code', $organization->province_code)"
                :ward-value="old('ward_code', $organization->ward_code)"
                :address-value="old('address', $organization->address)"
                instance-id="org-e"
                :required="true"
            />
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
        <a href="{{ route('backend.organizations.show', $organization) }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
@vite(['resources/js/modules/jodit.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initJoditAll('.jodit-editor');
    initFormValidation('[data-org-form]');
});
</script>
@endpush

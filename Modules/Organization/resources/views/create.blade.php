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
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Thêm tổ chức mới</h1>
    <a href="{{ route('backend.organizations.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.organizations.store') }}"
      class="max-w-3xl space-y-4" novalidate data-org-form>
    @csrf

    {{-- Basic Info --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Thông tin cơ bản</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Tên tổ chức <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           data-req="Vui lòng nhập tên tổ chức"
                           class="input input-bordered input-sm @error('name') input-error @enderror"
                           placeholder="VD: Công ty TNHH ABC">
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Slug (URL)</span>
                        <span class="label-text-alt text-base-content/40">Tự động tạo nếu để trống</span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="input input-bordered input-sm @error('slug') input-error @enderror"
                           placeholder="vd-cong-ty-tnhh-abc">
                    @error('slug')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5">
                        <span class="label-text font-medium">Trạng thái <span class="text-error">*</span></span>
                    </label>
                    <select name="status" class="select select-bordered select-sm @error('status') select-error @enderror">
                        <option value="active"    {{ old('status', 'active') === 'active'    ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive"  {{ old('status') === 'inactive'             ? 'selected' : '' }}>Không hoạt động</option>
                        <option value="suspended" {{ old('status') === 'suspended'            ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Ngành nghề</span></label>
                    <input type="text" name="industry" value="{{ old('industry') }}"
                           class="input input-bordered input-sm" placeholder="VD: Công nghệ thông tin">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Mã số thuế</span></label>
                    <input type="text" name="tax_code" value="{{ old('tax_code') }}"
                           class="input input-bordered input-sm" placeholder="VD: 0123456789">
                </div>
            </div>

            <div class="form-control mt-2">
                <label class="label py-0 pb-1.5"><span class="label-text font-medium">Mô tả</span></label>
                <textarea id="jodit-description" name="description"
                          class="textarea textarea-bordered textarea-sm w-full"
                          placeholder="Mô tả ngắn về tổ chức...">{{ old('description') }}</textarea>
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
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="input input-bordered input-sm" placeholder="028 1234 5678">
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Email liên hệ</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="input input-bordered input-sm @error('email') input-error @enderror"
                           placeholder="contact@company.com">
                    @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Website</span></label>
                    <input type="url" name="website" value="{{ old('website') }}"
                           class="input input-bordered input-sm @error('website') input-error @enderror"
                           placeholder="https://company.com">
                    @error('website')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Địa chỉ</h2>
            <x-address-picker
                :province-value="old('province_code')"
                :ward-value="old('ward_code')"
                :address-value="old('address')"
                instance-id="org-c"
            />
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Tạo tổ chức</button>
        <a href="{{ route('backend.organizations.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
@vite(['resources/js/modules/jodit.js'], 'build/backend')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.initJodit('#jodit-description', {
        height: 220,
        toolbarButtonSize: 'small',
        buttons: ['bold','italic','underline','strikethrough','|','ul','ol','|','paragraph','link','|','undo','redo','|','source'],
        removeButtons: ['about','classSpan','image'],
    });

    initOrgFormValidation('[data-org-form]');
});
</script>
@endpush

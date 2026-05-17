@extends('layouts.backend')
@section('title', 'Thêm tài khoản')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <a href="{{ route('backend.dashboard') }}">Trang chủ</a>
    <span class="sep">›</span>
    <a href="{{ route('backend.users.index') }}">Tài khoản</a>
    <span class="sep">›</span>
    <span class="current">Thêm mới</span>
</nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-base-content">Thêm tài khoản mới</h1>
    <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">← Quay lại</a>
</div>

<form method="POST" action="{{ route('backend.users.store') }}" class="max-w-3xl space-y-4">
    @csrf

    {{-- Account Info --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Thông tin tài khoản</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="input input-bordered input-sm @error('name') input-error @enderror"
                           placeholder="Nguyễn Văn A" required>
                    @error('name')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="input input-bordered input-sm @error('email') input-error @enderror"
                           placeholder="email@company.com" required>
                    @error('email')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Phòng ban</span></label>
                    <input type="text" name="department" value="{{ old('department') }}"
                           class="input input-bordered input-sm @error('department') input-error @enderror"
                           placeholder="VD: Kinh doanh, IT, HR...">
                    @error('department')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Mật khẩu <span class="text-error">*</span></span></label>
                    <input type="password" name="password"
                           class="input input-bordered input-sm @error('password') input-error @enderror"
                           placeholder="Tối thiểu 8 ký tự" required>
                    @error('password')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Xác nhận mật khẩu <span class="text-error">*</span></span></label>
                    <input type="password" name="password_confirmation"
                           class="input input-bordered input-sm"
                           placeholder="Nhập lại mật khẩu" required>
                </div>
            </div>
        </div>
    </div>

    {{-- Organization & Role --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-base mb-2">Tổ chức & Vai trò</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Tổ chức <span class="text-error">*</span></span></label>
                    <select name="organization_id" class="select select-bordered select-sm @error('organization_id') select-error @enderror" required>
                        <option value="">-- Chọn tổ chức --</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organization_id')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control">
                    <label class="label py-0 pb-1.5"><span class="label-text font-medium">Vai trò <span class="text-error">*</span></span></label>
                    <select name="role" class="select select-bordered select-sm @error('role') select-error @enderror" required>
                        <option value="">-- Chọn vai trò --</option>
                        <option value="owner"   {{ old('role') === 'owner'   ? 'selected' : '' }}>Owner</option>
                        <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="member"  {{ old('role') === 'member'  ? 'selected' : '' }}>Member</option>
                    </select>
                    @error('role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-control md:col-span-2">
                    <label class="label cursor-pointer justify-start gap-3 py-0">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="checkbox checkbox-sm checkbox-primary"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <span class="label-text font-medium">Kích hoạt tài khoản ngay</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-sm">Tạo tài khoản</button>
        <a href="{{ route('backend.users.index') }}" class="btn btn-ghost btn-sm">Hủy</a>
    </div>
</form>
@endsection

@extends('layouts.auth')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="w-full max-w-md">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body gap-4">

            <div class="text-center">
                <h1 class="text-2xl font-bold text-primary">Hồ sơ cá nhân</h1>
                <p class="text-base-content/60 text-sm mt-1">Cập nhật thông tin tài khoản</p>
            </div>

            {{-- Status message --}}
            @if (session('status') === 'profile-information-updated')
                <div class="alert alert-success text-sm">
                    <span>Thông tin đã được cập nhật.</span>
                </div>
            @endif

            {{-- Update profile form --}}
            <form method="POST" action="{{ url('user/profile-information') }}" class="flex flex-col gap-3">
                @csrf
                @method('PUT')

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Họ và tên <span class="text-error">*</span></span>
                    </div>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="input input-bordered w-full @error('name', 'updateProfileInformation') input-error @enderror"
                        required
                    />
                    @error('name', 'updateProfileInformation')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Email <span class="text-error">*</span></span>
                    </div>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="input input-bordered w-full @error('email', 'updateProfileInformation') input-error @enderror"
                        required
                    />
                    @error('email', 'updateProfileInformation')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </label>

                <button type="submit" class="btn btn-primary w-full mt-1">
                    Lưu thay đổi
                </button>
            </form>

            {{-- Change password form --}}
            <div class="divider text-xs text-base-content/40 my-0">Đổi mật khẩu</div>

            @if (session('status') === 'password-updated')
                <div class="alert alert-success text-sm">
                    <span>Mật khẩu đã được cập nhật.</span>
                </div>
            @endif

            <form method="POST" action="{{ url('user/password') }}" class="flex flex-col gap-3">
                @csrf
                @method('PUT')

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Mật khẩu hiện tại</span>
                    </div>
                    <input
                        type="password"
                        name="current_password"
                        class="input input-bordered w-full @error('current_password', 'updatePassword') input-error @enderror"
                    />
                    @error('current_password', 'updatePassword')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Mật khẩu mới</span>
                    </div>
                    <input
                        type="password"
                        name="password"
                        class="input input-bordered w-full @error('password', 'updatePassword') input-error @enderror"
                    />
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Xác nhận mật khẩu mới</span>
                    </div>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="input input-bordered w-full"
                    />
                </label>

                <button type="submit" class="btn btn-outline btn-primary w-full">
                    Đổi mật khẩu
                </button>
            </form>

            <div class="text-center text-sm">
                <a href="{{ route('backend.dashboard') }}" class="text-primary hover:underline">
                    ← Về Dashboard
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

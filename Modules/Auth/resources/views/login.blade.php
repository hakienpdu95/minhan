@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<div class="w-full max-w-sm">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body gap-4">

            <div class="text-center">
                <h1 class="text-2xl font-bold text-primary">{{ config('app.name') }}</h1>
                <p class="text-base-content/60 text-sm mt-1">Đăng nhập vào hệ thống</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3">
                @csrf

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Email</span>
                    </div>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        required autofocus autocomplete="username"
                    />
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-medium">Mật khẩu</span>
                    </div>
                    <input
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        required autocomplete="current-password"
                    />
                </label>

                <div class="flex items-center justify-between">
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
                        <span class="label-text text-sm">Ghi nhớ đăng nhập</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                            Quên mật khẩu?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-full mt-1">
                    Đăng nhập
                </button>
            </form>

            @if (Route::has('register'))
                <div class="divider text-sm text-base-content/50">Chưa có tài khoản?</div>
                <a href="{{ route('register') }}" class="btn btn-outline btn-primary w-full">
                    Tạo tổ chức mới
                </a>
            @endif

        </div>
    </div>
</div>
@endsection

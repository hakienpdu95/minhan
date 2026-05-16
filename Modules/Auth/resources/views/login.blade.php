@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<div class="w-full max-w-sm">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body gap-4">

            {{-- Logo --}}
            <div class="text-center">
                <h1 class="text-2xl font-bold text-primary">{{ config('app.name') }}</h1>
                <p class="text-base-content/60 text-sm mt-1">Đăng nhập vào hệ thống</p>
            </div>

            {{-- Session status --}}
            @if (session('status'))
                <div class="alert alert-success text-sm">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert alert-error text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3">
                @csrf

                {{-- Email --}}
                <label class="form-control w-full">
                    <div class="label pb-1">
                        <span class="label-text font-medium">Email</span>
                    </div>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </label>

                {{-- Password --}}
                <label class="form-control w-full">
                    <div class="label pb-1">
                        <span class="label-text font-medium">Mật khẩu</span>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="label-text-alt text-primary hover:underline">
                                Quên mật khẩu?
                            </a>
                        @endif
                    </div>
                    <input
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        required
                        autocomplete="current-password"
                    />
                </label>

                {{-- Remember me --}}
                <label class="label cursor-pointer justify-start gap-3 py-0">
                    <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
                    <span class="label-text text-sm">Ghi nhớ đăng nhập</span>
                </label>

                {{-- Cloudflare Turnstile — chỉ hiện khi enabled --}}
                @if (config('services.turnstile.enabled'))
                    <div class="flex flex-col gap-1">
                        <x-turnstile class="w-full" />
                        @error('cf-turnstile-response')
                            <p class="text-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-full mt-1">
                    Đăng nhập
                </button>
            </form>

            {{-- Register link --}}
            @if (Route::has('register'))
                <div class="divider text-xs text-base-content/40 my-0">Chưa có tài khoản?</div>
                <a href="{{ route('register') }}" class="btn btn-outline btn-primary btn-sm w-full">
                    Tạo tổ chức mới
                </a>
            @endif

        </div>
    </div>
</div>
@endsection

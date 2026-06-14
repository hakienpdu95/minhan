@extends('layouts.backend')
@section('title', 'Zalo ZNS — Cấu hình OTP')

@section('content')
@php
    $isLive = in_array($status, ['connected', 'expiring_soon', 'access_expired']);

    $badgeMap = [
        'connected'      => ['success', 'Đã kết nối'],
        'expiring_soon'  => ['warning', 'Sắp hết hạn'],
        'access_expired' => ['warning', 'Token hết hạn (tự refresh)'],
        'refresh_expired'=> ['error',   'Cần kết nối lại'],
        'not_connected'  => ['ghost',   'Chưa kết nối'],
        'not_configured' => ['ghost',   'Chưa cấu hình'],
    ];
    [$badgeColor, $badgeLabel] = $badgeMap[$status] ?? ['ghost', 'Không xác định'];

    $hasAppId    = (bool) config('otp_channel.drivers.zbs_zns.app_id');
    $hasTmplId   = (bool) config('otp_channel.drivers.zbs_zns.template_id');
    $isConnected = $status === 'connected';
@endphp

{{-- ── Page header ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-base-content leading-tight">Zalo ZNS — OTP Integration</h1>
            <p class="text-sm text-base-content/50 mt-0.5">Cấu hình gửi mã xác thực OTP qua Zalo ZNS Template Message API</p>
        </div>
    </div>
    <span class="badge badge-{{ $badgeColor }} badge-lg">{{ $badgeLabel }}</span>
</div>

{{-- ── Stat tiles ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">

    {{-- Driver --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">Driver</p>
                <div class="w-7 h-7 rounded-lg {{ $driver === 'zbs_zns' ? 'bg-success/10' : 'bg-warning/10' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $driver === 'zbs_zns' ? 'text-success' : 'text-warning' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <p class="font-mono font-bold text-base-content text-sm truncate">{{ $driver }}</p>
            <p class="text-xs mt-1 {{ $driver === 'zbs_zns' ? 'text-success' : 'text-warning' }}">
                {{ $driver === 'zbs_zns' ? 'Gửi thật qua Zalo' : 'Chế độ giả lập' }}
            </p>
        </div>
    </div>

    {{-- App ID --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">App ID</p>
                <div class="w-7 h-7 rounded-lg {{ $hasAppId ? 'bg-success/10' : 'bg-base-200' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $hasAppId ? 'text-success' : 'text-base-content/30' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>
            <p class="font-mono font-bold text-base-content text-sm truncate">
                {{ $hasAppId ? config('otp_channel.drivers.zbs_zns.app_id') : '—' }}
            </p>
            <p class="text-xs mt-1 {{ $hasAppId ? 'text-success' : 'text-base-content/40' }}">
                {{ $hasAppId ? 'Đã cấu hình' : 'Chưa đặt trong .env' }}
            </p>
        </div>
    </div>

    {{-- Access Token --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">Access Token</p>
                <div class="w-7 h-7 rounded-lg {{ $token && $token->access_token_expires_at->isFuture() ? 'bg-success/10' : 'bg-base-200' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $token && $token->access_token_expires_at->isFuture() ? 'text-success' : 'text-base-content/30' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            @if($token)
            <p class="font-semibold text-sm {{ $token->access_token_expires_at->isFuture() ? 'text-success' : 'text-error' }}">
                {{ $token->access_token_expires_at->diffForHumans() }}
            </p>
            <p class="text-xs text-base-content/40 mt-1 tabular-nums">{{ $token->access_token_expires_at->format('d/m H:i') }}</p>
            @else
            <p class="font-semibold text-sm text-base-content/30">—</p>
            <p class="text-xs text-base-content/30 mt-1">Chưa có token</p>
            @endif
        </div>
    </div>

    {{-- Refresh Token --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-base-content/50 uppercase tracking-wide">Refresh Token</p>
                <div class="w-7 h-7 rounded-lg {{ $token && $token->refresh_token_expires_at->isFuture() ? 'bg-info/10' : 'bg-base-200' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $token && $token->refresh_token_expires_at->isFuture() ? 'text-info' : 'text-base-content/30' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            @if($token)
            <p class="font-semibold text-sm {{ $token->refresh_token_expires_at->isFuture() ? 'text-info' : 'text-error' }}">
                {{ $token->refresh_token_expires_at->diffForHumans() }}
            </p>
            <p class="text-xs text-base-content/40 mt-1 tabular-nums">{{ $token->refresh_token_expires_at->format('d/m/Y') }}</p>
            @else
            <p class="font-semibold text-sm text-base-content/30">—</p>
            <p class="text-xs text-base-content/30 mt-1">Chưa có token</p>
            @endif
        </div>
    </div>

</div>

{{-- ── Main row: Connection + Test ──────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Connection card (2/3 width) --}}
    <div class="lg:col-span-2 card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-0">
            <div class="px-5 py-4 border-b border-base-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <h2 class="font-semibold text-sm">Trạng thái kết nối</h2>
                </div>
                <span class="badge badge-{{ $badgeColor }} badge-sm">{{ $badgeLabel }}</span>
            </div>

            <div class="p-5 space-y-4">

                {{-- Driver warning --}}
                @if($driver !== 'zbs_zns')
                <div class="flex gap-3 p-4 rounded-xl bg-warning/8 border border-warning/20">
                    <svg class="w-5 h-5 text-warning shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm min-w-0">
                        <p class="font-semibold text-warning">Driver đang là <code class="bg-warning/15 px-1.5 rounded text-xs">{{ $driver }}</code> — OTP không gửi qua Zalo</p>
                        <p class="text-base-content/60 mt-1">Đặt <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs font-mono">OTP_CHANNEL_DRIVER=zbs_zns</code> trong <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs font-mono">.env</code> để bật ZNS thật.</p>
                    </div>
                </div>
                @endif

                {{-- Status message --}}
                @if(in_array($status, ['not_configured', 'not_connected']))
                <div class="flex gap-3 p-4 rounded-xl bg-base-200/60 border border-base-200">
                    <svg class="w-5 h-5 text-base-content/40 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-base-content/60">Chưa có OAuth token. Nhấn <span class="font-semibold text-base-content">Kết nối Zalo OA</span> để bắt đầu xác thực.</p>
                </div>

                @elseif($status === 'refresh_expired')
                <div class="flex gap-3 p-4 rounded-xl bg-error/8 border border-error/20">
                    <svg class="w-5 h-5 text-error shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold text-error">Refresh token đã hết hạn</p>
                        <p class="text-base-content/60 mt-0.5">Cần kết nối lại để lấy token mới. Refresh token hết hạn sau 90 ngày nếu không được gia hạn.</p>
                    </div>
                </div>

                @elseif($status === 'expiring_soon')
                <div class="flex gap-3 p-4 rounded-xl bg-warning/8 border border-warning/20">
                    <svg class="w-5 h-5 text-warning shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold text-warning">Access token sắp hết hạn</p>
                        <p class="text-base-content/60 mt-0.5">Hệ thống sẽ tự refresh. Nếu OTP gửi thất bại, nhấn <span class="font-semibold">Kết nối lại</span> để buộc lấy token mới.</p>
                    </div>
                </div>

                @elseif($status === 'access_expired')
                <div class="flex gap-3 p-4 rounded-xl bg-info/8 border border-info/20">
                    <svg class="w-5 h-5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold text-info">Access token đã hết hạn — tự động refresh</p>
                        <p class="text-base-content/60 mt-0.5">Request OTP tiếp theo sẽ tự refresh token trước khi gửi.</p>
                    </div>
                </div>

                @elseif($status === 'connected')
                <div class="flex gap-3 p-4 rounded-xl bg-success/8 border border-success/20">
                    <svg class="w-5 h-5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold text-success">Kết nối hoạt động bình thường</p>
                        <p class="text-base-content/60 mt-0.5">OTP sẽ được gửi qua Zalo ZNS. Access token tự động refresh trước khi hết hạn.</p>
                    </div>
                </div>
                @endif

                {{-- Action buttons --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if(in_array($status, ['not_configured', 'not_connected', 'refresh_expired']))
                    <a href="{{ route('backend.zbs.connect') }}" class="btn btn-primary gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        {{ $status === 'refresh_expired' ? 'Kết nối lại Zalo OA' : 'Kết nối Zalo OA' }}
                    </a>
                    @else
                    <a href="{{ route('backend.zbs.connect') }}" class="btn btn-outline btn-sm gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Kết nối lại / đổi OA
                    </a>
                    <form method="POST" action="{{ route('backend.zbs.disconnect') }}"
                          onsubmit="return confirm('Ngắt kết nối? OTP sẽ không gửi được qua Zalo cho đến khi kết nối lại.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm text-error gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Ngắt kết nối
                        </button>
                    </form>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Test OTP card (1/3 width) --}}
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-0">
            <div class="px-5 py-4 border-b border-base-200 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <h2 class="font-semibold text-sm">Kiểm tra kết nối</h2>
            </div>

            @if($isLive)
            <div class="p-5 space-y-3">
                <p class="text-xs text-base-content/50">Gửi mã <code class="bg-base-200 px-1.5 py-0.5 rounded font-mono">123456</code> tới số điện thoại bất kỳ để xác nhận tích hợp hoạt động đúng.</p>
                <form method="POST" action="{{ route('backend.zbs.test') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label label-text text-xs pb-1.5">Số điện thoại</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               placeholder="0912 345 678"
                               class="input input-bordered w-full @error('phone') input-error @enderror">
                        @error('phone')
                        <p class="text-xs text-error mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-outline w-full gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Gửi OTP thử
                    </button>
                </form>

                <div class="pt-1 border-t border-base-200 space-y-1.5">
                    <p class="text-xs font-medium text-base-content/50">Lưu ý</p>
                    <p class="text-xs text-base-content/40">Số điện thoại phải là tài khoản Zalo đang hoạt động.</p>
                    <p class="text-xs text-base-content/40">Template ZNS phải được Zalo phê duyệt trước.</p>
                </div>
            </div>
            @else
            <div class="p-5 flex flex-col items-center justify-center text-center gap-3 py-10">
                <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-base-content/25" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-base-content/50">Chưa sẵn sàng</p>
                    <p class="text-xs text-base-content/35 mt-1">Hoàn thành kết nối Zalo OA trước khi test</p>
                </div>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Setup guide ────────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm">
    <div class="card-body p-0">
        <div class="px-5 py-4 border-b border-base-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h2 class="font-semibold text-sm">Hướng dẫn cấu hình lần đầu</h2>
            </div>
            <span class="text-xs text-base-content/40">4 bước</span>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Step 1 --}}
                @php $s1done = $hasAppId; @endphp
                <div class="flex flex-col gap-3 p-4 rounded-xl {{ $s1done ? 'bg-success/8 border border-success/20' : 'bg-base-200/40 border border-base-200' }} transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full {{ $s1done ? 'bg-success text-white' : 'bg-base-300 text-base-content/40' }} text-xs font-bold flex items-center justify-center">
                            @if($s1done)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                            1
                            @endif
                        </span>
                        <span class="text-xs {{ $s1done ? 'text-success' : 'text-base-content/30' }} font-medium">{{ $s1done ? 'Xong' : 'Chưa' }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-base-content">Tạo Zalo Official Account</p>
                        <p class="text-xs text-base-content/50 mt-1.5 leading-relaxed">Đăng ký tại <a href="https://business.zalo.me" target="_blank" class="link link-primary font-medium">business.zalo.me</a>, kích hoạt ZNS và tạo App trong Zalo Developer Console.</p>
                    </div>
                </div>

                {{-- Step 2 --}}
                @php $s2done = $hasAppId && $hasTmplId; @endphp
                <div class="flex flex-col gap-3 p-4 rounded-xl {{ $s2done ? 'bg-success/8 border border-success/20' : 'bg-base-200/40 border border-base-200' }} transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full {{ $s2done ? 'bg-success text-white' : 'bg-base-300 text-base-content/40' }} text-xs font-bold flex items-center justify-center">
                            @if($s2done)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                            2
                            @endif
                        </span>
                        <span class="text-xs {{ $s2done ? 'text-success' : 'text-base-content/30' }} font-medium">{{ $s2done ? 'Xong' : 'Chưa' }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-base-content">Cấu hình <code class="text-xs bg-base-200 px-1.5 py-0.5 rounded font-mono">.env</code></p>
                        <div class="mt-2 bg-base-200/80 border border-base-300 rounded-lg p-2.5 font-mono text-xs leading-relaxed text-base-content/65 break-all select-all">
                            OTP_CHANNEL_DRIVER=zbs_zns<br>
                            ZBS_APP_ID=<span class="text-warning">your_app_id</span><br>
                            ZBS_APP_SECRET=<span class="text-warning">your_secret</span><br>
                            ZBS_OTP_TEMPLATE_ID=<span class="text-warning">template_id</span>
                        </div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="flex flex-col gap-3 p-4 rounded-xl bg-base-200/40 border border-base-200">
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full bg-base-300 text-base-content/40 text-xs font-bold flex items-center justify-center">3</span>
                        <span class="text-xs text-base-content/30 font-medium">Thủ công</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-base-content">Đăng ký Callback URL</p>
                        <p class="text-xs text-base-content/50 mt-1.5 mb-2 leading-relaxed">Thêm URL sau vào <strong>Zalo Developer Console → Redirect URI</strong>:</p>
                        <div class="bg-base-200/80 border border-base-300 rounded-lg p-2.5 font-mono text-xs break-all text-base-content/65 select-all cursor-text">
                            {{ route('backend.zbs.callback') }}
                        </div>
                    </div>
                </div>

                {{-- Step 4 --}}
                @php $s4done = $isConnected; @endphp
                <div class="flex flex-col gap-3 p-4 rounded-xl {{ $s4done ? 'bg-success/8 border border-success/20' : 'bg-base-200/40 border border-base-200' }} transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full {{ $s4done ? 'bg-success text-white' : 'bg-base-300 text-base-content/40' }} text-xs font-bold flex items-center justify-center">
                            @if($s4done)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                            4
                            @endif
                        </span>
                        <span class="text-xs {{ $s4done ? 'text-success' : 'text-base-content/30' }} font-medium">{{ $s4done ? 'Xong' : 'Chưa' }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-base-content">Kết nối Zalo OA</p>
                        <p class="text-xs text-base-content/50 mt-1.5 leading-relaxed">Nhấn <strong class="text-base-content">Kết nối Zalo OA</strong> ở trên. Zalo sẽ redirect về đây và lưu token tự động.</p>
                        <div class="mt-3 space-y-1">
                            <p class="text-xs text-base-content/40 flex items-center gap-1.5">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Access token tự refresh mỗi 1 giờ
                            </p>
                            <p class="text-xs text-base-content/40 flex items-center gap-1.5">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Cần kết nối lại sau 90 ngày
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

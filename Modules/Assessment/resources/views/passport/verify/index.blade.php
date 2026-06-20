@extends('layouts.backend')
@section('title', 'Xác minh danh tính — Competency Passport')

@section('content')

{{-- ── Breadcrumb ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 text-sm text-base-content/50 mb-4">
    <a href="{{ route('passport.index') }}" class="hover:text-primary">Competency Passport</a>
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-base-content">Xác minh danh tính</span>
</div>

{{-- ── Alerts ──────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success mb-4">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('info'))
<div class="alert alert-info mb-4">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('info') }}</span>
</div>
@endif
@if(session('error'))
<div class="alert alert-error mb-4">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('error') }}</span>
</div>
@endif
@if(session('status') === 'verification-link-sent')
<div class="alert alert-info mb-4">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <div>
        <p class="font-semibold">Email xác minh đã được gửi!</p>
        <p class="text-sm">Kiểm tra hộp thư của <strong>{{ $user->email }}</strong> và nhấp vào link xác minh. Link có hiệu lực trong 60 phút.</p>
    </div>
</div>
@endif

{{-- ── Current trust level + progress ─────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm mb-6">
    <div class="card-body">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">Xác minh danh tính</h1>
                <p class="text-sm text-base-content/50 mt-0.5">Mỗi cấp độ xác minh mở thêm tính năng mới trên nền tảng</p>
            </div>
            <div>
                @if($user->trust_level >= 2)
                    <div class="badge badge-info badge-lg gap-1.5 px-4">📱 Điện thoại xác minh — Lv2</div>
                @elseif($user->trust_level >= 1)
                    <div class="badge badge-outline badge-lg gap-1.5 px-4">✉ Email xác minh — Lv1</div>
                @else
                    <div class="badge badge-ghost badge-lg px-4">Chưa xác minh — Lv0</div>
                @endif
            </div>
        </div>

        {{-- Progress steps --}}
        <div class="mt-5">
            <ul class="steps steps-horizontal w-full text-xs">
                <li class="step step-primary">Đăng ký</li>
                <li class="step {{ $user->trust_level >= 1 ? 'step-primary' : '' }}">✉ Email</li>
                <li class="step {{ $user->trust_level >= 2 ? 'step-primary' : '' }}">📱 Điện thoại</li>
            </ul>
        </div>
    </div>
</div>

{{-- ── Tính năng được mở khoá theo cấp độ ─────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">

    {{-- Lv1 --}}
    <div class="rounded-xl border p-4 {{ $user->trust_level >= 1 ? 'border-success/40 bg-success/5' : 'border-base-200 bg-base-100 opacity-60' }}">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">✉</span>
            <span class="font-semibold text-sm">Lv1 — Email</span>
            @if($user->trust_level >= 1)
            <svg class="w-4 h-4 text-success ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>
        <ul class="space-y-1.5 text-xs text-base-content/70">
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Truy cập toàn bộ hệ thống</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Xem Career Journal &amp; Passport</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Làm khảo sát TDWCF &amp; Sandbox</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-success shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Nhận chứng nhận AI nội bộ</li>
            <li class="flex gap-1.5 text-base-content/40"><svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-6v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>Assessment Marketplace <em>(cần Lv2)</em></li>
        </ul>
    </div>

    {{-- Lv2 --}}
    <div class="rounded-xl border p-4 {{ $user->trust_level >= 2 ? 'border-info/40 bg-info/5' : 'border-base-200 bg-base-100' }} {{ $user->trust_level < 1 ? 'opacity-60' : '' }}">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">📱</span>
            <span class="font-semibold text-sm">Lv2 — Điện thoại</span>
            @if($user->trust_level >= 2)
            <svg class="w-4 h-4 text-success ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>
        <ul class="space-y-1.5 text-xs text-base-content/70">
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Tham gia Assessment Marketplace</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Tham gia các campaign tuyển dụng mở</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Chia sẻ Passport qua link cá nhân</li>
            <li class="flex gap-1.5"><svg class="w-3.5 h-3.5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Kết quả campaign lưu vào Career Journal</li>
        </ul>
    </div>

</div>

{{-- ── Verification cards ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

{{-- ── Lv1: Email ─────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border {{ $user->hasVerifiedEmail() ? 'border-success/30' : 'border-base-200' }} shadow-sm">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $user->hasVerifiedEmail() ? 'bg-success/10 text-success' : 'bg-base-200 text-base-content/40' }} flex items-center justify-center text-xl shrink-0">✉</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold">Email xác minh</h3>
                    @if($user->hasVerifiedEmail())
                        <span class="badge badge-success badge-sm">Hoàn thành</span>
                    @else
                        <span class="badge badge-ghost badge-sm">Lv1</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/60 mb-3">Xác nhận địa chỉ email để truy cập toàn bộ hệ thống, Career Journal và Sandbox.</p>

                @if($user->hasVerifiedEmail())
                    {{-- Already verified --}}
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-success font-medium">{{ $user->email }}</p>
                    </div>
                @else
                    {{-- Not verified — delegate to native Fortify --}}
                    <div class="text-sm text-base-content/60 mb-3">
                        <span class="font-mono">{{ $user->email }}</span> — chưa xác minh
                    </div>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Gửi email xác minh
                        </button>
                        <p class="text-xs text-base-content/40 mt-1.5">Tối đa 6 lần/phút. Link có hiệu lực 60 phút.</p>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- ── Lv2: Phone ──────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border {{ $user->trust_level >= 2 ? 'border-info/30' : ($pendingPhone ? 'border-warning/30' : 'border-base-200') }} shadow-sm"
     x-data="{
        step: '{{ $pendingPhone ? 'confirm' : 'request' }}',
        phone: '{{ $pendingPhone?->phone_candidate ?? '' }}'
     }">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $user->trust_level >= 2 ? 'bg-info/10 text-info' : ($pendingPhone ? 'bg-warning/10 text-warning' : 'bg-base-200 text-base-content/40') }} flex items-center justify-center text-xl shrink-0">📱</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold">Số điện thoại</h3>
                    @if($user->trust_level >= 2)
                        <span class="badge badge-info badge-sm">Hoàn thành</span>
                    @elseif($pendingPhone)
                        <span class="badge badge-warning badge-sm">Đang chờ nhập OTP</span>
                    @else
                        <span class="badge badge-ghost badge-sm">Lv2</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/60 mb-3">Cần để tham gia <strong>Assessment Marketplace</strong> và chia sẻ Passport công khai.</p>

                @if($user->trust_level >= 2)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-info shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-info font-medium">{{ $user->phone_number }}</p>
                    </div>
                @elseif($user->trust_level < 1)
                    <p class="text-sm text-warning">Cần xác minh email trước (Lv1).</p>
                @else

                {{-- Step 1: Request OTP --}}
                <div x-show="step === 'request'">
                    @error('phone_number')<p class="text-sm text-error mb-2">{{ $message }}</p>@enderror
                    <form method="POST" action="{{ route('passport.verify.phone.request') }}">
                        @csrf
                        <div class="flex gap-2">
                            <input type="tel" name="phone_number" x-model="phone"
                                   value="{{ old('phone_number') }}"
                                   placeholder="0912 345 678"
                                   class="input input-bordered input-sm flex-1 @error('phone_number') input-error @enderror"
                                   required>
                            <button type="submit" class="btn btn-primary btn-sm">Lấy mã OTP</button>
                        </div>
                        <p class="text-xs text-base-content/40 mt-1.5">Tối đa 3 lần/giờ. Mã có hiệu lực 5 phút.</p>
                    </form>
                </div>

                {{-- Step 2: Enter OTP --}}
                <div x-show="step === 'confirm'">
                    {{-- Delivery notice --}}
                    @if(session('phone_code_sent'))
                        @if(session('dev_code'))
                        {{-- Local dev only: code surfaced in session --}}
                        <div class="alert alert-warning py-2 mb-3">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="text-sm">
                                <span class="font-semibold">[DEV] Mã OTP:</span>
                                <code class="font-mono text-base font-bold tracking-widest ml-1">{{ session('dev_code') }}</code>
                            </span>
                        </div>
                        @else
                        <div class="flex items-center gap-2 text-sm text-base-content/60 mb-3">
                            <svg class="w-4 h-4 shrink-0 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mã OTP đã được gửi qua <strong class="text-base-content ml-1">Zalo</strong>.
                        </div>
                        @endif
                    @elseif($pendingPhone)
                        <div class="flex items-center gap-2 text-sm text-base-content/60 mb-3">
                            <svg class="w-4 h-4 shrink-0 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Nhập mã đã gửi đến <strong class="text-base-content ml-1">{{ $pendingPhone->phone_candidate }}</strong> qua Zalo
                            <span class="text-base-content/40">(còn {{ $pendingPhone->code_expires_at->diffForHumans() }})</span>
                        </div>
                    @endif

                    @error('code')<p class="text-sm text-error mb-2">{{ $message }}</p>@enderror
                    <form method="POST" action="{{ route('passport.verify.phone.confirm') }}">
                        @csrf
                        <div class="flex gap-2 items-center">
                            <input type="text" name="code" inputmode="numeric" maxlength="6"
                                   class="input input-bordered input-sm w-36 font-mono text-lg tracking-widest @error('code') input-error @enderror"
                                   placeholder="000000" autofocus required>
                            <button type="submit" class="btn btn-success btn-sm">Xác nhận</button>
                        </div>
                    </form>
                    <button @click="step='request'" class="btn btn-ghost btn-xs mt-2 text-base-content/40">← Đổi số điện thoại</button>
                </div>

                @endif
            </div>
        </div>
    </div>
</div>

</div>

{{-- ── Verification history ─────────────────────────────────────────────────── --}}
@if($verifications->count())
<div class="mt-6">
    <h2 class="text-sm font-semibold text-base-content/60 mb-3 uppercase tracking-wide">Lịch sử xác minh</h2>
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày xác minh</th>
                        <th>Hết hạn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($verifications as $v)
                    <tr>
                        <td><span class="font-medium">{{ $v->method->label() }}</span></td>
                        <td>
                            @if($v->status->value === 'verified')
                                <span class="badge badge-success badge-sm">Đã xác minh</span>
                            @elseif($v->status->value === 'expired')
                                <span class="badge badge-warning badge-sm">Hết hạn</span>
                            @else
                                <span class="badge badge-ghost badge-sm">{{ $v->status->label() }}</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $v->verified_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-sm text-base-content/50">{{ $v->expires_at?->format('d/m/Y') ?? 'Không giới hạn' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

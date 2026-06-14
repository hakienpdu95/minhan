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
<div class="alert mb-4" style="background:oklch(var(--in)/0.12);border-color:oklch(var(--in)/0.3)">
    <svg class="w-5 h-5 shrink-0 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('info') }}</span>
</div>
@endif

{{-- ── Trust Level overview ─────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm mb-6">
    <div class="card-body">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">Xác minh danh tính</h1>
                <p class="text-sm text-base-content/50 mt-0.5">Tăng độ tin cậy hồ sơ Passport khi chia sẻ với nhà tuyển dụng</p>
            </div>
            <div class="flex items-center gap-3">
                @if($user->trust_level >= 3)
                    <div class="badge badge-success badge-lg gap-1.5 px-4">🪪 Danh tính xác minh — Lv{{ $user->trust_level }}</div>
                @elseif($user->trust_level >= 2)
                    <div class="badge badge-info badge-lg gap-1.5 px-4">📱 Điện thoại — Lv{{ $user->trust_level }}</div>
                @elseif($user->trust_level >= 1)
                    <div class="badge badge-outline badge-lg gap-1.5 px-4">✉ Email — Lv{{ $user->trust_level }}</div>
                @else
                    <div class="badge badge-ghost badge-lg px-4">Chưa xác minh — Lv0</div>
                @endif
            </div>
        </div>

        {{-- Trust level steps --}}
        <div class="mt-5">
            <ul class="steps steps-horizontal w-full">
                <li class="step {{ $user->trust_level >= 0 ? 'step-primary' : '' }}">Đăng ký</li>
                <li class="step {{ $user->trust_level >= 1 ? 'step-primary' : '' }}">✉ Email</li>
                <li class="step {{ $user->trust_level >= 2 ? 'step-primary' : '' }}">📱 Điện thoại</li>
                <li class="step {{ $user->trust_level >= 3 ? 'step-primary' : '' }}">🪪 CCCD</li>
                <li class="step {{ $user->trust_level >= 4 ? 'step-primary' : '' }}">⭐ VNeID</li>
            </ul>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

{{-- ── Lv1: Email ───────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $user->trust_level >= 1 ? 'bg-success/10 text-success' : 'bg-base-200 text-base-content/40' }} flex items-center justify-center text-xl shrink-0">✉</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold">Email xác minh</h3>
                    @if($user->trust_level >= 1)
                        <span class="badge badge-success badge-sm">Hoàn thành</span>
                    @else
                        <span class="badge badge-ghost badge-sm">Lv 1</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/60 mb-3">Xác nhận địa chỉ email cá nhân để kích hoạt tính năng cơ bản.</p>
                @if($user->trust_level >= 1)
                    <p class="text-sm text-success font-medium">{{ $user->email }} — đã xác minh</p>
                @elseif($user->email_verified_at)
                    <p class="text-sm text-success">Email đã xác minh</p>
                @else
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline">Gửi lại email xác minh</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Lv2: Phone ──────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm" x-data="{
    step: {{ $pendingPhone ? '\'confirm\'' : '\'request\'' }},
    devCode: '{{ $pendingPhone?->verification_code ?? '' }}',
    phone: '{{ $pendingPhone?->phone_candidate ?? '' }}'
}">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $user->trust_level >= 2 ? 'bg-info/10 text-info' : 'bg-base-200 text-base-content/40' }} flex items-center justify-center text-xl shrink-0">📱</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold">Số điện thoại</h3>
                    @if($user->trust_level >= 2)
                        <span class="badge badge-info badge-sm">Hoàn thành</span>
                    @else
                        <span class="badge badge-ghost badge-sm">Lv 2</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/60 mb-3">Xác minh số điện thoại để tham gia Marketplace đánh giá mở (Phase 4).</p>

                @if($user->trust_level >= 2)
                    <p class="text-sm text-info font-medium">{{ $user->phone_number }} — đã xác minh</p>
                @elseif($user->trust_level < 1)
                    <p class="text-sm text-warning">Cần xác minh email trước.</p>
                @else

                {{-- Step 1: Request code --}}
                <div x-show="step === 'request'">
                    @error('phone_number')<p class="text-sm text-error mb-2">{{ $message }}</p>@enderror
                    <form method="POST" action="{{ route('passport.verify.phone.request') }}" @submit="step='confirm'">
                        @csrf
                        <div class="flex gap-2">
                            <input type="tel" name="phone_number" x-model="phone"
                                   placeholder="0912 345 678"
                                   class="input input-bordered input-sm flex-1 @error('phone_number') input-error @enderror"
                                   required>
                            <button type="submit" class="btn btn-primary btn-sm">Lấy mã</button>
                        </div>
                        <p class="text-xs text-base-content/40 mt-1.5">Tối đa 3 lần/giờ. Mã có hiệu lực 5 phút.</p>
                    </form>
                </div>

                {{-- Step 2: Enter code (dev mode: show the code) --}}
                <div x-show="step === 'confirm'">
                    @if(session('phone_code_sent'))
                    <div class="alert alert-info alert-sm mb-3 py-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm">
                            <span class="font-semibold">Mã xác minh (dev):</span>
                            <code class="font-mono text-base font-bold tracking-widest ml-1">{{ session('dev_code') }}</code>
                        </span>
                    </div>
                    @endif

                    @error('code')<p class="text-sm text-error mb-2">{{ $message }}</p>@enderror

                    <form method="POST" action="{{ route('passport.verify.phone.confirm') }}">
                        @csrf
                        <p class="text-sm mb-2">Nhập mã 6 chữ số đã cấp:</p>
                        <div class="flex gap-2">
                            <input type="text" name="code" inputmode="numeric" maxlength="6"
                                   class="input input-bordered input-sm w-32 font-mono text-lg tracking-widest @error('code') input-error @enderror"
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

{{-- ── Lv3: CCCD ───────────────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm lg:col-span-2">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full {{ $user->trust_level >= 3 ? 'bg-success/10 text-success' : ($pendingCccd ? 'bg-warning/10 text-warning' : 'bg-base-200 text-base-content/40') }} flex items-center justify-center text-xl shrink-0">🪪</div>
            <div class="flex-1 min-w-0">

                {{-- Header --}}
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold">Căn cước công dân (CCCD)</h3>
                    @if($user->trust_level >= 3)
                        <span class="badge badge-success badge-sm">Xác minh OCR</span>
                    @elseif($pendingCccd)
                        <span class="badge badge-warning badge-sm">Đã đăng ký — chưa xác minh ảnh</span>
                    @else
                        <span class="badge badge-ghost badge-sm">Lv 3</span>
                    @endif
                </div>
                <p class="text-sm text-base-content/60 mb-4">
                    Số CCCD được mã hóa SHA-256, không thể truy ngược.
                    Ảnh (nếu có) chỉ xử lý trong bộ nhớ, <strong>không lưu vào máy chủ</strong>.
                </p>

                {{-- ═══ Trạng thái 1: Đã xác minh OCR ═══ --}}
                @if($user->trust_level >= 3)
                <div class="alert alert-success">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>CCCD đã được xác minh qua ảnh — hồ sơ Passport hiển thị badge 🪪 Danh tính xác minh.</span>
                </div>

                {{-- ═══ Trạng thái 2: Chưa xác minh điện thoại ═══ --}}
                @elseif($user->trust_level < 2)
                <div class="alert alert-warning">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Cần xác minh điện thoại (Lv 2) trước khi đăng ký CCCD.</span>
                </div>

                {{-- ═══ Trạng thái 3: Đã nhập text, chưa upload ảnh ═══ --}}
                @elseif($pendingCccd)
                <div class="alert mb-4" style="background:oklch(var(--w)/0.12);border-color:oklch(var(--w)/0.3)">
                    <svg class="w-4 h-4 shrink-0 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm">
                        <p class="font-semibold text-warning">Thông tin CCCD đã được lưu tạm.</p>
                        <p class="text-base-content/60 mt-0.5">Upload ảnh 2 mặt CCCD để hoàn tất xác minh và nhận Trust Level 3.</p>
                    </div>
                </div>

                @include('assessment::passport.verify._cccd_image_form', ['mode' => 'upgrade'])

                {{-- ═══ Trạng thái 4: Chưa đăng ký — form đầy đủ ═══ --}}
                @else
                @include('assessment::passport.verify._cccd_full_form')
                @endif

            </div>
        </div>
    </div>
</div>

{{-- ── Lv4: VNeID (Phase 5) ────────────────────────────────────────────────── --}}
<div class="card bg-base-100 border border-base-200 shadow-sm opacity-50">
    <div class="card-body">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-base-200 text-base-content/30 flex items-center justify-center text-xl shrink-0">⭐</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold text-base-content/50">VNeID (Biometrics)</h3>
                    <span class="badge badge-ghost badge-sm">Lv 4 — Phase 5</span>
                </div>
                <p class="text-sm text-base-content/40">Tích hợp xác minh sinh trắc học qua ứng dụng VNeID của Chính phủ. Sẽ có trong Phase 5.</p>
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
                        <td>
                            <span class="font-medium">{{ $v->method->label() }}</span>
                        </td>
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

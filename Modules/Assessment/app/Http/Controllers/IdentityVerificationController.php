<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Modules\Assessment\Enums\VerificationMethod;
use Modules\Assessment\Enums\VerificationStatus;
use App\Services\OtpChannel\OtpChannelManager;
use Modules\Assessment\Models\IdentityVerification;

class IdentityVerificationController extends Controller
{
    // ── GET /passport/verify ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $request->user();

        $verifications = IdentityVerification::where('user_id', $user->id)
            ->where('status', VerificationStatus::Verified->value)
            ->orderByDesc('verified_at')
            ->get();

        $pendingPhone = IdentityVerification::where('user_id', $user->id)
            ->where('method', VerificationMethod::PhoneOtp->value)
            ->where('status', VerificationStatus::Pending->value)
            ->where('code_expires_at', '>', now())
            ->latest()
            ->first();

        return view('assessment::passport.verify.index', compact(
            'user', 'verifications', 'pendingPhone'
        ));
    }

    // ── POST /passport/verify/phone/request ──────────────────────────────────

    public function phoneRequest(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^(0[3-9])[0-9]{8}$/'],
        ], [
            'phone_number.regex' => 'Số điện thoại không hợp lệ (VD: 0912345678)',
        ]);

        $phone = $request->input('phone_number');

        $rateLimitKey = 'phone-verify:' . preg_replace('/\D/', '', $phone);
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['phone_number' => "Quá nhiều yêu cầu. Vui lòng thử lại sau {$seconds} giây."]);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        IdentityVerification::where('user_id', $user->id)
            ->where('method', VerificationMethod::PhoneOtp->value)
            ->where('status', VerificationStatus::Pending->value)
            ->update(['status' => VerificationStatus::Expired->value]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $verification = IdentityVerification::create([
            'user_id'           => $user->id,
            'method'            => VerificationMethod::PhoneOtp->value,
            'status'            => VerificationStatus::Pending->value,
            'verification_code' => $code,
            'code_expires_at'   => now()->addMinutes(5),
            'phone_candidate'   => $phone,
        ]);

        // Gửi đồng bộ — không cần queue worker, người dùng nhận mã ngay lập tức.
        // ZbsTokenService tự refresh access token nếu hết hạn trước khi gọi API.
        $result = app(OtpChannelManager::class)->driver()->send($phone, $code);

        if (!$result->success) {
            // Code chưa đến tay người dùng → expire record, trả lỗi thân thiện
            $verification->update(['status' => VerificationStatus::Expired->value]);

            return back()
                ->withInput(['phone_number' => $phone])
                ->withErrors(['phone_number' => 'Không thể gửi mã OTP lúc này. Vui lòng thử lại sau.']);
        }

        $flash = back()->with('phone_code_sent', true);

        // Dev-only: hiển thị code trong UI khi dùng log driver
        if (app()->isLocal()) {
            $flash = $flash->with('dev_code', $code);
        }

        return $flash;
    }

    // ── POST /passport/verify/phone/confirm ──────────────────────────────────

    public function phoneConfirm(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate(['code' => ['required', 'string', 'digits:6']]);

        $pending = IdentityVerification::where('user_id', $user->id)
            ->where('method', VerificationMethod::PhoneOtp->value)
            ->where('status', VerificationStatus::Pending->value)
            ->where('code_expires_at', '>', now())
            ->latest()
            ->first();

        if (!$pending) {
            return back()->withErrors(['code' => 'Không có yêu cầu xác minh hợp lệ. Hãy yêu cầu gửi lại mã.']);
        }

        if (!hash_equals($pending->verification_code, $request->input('code'))) {
            return back()->withErrors(['code' => 'Mã xác minh không đúng.']);
        }

        DB::transaction(function () use ($user, $pending) {
            $pending->update([
                'status'            => VerificationStatus::Verified->value,
                'verified_at'       => now(),
                'verification_code' => null,
            ]);

            $user->update([
                'phone_number'      => $pending->phone_candidate,
                'phone_verified_at' => now(),
                'trust_level'       => max($user->trust_level, 2),
            ]);
        });

        return redirect()->route('passport.verify.index')
            ->with('success', 'Số điện thoại đã được xác minh. Trust Level nâng lên 2.');
    }
}

<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Modules\Assessment\Enums\VerificationMethod;
use Modules\Assessment\Enums\VerificationStatus;
use Modules\Assessment\Exceptions\CccdOcrException;
use Modules\Assessment\Models\IdentityVerification;
use Modules\Assessment\Services\CccdOcrService;

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

        // CCCD đã đăng ký nhưng chưa xác minh qua ảnh (manual text entry)
        $pendingCccd = IdentityVerification::where('user_id', $user->id)
            ->whereIn('method', [VerificationMethod::CccdOcr->value, VerificationMethod::CccdChip->value])
            ->where('status', VerificationStatus::Pending->value)
            ->latest()
            ->first();

        return view('assessment::passport.verify.index', compact(
            'user', 'verifications', 'pendingPhone', 'pendingCccd'
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

        IdentityVerification::create([
            'user_id'           => $user->id,
            'method'            => VerificationMethod::PhoneOtp->value,
            'status'            => VerificationStatus::Pending->value,
            'verification_code' => $code,
            'code_expires_at'   => now()->addMinutes(5),
            'phone_candidate'   => $phone,
        ]);

        return back()->with('phone_code_sent', true)->with('dev_code', $code);
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

    // ── POST /passport/verify/cccd ───────────────────────────────────────────

    /**
     * Dispatch dựa trên việc người dùng có upload ảnh hay không.
     *
     * Cả 2 ảnh → OCR path → status=verified, trust_level=3
     * Chỉ text → manual path → status=pending, trust_level giữ nguyên
     * Chỉ 1 ảnh → lỗi: cần cả 2 ảnh
     */
    public function cccdSubmit(Request $request): RedirectResponse
    {
        $hasFront = $request->hasFile('front_image');
        $hasBack  = $request->hasFile('back_image');

        if ($hasFront !== $hasBack) {
            $missing = $hasFront ? 'back_image' : 'front_image';
            return back()->withErrors([
                $missing => 'Cần upload cả 2 ảnh (mặt trước và mặt sau) để xác minh qua OCR.',
            ]);
        }

        return ($hasFront && $hasBack)
            ? $this->submitOcr($request)
            : $this->submitManual($request);
    }

    // ── Private: OCR path (cả 2 ảnh → trust_level 3, verified) ─────────────

    private function submitOcr(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'front_image' => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'back_image'  => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'front_image.mimes' => 'Ảnh mặt trước phải là JPEG, PNG hoặc WebP.',
            'front_image.max'   => 'Ảnh mặt trước không được vượt quá 5MB.',
            'back_image.mimes'  => 'Ảnh mặt sau phải là JPEG, PNG hoặc WebP.',
            'back_image.max'    => 'Ảnh mặt sau không được vượt quá 5MB.',
        ]);

        try {
            $ocr = app(CccdOcrService::class)->extract(
                $request->file('front_image'),
                $request->file('back_image')
            );
        } catch (CccdOcrException $e) {
            return back()->withErrors([$e->getField() => $e->getMessage()]);
        }

        similar_text(
            mb_strtolower(trim($ocr['full_name'])),
            mb_strtolower(trim($user->name ?? '')),
            $similarity
        );

        if ($similarity < 85) {
            return back()->withErrors([
                'front_image' => sprintf(
                    'Họ tên trên CCCD (%s) không khớp với tên tài khoản (%s). Độ khớp: %d%%. Hãy đảm bảo tên tài khoản trùng với tên trên CCCD.',
                    $ocr['full_name'],
                    $user->name,
                    round($similarity)
                ),
            ]);
        }

        $hash = hash('sha256', $ocr['cccd_number']);

        if (DB::table('users')->where('national_id_hash', $hash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['front_image' => 'Số CCCD này đã được liên kết với một tài khoản khác.']);
        }

        try {
            $issueDate = Carbon::createFromFormat('d/m/Y', $ocr['issue_date']);
        } catch (\Throwable) {
            return back()->withErrors(['back_image' => 'Ngày cấp nhận diện được không hợp lệ. Vui lòng chụp lại ảnh mặt sau rõ nét hơn.']);
        }

        $expiresAt    = $issueDate->copy()->addYears(10);
        $provinceCode = DB::table('provinces')->where('province_code', $ocr['province_code'])->value('province_code');

        DB::transaction(function () use ($user, $hash, $expiresAt, $provinceCode) {
            IdentityVerification::where('user_id', $user->id)
                ->whereIn('method', [VerificationMethod::CccdOcr->value, VerificationMethod::CccdChip->value])
                ->update(['status' => VerificationStatus::Expired->value]);

            IdentityVerification::create([
                'user_id'               => $user->id,
                'method'                => VerificationMethod::CccdOcr->value,
                'status'                => VerificationStatus::Verified->value,
                'verified_at'           => now(),
                'expires_at'            => $expiresAt,
                'issuing_province_code' => $provinceCode,
            ]);

            $user->update([
                'national_id_hash' => $hash,
                'trust_level'      => max($user->trust_level, 3),
            ]);
        });

        return redirect()->route('passport.verify.index')
            ->with('success', 'CCCD đã được xác minh qua ảnh thành công. Trust Level nâng lên 3.');
    }

    // ── Private: Manual path (chỉ text → status=pending, trust_level giữ nguyên) ──

    private function submitManual(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'cccd_number'  => ['required', 'string', 'digits:12'],
            'name_on_cccd' => ['required', 'string', 'max:100'],
            'issue_date'   => ['required', 'date', 'before:today'],
        ], [
            'cccd_number.digits' => 'Số CCCD phải gồm đúng 12 chữ số.',
            'issue_date.before'  => 'Ngày cấp phải trước hôm nay.',
        ]);

        similar_text(
            mb_strtolower(trim($request->input('name_on_cccd'))),
            mb_strtolower(trim($user->name ?? '')),
            $similarity
        );

        if ($similarity < 85) {
            return back()->withErrors([
                'name_on_cccd' => sprintf(
                    'Họ tên trên CCCD (%s) không khớp với tên tài khoản (%s). Độ khớp: %d%%.',
                    $request->input('name_on_cccd'),
                    $user->name,
                    round($similarity)
                ),
            ]);
        }

        $hash = hash('sha256', $request->input('cccd_number'));

        if (DB::table('users')->where('national_id_hash', $hash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['cccd_number' => 'Số CCCD này đã được liên kết với một tài khoản khác.']);
        }

        $issueDate    = Carbon::parse($request->input('issue_date'));
        $expiresAt    = $issueDate->copy()->addYears(10);
        $provinceCode = DB::table('provinces')
            ->where('province_code', substr($request->input('cccd_number'), 1, 2))
            ->value('province_code');

        DB::transaction(function () use ($user, $hash, $expiresAt, $provinceCode) {
            // Expire record cũ (nếu trước đó đã nhập text rồi nhập lại)
            IdentityVerification::where('user_id', $user->id)
                ->whereIn('method', [VerificationMethod::CccdOcr->value, VerificationMethod::CccdChip->value])
                ->where('status', VerificationStatus::Pending->value)
                ->update(['status' => VerificationStatus::Expired->value]);

            IdentityVerification::create([
                'user_id'               => $user->id,
                'method'                => VerificationMethod::CccdOcr->value,
                'status'                => VerificationStatus::Pending->value, // chưa xác minh OCR
                'expires_at'            => $expiresAt,
                'issuing_province_code' => $provinceCode,
            ]);

            // Lưu hash để ngăn trùng số CCCD, nhưng KHÔNG nâng trust_level
            $user->update(['national_id_hash' => $hash]);
        });

        return redirect()->route('passport.verify.index')
            ->with('info', 'Đã lưu thông tin CCCD. Upload ảnh 2 mặt để xác minh hoàn toàn và nhận Trust Level 3.');
    }
}

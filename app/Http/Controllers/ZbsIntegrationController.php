<?php

namespace App\Http\Controllers;

use App\Models\ZbsOauthToken;
use App\Services\OtpChannel\Exceptions\ZbsTokenException;
use App\Services\OtpChannel\OtpChannelManager;
use App\Services\OtpChannel\ZbsTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ZbsIntegrationController extends Controller
{
    // ── GET /dashboard/integrations/zbs ──────────────────────────────────────

    public function index(): View
    {
        $token  = ZbsOauthToken::first();
        $driver = config('otp_channel.driver', 'log');

        $status = match (true) {
            $driver !== 'zbs_zns'                           => 'not_configured',
            $token === null                                  => 'not_connected',
            $token->refresh_token_expires_at->isPast()      => 'refresh_expired',
            $token->access_token_expires_at->isPast()       => 'access_expired',
            $token->accessTokenExpiresSoon(60)               => 'expiring_soon',
            default                                          => 'connected',
        };

        return view('backend.integrations.zbs', compact('token', 'driver', 'status'));
    }

    // ── GET /dashboard/integrations/zbs/connect ──────────────────────────────

    public function connect(Request $request): RedirectResponse
    {
        $appId = config('otp_channel.drivers.zbs_zns.app_id');

        if (!$appId) {
            return back()->with('error', 'ZBS_APP_ID chưa được cấu hình trong .env');
        }

        // CSRF state — keyed per user to allow concurrent sessions
        $state = Str::random(40);
        Cache::put('zbs_oauth_state_' . $request->user()->id, $state, now()->addMinutes(10));

        $callbackUrl = route('backend.zbs.callback');

        $authUrl = 'https://oauth.zaloapp.com/v4/oa/permission?' . http_build_query([
            'app_id'       => $appId,
            'redirect_uri' => $callbackUrl,
            'state'        => $state,
        ]);

        return redirect($authUrl);
    }

    // ── GET /dashboard/integrations/zbs/callback ─────────────────────────────

    public function callback(Request $request, ZbsTokenService $tokenService): RedirectResponse
    {
        $userId        = $request->user()->id;
        $expectedState = Cache::pull('zbs_oauth_state_' . $userId);

        if (!$expectedState || $request->input('state') !== $expectedState) {
            return redirect()->route('backend.zbs.index')
                ->with('error', 'OAuth state không hợp lệ. Vui lòng thử lại.');
        }

        if ($request->has('error')) {
            return redirect()->route('backend.zbs.index')
                ->with('error', 'Người dùng từ chối uỷ quyền: ' . $request->input('error_description', 'unknown'));
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('backend.zbs.index')
                ->with('error', 'Không nhận được authorization code từ Zalo.');
        }

        $appId     = config('otp_channel.drivers.zbs_zns.app_id');
        $appSecret = config('otp_channel.drivers.zbs_zns.app_secret');
        $endpoint  = config('otp_channel.drivers.zbs_zns.endpoints.token');

        $response = Http::asForm()
            ->withHeaders(['secret_key' => $appSecret])
            ->timeout(10)
            ->post($endpoint, [
                'app_id'     => $appId,
                'grant_type' => 'authorization_code',
                'code'       => $code,
            ]);

        $data = $response->json();

        if (empty($data['access_token'])) {
            Log::error('[ZBS] Callback token exchange failed', ['response' => $data]);
            return redirect()->route('backend.zbs.index')
                ->with('error', 'Zalo từ chối cấp token: ' . ($data['message'] ?? 'Lỗi không xác định'));
        }

        $tokenService->storeTokens(
            accessToken:      $data['access_token'],
            accessExpiresIn:  (int) ($data['expires_in'] ?? 3600),
            refreshToken:     $data['refresh_token'],
            refreshExpiresIn: 90 * 86400,
        );

        Log::info('[ZBS] OAuth token obtained via web flow', ['app_id' => $appId]);

        return redirect()->route('backend.zbs.index')
            ->with('success', 'Kết nối Zalo OA thành công! OTP sẽ được gửi qua Zalo ZNS.');
    }

    // ── POST /dashboard/integrations/zbs/test ────────────────────────────────

    public function test(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^(0[3-9])[0-9]{8}$/'],
        ], [
            'phone.regex' => 'Số điện thoại không hợp lệ',
        ]);

        $testCode = '123456';

        try {
            $result = app(OtpChannelManager::class)->driver()->send(
                $request->input('phone'),
                $testCode,
            );
        } catch (ZbsTokenException $e) {
            return back()->with('error', 'Token lỗi: ' . $e->getMessage());
        }

        if ($result->success) {
            return back()->with('success',
                "Gửi test OTP thành công! Mã {$testCode} đã đến SĐT {$request->input('phone')} qua Zalo."
                . ($result->messageId ? " (msg_id: {$result->messageId})" : '')
            );
        }

        return back()->with('error', 'Gửi thất bại: ' . $result->error);
    }

    // ── POST /dashboard/integrations/zbs/disconnect ──────────────────────────

    public function disconnect(): RedirectResponse
    {
        ZbsOauthToken::query()->delete();
        Cache::forget('zbs:access_token:' . config('otp_channel.drivers.zbs_zns.app_id'));

        return redirect()->route('backend.zbs.index')
            ->with('success', 'Đã ngắt kết nối Zalo OA.');
    }
}

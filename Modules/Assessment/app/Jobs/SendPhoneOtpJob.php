<?php

namespace Modules\Assessment\Jobs;

use App\Services\OtpChannel\OtpChannelManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Assessment\Enums\VerificationStatus;
use Modules\Assessment\Models\IdentityVerification;

class SendPhoneOtpJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 30;   // seconds between retries
    public int $timeout = 30;

    public function __construct(public readonly int $verificationId) {}

    public function handle(OtpChannelManager $manager): void
    {
        $verification = IdentityVerification::find($this->verificationId);

        // Idempotency: skip if already confirmed or expired before delivery
        if (!$verification
            || $verification->status !== VerificationStatus::Pending
            || !$verification->phone_candidate
            || !$verification->verification_code
        ) {
            return;
        }

        $result = $manager->driver()->send(
            phone: $verification->phone_candidate,
            code:  $verification->verification_code,
        );

        if (!$result->success) {
            Log::warning('[SendPhoneOtp] Delivery failed — will retry', [
                'verification_id' => $this->verificationId,
                'error_code'      => $result->errorCode,
                'error'           => $result->error,
                'attempt'         => $this->attempts(),
            ]);

            throw new \RuntimeException(
                "OTP delivery failed [{$result->errorCode}]: {$result->error}"
            );
        }

        Log::info('[SendPhoneOtp] Delivered', [
            'verification_id' => $this->verificationId,
            'message_id'      => $result->messageId,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendPhoneOtp] Permanently failed after all retries', [
            'verification_id' => $this->verificationId,
            'error'           => $e->getMessage(),
        ]);
    }
}

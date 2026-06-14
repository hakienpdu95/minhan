<?php

namespace Modules\Assessment\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Modules\Assessment\Enums\VerificationStatus;
use Modules\Assessment\Mail\EmailVerificationMail;
use Modules\Assessment\Models\IdentityVerification;

class SendEmailVerificationLinkJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int    $verificationId,
        public readonly string $toEmail,
        public readonly string $userName,
        public readonly string $verifyUrl,
        public readonly string $expiresAt,
    ) {}

    public function handle(): void
    {
        $verification = IdentityVerification::find($this->verificationId);

        // Idempotent: skip if already confirmed or expired before delivery
        if (!$verification || $verification->status !== VerificationStatus::Pending) {
            return;
        }

        Mail::to($this->toEmail)
            ->send(new EmailVerificationMail($this->userName, $this->verifyUrl, $this->expiresAt));
    }
}

<?php

namespace Modules\Assessment\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmailVerificationMail extends Mailable
{
    public function __construct(
        public readonly string $userName,
        public readonly string $verifyUrl,
        public readonly string $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác minh email — Competency Passport',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'assessment::mail.email-verification',
        );
    }
}

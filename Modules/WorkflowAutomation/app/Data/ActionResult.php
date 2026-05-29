<?php
namespace Modules\WorkflowAutomation\Data;

use Spatie\LaravelData\Data;

class ActionResult extends Data
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $errorMessage = null,
        public readonly int     $durationMs   = 0,
        public readonly array   $meta         = [],
    ) {}

    public static function ok(int $ms = 0, array $meta = []): self
    {
        return new self(success: true, durationMs: $ms, meta: $meta);
    }

    public static function fail(string $error, int $ms = 0): self
    {
        return new self(success: false, errorMessage: $error, durationMs: $ms);
    }
}

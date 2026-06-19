<?php

namespace App\Shared\Ocr\Data;

use Spatie\LaravelData\Data;

class OcrResult extends Data
{
    public function __construct(
        public readonly string $rawText,
        public readonly string $driver,
        public readonly float  $processingTime,
    ) {}
}

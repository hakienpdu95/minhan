<?php

namespace Modules\BusinessProject\Enums;

/**
 * Handbook 4.7 Impact–Effort Matrix — trục Effort (Thấp → Cao), kết hợp với Impact để tính
 * priority quadrant (xem DiagnosisPriority::fromImpactAndEffort()).
 */
enum DiagnosisEffort: string
{
    case Low = 'low';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Thấp',
            self::High => 'Cao',
        };
    }
}

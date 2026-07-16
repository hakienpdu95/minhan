<?php

namespace Modules\BusinessProject\Enums;

enum IssueSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Thấp',
            self::Medium => 'Trung bình',
            self::High => 'Cao',
            self::Critical => 'Nghiêm trọng',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'badge-ghost',
            self::Medium => 'badge-info',
            self::High => 'badge-warning',
            self::Critical => 'badge-error',
        };
    }
}

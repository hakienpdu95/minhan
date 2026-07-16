<?php

namespace Modules\BusinessProject\Enums;

enum RiskStatus: string
{
    case Open = 'open';
    case Mitigated = 'mitigated';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Đang theo dõi',
            self::Mitigated => 'Đã giảm thiểu',
            self::Escalated => 'Đã escalate',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-warning',
            self::Mitigated => 'badge-success',
            self::Escalated => 'badge-error',
        };
    }
}

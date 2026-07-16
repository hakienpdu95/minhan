<?php

namespace Modules\BusinessProject\Enums;

enum IssueStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Đang mở',
            self::Resolved => 'Đã giải quyết',
            self::Escalated => 'Đã escalate',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-warning',
            self::Resolved => 'badge-success',
            self::Escalated => 'badge-error',
        };
    }
}

<?php

namespace Modules\BusinessProject\Enums;

/**
 * Spec Giai đoạn 4 (Transformation Workspace) — Roadmap theo 4 lớp thời gian
 * (Quick Wins / 30 / 90 / 365 ngày, master_flow.md Phần 6.1 + Phần 3).
 */
enum MilestoneCategory: string
{
    case QuickWin = 'quick_win';
    case Day30 = 'day_30';
    case Day90 = 'day_90';
    case Day365 = 'day_365';

    public function label(): string
    {
        return match ($this) {
            self::QuickWin => 'Quick Wins',
            self::Day30 => '30 ngày',
            self::Day90 => '90 ngày',
            self::Day365 => '365 ngày',
        };
    }

    /**
     * @return self[]
     */
    public static function ordered(): array
    {
        return [self::QuickWin, self::Day30, self::Day90, self::Day365];
    }
}

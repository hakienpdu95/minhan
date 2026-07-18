<?php

namespace Modules\BusinessProject\Enums;

/**
 * Handbook 4.7 — Impact–Effort Matrix, 4 quadrant chuẩn:
 *   Impact Cao + Effort Thấp  => Quick Win  (làm ngay)
 *   Impact Cao + Effort Cao   => Strategic  (lập Roadmap)
 *   Impact Thấp + Effort Thấp => Fill-in    (thực hiện khi có điều kiện)
 *   Impact Thấp + Effort Cao  => Low Priority (theo dõi)
 * Priority KHÔNG nhập tay — luôn tính từ Impact + Effort để đúng bản chất ma trận, tránh lệch
 * giữa 2 giá trị (Handbook không mô tả priority như 1 trường tự do).
 */
enum DiagnosisPriority: string
{
    case QuickWin = 'quick_win';
    case Strategic = 'strategic';
    case FillIn = 'fill_in';
    case LowPriority = 'low_priority';

    public function label(): string
    {
        return match ($this) {
            self::QuickWin => 'Làm ngay (Quick Win)',
            self::Strategic => 'Chiến lược',
            self::FillIn => 'Làm khi có điều kiện',
            self::LowPriority => 'Ưu tiên thấp',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::QuickWin => 'badge-success',
            self::Strategic => 'badge-primary',
            self::FillIn => 'badge-info',
            self::LowPriority => 'badge-ghost',
        };
    }

    public static function fromImpactAndEffort(DiagnosisImpact $impact, DiagnosisEffort $effort): self
    {
        $highImpact = $impact === DiagnosisImpact::High || $impact === DiagnosisImpact::Medium;

        return match (true) {
            $highImpact && $effort === DiagnosisEffort::Low => self::QuickWin,
            $highImpact && $effort === DiagnosisEffort::High => self::Strategic,
            ! $highImpact && $effort === DiagnosisEffort::Low => self::FillIn,
            default => self::LowPriority,
        };
    }
}

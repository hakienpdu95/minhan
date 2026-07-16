<?php

namespace Modules\BusinessProject\Enums;

/**
 * Handbook 4.6 Diagnosis Matrix, cột "Tác động". Bước 5 đánh giá theo Tài chính/Khách hàng/
 * Nhân sự/Vận hành/Chiến lược — gộp thành 1 mức tổng quan (MVP, không tách 5 chiều điểm số).
 */
enum DiagnosisImpact: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Thấp',
            self::Medium => 'Trung bình',
            self::High => 'Cao',
        };
    }
}

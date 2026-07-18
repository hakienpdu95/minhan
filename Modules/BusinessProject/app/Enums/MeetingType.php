<?php

namespace Modules\BusinessProject\Enums;

enum MeetingType: string
{
    case Interview = 'interview';
    case Workshop = 'workshop';
    case WeeklyReview = 'weekly_review';
    case Retrospective = 'retrospective';

    public function label(): string
    {
        return match ($this) {
            self::Interview => 'Phỏng vấn',
            self::Workshop => 'Hội thảo (Workshop)',
            self::WeeklyReview => 'Đánh giá hàng tuần',
            self::Retrospective => 'Họp tổng kết (Retrospective)',
        };
    }
}

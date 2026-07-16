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
            self::Interview => 'Interview',
            self::Workshop => 'Workshop',
            self::WeeklyReview => 'Weekly Review',
            self::Retrospective => 'Retrospective',
        };
    }
}

<?php

namespace Modules\Employee\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Intern   = 'intern';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Toàn thời gian',
            self::PartTime => 'Bán thời gian',
            self::Contract => 'Hợp đồng',
            self::Intern   => 'Thực tập',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::FullTime => 'badge-primary',
            self::PartTime => 'badge-info',
            self::Contract => 'badge-warning',
            self::Intern   => 'badge-ghost',
        };
    }
}

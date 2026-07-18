<?php

namespace Modules\BusinessProject\Enums;

enum ChangeRequestSourceType: string
{
    case Issue = 'issue';
    case Risk = 'risk';

    public function label(): string
    {
        return match ($this) {
            self::Issue => 'Vấn đề phát sinh',
            self::Risk => 'Rủi ro',
        };
    }
}

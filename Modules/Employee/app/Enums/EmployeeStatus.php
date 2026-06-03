<?php

namespace Modules\Employee\Enums;

enum EmployeeStatus: string
{
    case Active     = 'active';
    case OnLeave    = 'on_leave';
    case Resigned   = 'resigned';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active     => 'Đang làm việc',
            self::OnLeave    => 'Nghỉ phép',
            self::Resigned   => 'Đã nghỉ việc',
            self::Terminated => 'Đã chấm dứt',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active     => 'badge-success',
            self::OnLeave    => 'badge-warning',
            self::Resigned   => 'badge-ghost',
            self::Terminated => 'badge-error',
        };
    }
}

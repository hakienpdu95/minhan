<?php

namespace Modules\BusinessProject\Enums;

enum ChangeRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Submitted => 'Đã gửi phê duyệt',
            self::Approved => 'Đã duyệt',
            self::Rejected => 'Bị từ chối',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-ghost',
            self::Submitted => 'badge-warning',
            self::Approved => 'badge-success',
            self::Rejected => 'badge-error',
        };
    }
}

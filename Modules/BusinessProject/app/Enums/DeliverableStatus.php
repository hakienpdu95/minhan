<?php

namespace Modules\BusinessProject\Enums;

enum DeliverableStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nháp',
            self::Submitted => 'Đã gửi phê duyệt',
            self::Approved => 'Đã duyệt',
            self::Rejected => 'Bị từ chối',
            self::Confirmed => 'Đã xác nhận',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-ghost',
            self::Submitted => 'badge-warning',
            self::Approved => 'badge-success',
            self::Rejected => 'badge-error',
            self::Confirmed => 'badge-primary',
        };
    }
}

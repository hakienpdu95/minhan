<?php

namespace Modules\BusinessProject\Enums;

enum RenewalStatus: string
{
    case None = 'none';
    case Considering = 'considering';
    case Renewed = 'renewed';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Chưa ghi nhận',
            self::Considering => 'Đang cân nhắc',
            self::Renewed => 'Đã gia hạn',
            self::Lost => 'Không gia hạn',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::None => 'badge-ghost',
            self::Considering => 'badge-warning',
            self::Renewed => 'badge-success',
            self::Lost => 'badge-error',
        };
    }
}

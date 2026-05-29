<?php
namespace Modules\WorkflowAutomation\Enums;

enum WorkflowStatus: int
{
    case Pass      = 1;
    case Skip      = 2;
    case Fail      = 3;
    case Partial   = 4;
    case Scheduled = 5;

    public function label(): string {
        return match($this) {
            self::Pass      => 'Thành công',
            self::Skip      => 'Bỏ qua',
            self::Fail      => 'Lỗi',
            self::Partial   => 'Một phần',
            self::Scheduled => 'Đã lên lịch',
        };
    }
    public function badge(): string {
        return match($this) {
            self::Pass      => 'badge-success',
            self::Skip      => 'badge-ghost',
            self::Fail      => 'badge-error',
            self::Partial   => 'badge-warning',
            self::Scheduled => 'badge-info',
        };
    }
}

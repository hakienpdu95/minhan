<?php

namespace Modules\BusinessProject\Enums;

/**
 * `deliverables.type` là cột STRING (không phải DB enum) — xem lý do trong migration
 * create_deliverables_table. Enum PHP này chỉ là danh mục giá trị hợp lệ ở tầng ứng dụng,
 * mở rộng dần qua từng Phase mà KHÔNG cần ALTER TABLE.
 */
enum DeliverableType: string
{
    case BusinessContextReport = 'business_context_report';

    public function label(): string
    {
        return match ($this) {
            self::BusinessContextReport => 'Business Context Report',
        };
    }

    public function workspace(): BusinessProjectStage
    {
        return match ($this) {
            self::BusinessContextReport => BusinessProjectStage::Context,
        };
    }
}

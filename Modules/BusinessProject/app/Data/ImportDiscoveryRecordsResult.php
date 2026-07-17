<?php

namespace Modules\BusinessProject\Data;

/**
 * Kết quả import hàng loạt Discovery record — không phải request DTO (không cần Spatie Data
 * validate/from), chỉ mang kết quả về Controller để hiển thị.
 */
class ImportDiscoveryRecordsResult
{
    /** @param string[] $errors */
    public function __construct(
        public readonly int $total,
        public readonly int $imported,
        public readonly array $errors,
    ) {}
}

<?php

namespace Modules\BusinessProject\Enums;

enum BusinessProjectStage: string
{
    case Context = 'context';
    case Discovery = 'discovery';
    case Diagnosis = 'diagnosis';
    case Transformation = 'transformation';
    case Delivery = 'delivery';
    case Closing = 'closing';
    case Knowledge = 'knowledge';
    case CustomerSuccess = 'customer_success';

    public function label(): string
    {
        return match ($this) {
            self::Context => 'Bối cảnh Doanh nghiệp',
            self::Discovery => 'Khảo sát',
            self::Diagnosis => 'Chẩn đoán',
            self::Transformation => 'Chuyển đổi',
            self::Delivery => 'Triển khai',
            self::Closing => 'Đóng dự án',
            self::Knowledge => 'Tri thức',
            self::CustomerSuccess => 'Chăm sóc khách hàng',
        };
    }

    /**
     * Thứ tự cố định của 8 giai đoạn — dùng để xác định "stage kế tiếp"
     * khi AdvanceBusinessProjectStageAction chạy. Đủ 8 case ngay từ Vertical Slice 1
     * theo yêu cầu bắt buộc của spec (Phần 9 — bypass Diagnosis không được code cứng
     * bỏ qua state, chỉ tắt qua flag ở tầng Query).
     */
    public static function ordered(): array
    {
        return [
            self::Context,
            self::Discovery,
            self::Diagnosis,
            self::Transformation,
            self::Delivery,
            self::Closing,
            self::Knowledge,
            self::CustomerSuccess,
        ];
    }

    public function next(): ?self
    {
        $ordered = self::ordered();
        $index = array_search($this, $ordered, true);

        return $ordered[$index + 1] ?? null;
    }
}

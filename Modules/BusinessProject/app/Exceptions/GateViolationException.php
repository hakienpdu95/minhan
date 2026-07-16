<?php

namespace Modules\BusinessProject\Exceptions;

use Modules\BusinessProject\Data\StageGateResultData;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Ném khi AdvanceBusinessProjectStageAction bị gọi nhưng CheckStageGateEligibilityQuery
 * trả canAdvance=false. Mang theo StageGateResultData đầy đủ để controller render tooltip
 * liệt kê chính xác điều kiện còn thiếu (Phần 5B/5 spec — không phải lỗi chung "không thể chuyển").
 */
class GateViolationException extends HttpException
{
    public function __construct(public readonly StageGateResultData $gateResult)
    {
        $missing = collect($gateResult->conditions)
            ->reject(fn ($c) => $c->met)
            ->pluck('label')
            ->implode('; ');

        parent::__construct(
            statusCode: 422,
            message: "Chưa đủ điều kiện chuyển sang giai đoạn tiếp theo: {$missing}",
        );
    }
}

<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Models\Deliverable;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rule R4 — xác nhận thương mại: khách ký duyệt Proposal/SOW ngoài hệ thống (email/chữ ký tay,
 * MVP không làm chữ ký số), Consultant/PM tick "Confirmed" thủ công ở đây, hệ thống lưu
 * confirmed_at/confirmed_by làm bằng chứng audit. Generic trên mọi Deliverable (không riêng
 * Proposal/SOW) nhưng chỉ có ý nghĩa gate sau khi đã duyệt nội bộ (status=approved, qua Approval
 * Service — "xác nhận nội bộ trước khi gửi khách", spec Giai đoạn 4) — bắt buộc approved trước
 * khi confirm, không cho tick tắt qua bước duyệt nội bộ.
 */
class ConfirmDeliverableAction
{
    use AsAction;

    public function handle(Deliverable $deliverable): Deliverable
    {
        if ($deliverable->status?->value !== DeliverableStatus::Approved->value) {
            throw new HttpException(422, $deliverable->title.' phải được duyệt nội bộ trước khi xác nhận (Confirmed).');
        }

        $deliverable->update([
            'status' => DeliverableStatus::Confirmed->value,
            'confirmed_at' => now(),
            'confirmed_by' => Auth::id(),
        ]);

        return $deliverable;
    }
}

<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Contracts\DeliverableSignatureProvider;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Models\Deliverable;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rule R4 — xác nhận thương mại: khách ký duyệt Proposal/SOW ngoài hệ thống (email/chữ ký tay),
 * Consultant/PM tick "Confirmed" ở đây SAU KHI đã xác thực lại mật khẩu (Controller, tách riêng
 * khỏi việc ký — xem TransformationController::confirm()). Ngoài `confirmed_at/confirmed_by`
 * (giữ nguyên để không phá tương thích chỗ khác đang đọc 2 cột này), giờ còn tạo 1
 * `DeliverableSignature` — chữ ký nội bộ (xem DeliverableSignatureProvider) làm bằng chứng
 * chống sửa/chối bỏ mạnh hơn 1 boolean tick đơn thuần. Generic trên mọi Deliverable (không riêng
 * Proposal/SOW) nhưng chỉ có ý nghĩa gate sau khi đã duyệt nội bộ (status=approved, qua Approval
 * Service — "xác nhận nội bộ trước khi gửi khách", spec Giai đoạn 4) — bắt buộc approved trước
 * khi confirm, không cho tick tắt qua bước duyệt nội bộ.
 */
class ConfirmDeliverableAction
{
    use AsAction;

    public function __construct(
        private readonly DeliverableSignatureProvider $signer,
    ) {}

    public function handle(Deliverable $deliverable): Deliverable
    {
        if ($deliverable->status?->value !== DeliverableStatus::Approved->value) {
            throw new HttpException(422, $deliverable->title.' phải được duyệt nội bộ trước khi xác nhận (Confirmed).');
        }

        $version = $deliverable->versions()->where('version_number', $deliverable->current_version)->firstOrFail();

        $this->signer->sign($version, Auth::user());

        $deliverable->update([
            'status' => DeliverableStatus::Confirmed->value,
            'confirmed_at' => now(),
            'confirmed_by' => Auth::id(),
        ]);

        return $deliverable;
    }
}

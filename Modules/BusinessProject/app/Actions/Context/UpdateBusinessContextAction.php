<?php

namespace Modules\BusinessProject\Actions\Context;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessContextData;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Models\BusinessContext;
use Modules\BusinessProject\Models\DeliverableVersion;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UpdateBusinessContextAction
{
    use AsAction;

    public function handle(BusinessContext $context, StoreBusinessContextData $data): BusinessContext
    {
        $deliverable = $context->deliverable;

        if ($deliverable !== null && $deliverable->status?->value === DeliverableStatus::Confirmed->value) {
            throw new HttpException(422, 'Business Context Report đã confirmed, không thể sửa trực tiếp.');
        }

        return DB::transaction(function () use ($context, $data, $deliverable): BusinessContext {
            $context->update([
                'company_profile' => $data->company_profile,
                'stakeholders' => $data->stakeholders,
                'strategic_goals' => $data->strategic_goals,
                'updated_by' => Auth::id(),
            ]);

            if ($deliverable !== null) {
                $nextVersion = $deliverable->current_version + 1;

                DeliverableVersion::create([
                    'deliverable_id' => $deliverable->id,
                    'version_number' => $nextVersion,
                    'content' => [
                        'company_profile' => $data->company_profile,
                        'stakeholders' => $data->stakeholders,
                        'strategic_goals' => $data->strategic_goals,
                    ],
                    'change_summary' => 'Cập nhật Business Context Report.',
                    'created_by' => Auth::id(),
                ]);

                // Sửa nội dung sau khi đã submit/approve coi như quay lại draft —
                // phải Gửi phê duyệt lại (đúng nguyên tắc "duyệt lại khi có thay đổi").
                $deliverable->update([
                    'current_version' => $nextVersion,
                    'status' => DeliverableStatus::Draft->value,
                    'updated_by' => Auth::id(),
                ]);
            }

            return $context;
        });
    }
}

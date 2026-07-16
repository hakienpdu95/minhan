<?php

namespace Modules\BusinessProject\Actions\Context;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessContextData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Events\BusinessContextCreated;
use Modules\BusinessProject\Exceptions\DuplicateContextException;
use Modules\BusinessProject\Models\BusinessContext;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableVersion;

/**
 * Rule R1 — enforce ở tầng Service, ĐỘC LẬP với unique index DB (2 lưới an toàn khác nhau,
 * không thay thế nhau — xem business_contexts migration). Chặn cả trường hợp nhân viên
 * "sửa lại" bằng cách gọi lại action tạo mới thay vì UpdateBusinessContextAction.
 */
class CreateBusinessContextAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreBusinessContextData $data): BusinessContext
    {
        if (BusinessContext::where('business_project_id', $businessProject->id)->exists()) {
            throw new DuplicateContextException();
        }

        return DB::transaction(function () use ($businessProject, $data): BusinessContext {
            $deliverable = Deliverable::create([
                'organization_id' => $businessProject->organization_id,
                'uuid' => Str::uuid(),
                'business_project_id' => $businessProject->id,
                'workspace' => BusinessProjectStage::Context->value,
                'type' => DeliverableType::BusinessContextReport->value,
                'title' => 'Business Context Report',
                'current_version' => 1,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            DeliverableVersion::create([
                'deliverable_id' => $deliverable->id,
                'version_number' => 1,
                'content' => [
                    'company_profile' => $data->company_profile,
                    'stakeholders' => $data->stakeholders,
                    'strategic_goals' => $data->strategic_goals,
                ],
                'change_summary' => 'Khởi tạo Business Context Report.',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $context = BusinessContext::create([
                'organization_id' => $businessProject->organization_id,
                'business_project_id' => $businessProject->id,
                'company_profile' => $data->company_profile,
                'stakeholders' => $data->stakeholders,
                'strategic_goals' => $data->strategic_goals,
                'deliverable_id' => $deliverable->id,
                'created_by' => Auth::id(),
            ]);

            event(new BusinessContextCreated($context));

            return $context;
        });
    }
}

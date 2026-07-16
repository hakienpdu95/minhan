<?php

namespace Modules\BusinessProject\Actions\Discovery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreDiscoveryRecordData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableVersion;

/**
 * Spec Giai đoạn 2 (Discovery Workspace): mỗi bản ghi Interview/Observation/Document Review/
 * Data Review/Process Map nhập trực tiếp tự động trở thành 1 Deliverable CON của Business
 * Discovery Report (quan hệ parent_id — khác evidence_links, xem Phần 6.2 spec). Report cha
 * được tự khởi tạo rỗng (draft, current_version=0 — "chưa có nội dung tổng hợp") nếu chưa có,
 * để Consultant không phải "tạo Discovery Report" như một bước riêng trước khi ghi Interview
 * đầu tiên — nhập liệu và Deliverable Engine là MỘT luồng.
 */
class AddDiscoveryRecordAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreDiscoveryRecordData $data): Deliverable
    {
        return DB::transaction(function () use ($businessProject, $data): Deliverable {
            $report = $this->findOrCreateReportContainer($businessProject);
            $type = DeliverableType::from($data->type);

            $record = Deliverable::create([
                'organization_id' => $businessProject->organization_id,
                'uuid' => Str::uuid(),
                'business_project_id' => $businessProject->id,
                'workspace' => BusinessProjectStage::Discovery->value,
                'type' => $type->value,
                'title' => $data->title,
                'parent_id' => $report->id,
                'current_version' => 1,
                'status' => DeliverableStatus::Draft->value,
                'created_by' => Auth::id(),
            ]);

            DeliverableVersion::create([
                'deliverable_id' => $record->id,
                'version_number' => 1,
                'content' => [
                    'notes' => $data->notes,
                    'occurred_at' => $data->occurred_at,
                    'participants' => $data->participants,
                ],
                'change_summary' => 'Khởi tạo '.$type->label().'.',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            return $record;
        });
    }

    private function findOrCreateReportContainer(BusinessProject $businessProject): Deliverable
    {
        $report = $businessProject->deliverables()
            ->where('type', DeliverableType::BusinessDiscoveryReport->value)
            ->whereNull('parent_id')
            ->first();

        if ($report !== null) {
            return $report;
        }

        return Deliverable::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'workspace' => BusinessProjectStage::Discovery->value,
            'type' => DeliverableType::BusinessDiscoveryReport->value,
            'title' => 'Business Discovery Report',
            'current_version' => 0,
            'status' => DeliverableStatus::Draft->value,
            'created_by' => Auth::id(),
        ]);
    }
}

<?php

namespace Modules\BusinessProject\Workflow;

use Illuminate\Support\Str;
use Modules\BusinessProject\Enums\MeetingType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Meeting;
use Modules\WorkflowAutomation\Contracts\ActionExecutor;
use Modules\WorkflowAutomation\Data\ActionResult;
use Modules\WorkflowAutomation\Data\TriggerPayload;
use Modules\WorkflowAutomation\Models\WorkflowStep;

/**
 * Master flow Giai đoạn 6: "đóng thành công → tự động tạo Project Retrospective (gợi ý)".
 * "Gợi ý" = tạo 1 Meeting type=retrospective CHƯA lên lịch (held_at=null), Lead Consultant/PM
 * tự vào Delivery Workspace điền ngày + minutes thật qua luồng Meeting sẵn có — không tự chế nội
 * dung Retrospective thay con người.
 */
class CreateProjectRetrospectiveExecutor implements ActionExecutor
{
    public function type(): string   { return 'business_project.create_retrospective'; }
    public function label(): string  { return 'Tạo Project Retrospective (gợi ý)'; }
    public function module(): string { return 'BusinessProject'; }

    public function stepConfigFields(): array
    {
        return [];
    }

    public function execute(WorkflowStep $step, TriggerPayload $payload): ActionResult
    {
        $start = microtime(true);

        try {
            if ($payload->subjectType !== 'BusinessProject' || !$payload->subjectId) {
                return ActionResult::fail('Payload không mang BusinessProject subject.', $this->ms($start));
            }

            $businessProject = BusinessProject::withoutTenant()->find($payload->subjectId);
            if (!$businessProject) {
                return ActionResult::fail("BusinessProject #{$payload->subjectId} không tồn tại.", $this->ms($start));
            }

            // Idempotent — retry/duplicate dispatch không tạo thêm bản ghi thứ 2.
            $existing = Meeting::withoutTenant()
                ->where('business_project_id', $businessProject->id)
                ->where('type', MeetingType::Retrospective->value)
                ->first();

            if ($existing) {
                return ActionResult::ok($this->ms($start), ['skipped' => 'already_exists', 'meeting_id' => $existing->id]);
            }

            $createdBy = $payload->actorId ?? $businessProject->updated_by ?? $businessProject->created_by;
            if (!$createdBy) {
                return ActionResult::fail('Không xác định được created_by cho Meeting.', $this->ms($start));
            }

            $meeting = Meeting::withoutTenant()->create([
                'organization_id' => $businessProject->organization_id,
                'uuid' => (string) Str::uuid(),
                'business_project_id' => $businessProject->id,
                'type' => MeetingType::Retrospective->value,
                'title' => "Project Retrospective — {$businessProject->code} {$businessProject->name}",
                'held_at' => null,
                'created_by' => $createdBy,
            ]);

            return ActionResult::ok($this->ms($start), ['meeting_id' => $meeting->id]);
        } catch (\Throwable $e) {
            return ActionResult::fail($e->getMessage(), $this->ms($start));
        }
    }

    private function ms(float $start): int
    {
        return (int) ((microtime(true) - $start) * 1000);
    }
}

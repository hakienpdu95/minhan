<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreWeeklyReportData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\IssueStatus;
use Modules\BusinessProject\Enums\RiskStatus;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableVersion;
use Modules\Task\Enums\TaskStatus;
use Modules\Task\Models\Task;

/**
 * Spec Giai đoạn 5: "Report tổng hợp từ dữ liệu đã có: task done/pending, issue mới, risk thay
 * đổi — hệ thống prefill, Consultant bổ sung nhận định." Mỗi tuần là 1 Deliverable MỚI (KHÔNG
 * qua UpsertSingletonDeliverableAction — đó là cho "1 bản/project", ở đây nhiều bản/project theo
 * thời gian), snapshot số liệu tại thời điểm tạo báo cáo, không tính lại khi xem lại report cũ.
 */
class CreateWeeklyReportAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreWeeklyReportData $data): Deliverable
    {
        return DB::transaction(function () use ($businessProject, $data): Deliverable {
            $previousReport = $businessProject->deliverables()
                ->where('type', DeliverableType::WeeklyReport->value)
                ->orderByDesc('created_at')
                ->first();

            $since = $previousReport?->created_at ?? $businessProject->created_at;

            $tasksQuery = Task::where('business_project_id', $businessProject->id);
            $tasksDone = (clone $tasksQuery)->where('status', TaskStatus::Done->value)->count();
            $tasksPending = (clone $tasksQuery)->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])->count();

            $newIssuesCount = $businessProject->issues()->where('created_at', '>=', $since)->count();
            $openIssuesCount = $businessProject->issues()->where('status', IssueStatus::Open->value)->count();
            $openRisksCount = $businessProject->risks()->where('status', RiskStatus::Open->value)->count();

            $prefill = [
                'tasks_done' => $tasksDone,
                'tasks_pending' => $tasksPending,
                'new_issues' => $newIssuesCount,
                'open_issues' => $openIssuesCount,
                'open_risks' => $openRisksCount,
                'since' => $since?->toDateString(),
            ];

            $deliverable = Deliverable::create([
                'organization_id' => $businessProject->organization_id,
                'uuid' => Str::uuid(),
                'business_project_id' => $businessProject->id,
                'workspace' => BusinessProjectStage::Delivery->value,
                'type' => DeliverableType::WeeklyReport->value,
                'title' => 'Weekly Report — '.now()->toDateString(),
                'current_version' => 1,
                'status' => DeliverableStatus::Draft->value,
                'created_by' => Auth::id(),
            ]);

            DeliverableVersion::create([
                'deliverable_id' => $deliverable->id,
                'version_number' => 1,
                'content' => [
                    'prefill' => $prefill,
                    'narrative' => $data->narrative,
                ],
                'change_summary' => 'Khởi tạo Weekly Report.',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            return $deliverable;
        });
    }
}

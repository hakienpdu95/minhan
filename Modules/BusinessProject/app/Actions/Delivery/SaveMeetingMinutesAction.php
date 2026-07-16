<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\SaveMeetingMinutesData;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableVersion;
use Modules\BusinessProject\Models\Meeting;

/**
 * Minutes là Deliverable con-1-1 của đúng Meeting này (`meetings.deliverable_id`) — KHÔNG dùng
 * `UpsertSingletonDeliverableAction` (khoá theo business_project_id+type, chỉ đúng khi có DUY
 * NHẤT 1 bản/project; ở đây có nhiều Meeting cùng type `meeting_minutes` trong 1 project).
 */
class SaveMeetingMinutesAction
{
    use AsAction;

    public function handle(Meeting $meeting, SaveMeetingMinutesData $data): Deliverable
    {
        return DB::transaction(function () use ($meeting, $data): Deliverable {
            $deliverable = $meeting->deliverable;

            if ($deliverable === null) {
                $deliverable = Deliverable::create([
                    'organization_id' => $meeting->organization_id,
                    'uuid' => Str::uuid(),
                    'business_project_id' => $meeting->business_project_id,
                    'workspace' => DeliverableType::MeetingMinutes->workspace()->value,
                    'type' => DeliverableType::MeetingMinutes->value,
                    'title' => 'Minutes — '.$meeting->title,
                    'current_version' => 0,
                    'status' => DeliverableStatus::Draft->value,
                    'created_by' => Auth::id(),
                ]);

                $meeting->update(['deliverable_id' => $deliverable->id]);
            }

            $actionItems = collect(preg_split('/\r\n|\r|\n/', (string) $data->action_items))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();

            $nextVersion = $deliverable->current_version + 1;

            DeliverableVersion::create([
                'deliverable_id' => $deliverable->id,
                'version_number' => $nextVersion,
                'content' => [
                    'minutes' => $data->minutes,
                    'action_items' => $actionItems,
                ],
                'change_summary' => 'Cập nhật Meeting Minutes.',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $deliverable->update([
                'current_version' => $nextVersion,
                'updated_by' => Auth::id(),
            ]);

            return $deliverable;
        });
    }
}

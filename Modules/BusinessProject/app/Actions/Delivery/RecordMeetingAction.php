<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreMeetingData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Meeting;

class RecordMeetingAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreMeetingData $data): Meeting
    {
        return Meeting::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'type' => $data->type,
            'title' => $data->title,
            'held_at' => $data->held_at,
            'created_by' => Auth::id(),
        ]);
    }
}

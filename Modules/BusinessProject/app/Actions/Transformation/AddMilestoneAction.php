<?php

namespace Modules\BusinessProject\Actions\Transformation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreMilestoneData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Milestone;

class AddMilestoneAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreMilestoneData $data): Milestone
    {
        return Milestone::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'category' => $data->category,
            'title' => $data->title,
            'description' => $data->description,
            'target_date' => $data->target_date,
            'created_by' => Auth::id(),
        ]);
    }
}

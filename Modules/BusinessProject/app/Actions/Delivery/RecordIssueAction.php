<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreIssueData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Issue;

class RecordIssueAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreIssueData $data): Issue
    {
        return Issue::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'title' => $data->title,
            'description' => $data->description,
            'severity' => $data->severity,
            'status' => 'open',
            'created_by' => Auth::id(),
        ]);
    }
}

<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreRiskData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Risk;

class RecordRiskAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreRiskData $data): Risk
    {
        return Risk::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'title' => $data->title,
            'description' => $data->description,
            'likelihood' => $data->likelihood,
            'impact' => $data->impact,
            'status' => 'open',
            'created_by' => Auth::id(),
        ]);
    }
}

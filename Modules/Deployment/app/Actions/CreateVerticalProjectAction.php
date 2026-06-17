<?php

namespace Modules\Deployment\Actions;

use App\Foundation\VerticalDefinition;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Deployment\Data\CreateVerticalProjectData;
use Modules\Project\Models\Project;

class CreateVerticalProjectAction
{
    use AsAction;

    public function handle(CreateVerticalProjectData $data, VerticalDefinition $vertical): Project
    {
        $user = auth()->user();

        return Project::create([
            'uuid'            => (string) Str::uuid(),
            'organization_id' => TenantContext::getOrganizationId(),
            'owner_id'        => $user->id,
            'code'            => strtoupper(trim($data->code)),
            'name'            => $data->name,
            'description'     => $data->description,
            'category'        => $vertical->code(),
            'vertical_code'   => $vertical->code(),
            'status'          => $data->status->value,
            'priority'        => 'medium',
            'start_date'      => $data->start_date,
            'end_date'        => $data->end_date,
            'created_by'      => $user->id,
            'updated_by'      => $user->id,
        ]);
    }
}

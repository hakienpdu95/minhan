<?php

namespace Modules\Organization\Actions\Backend;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\Organization;

class UpdateOrganizationAction
{
    use AsAction;

    public function handle(Organization $organization, array $validated): Organization
    {
        $organization->update($validated);

        return $organization;
    }
}

<?php

namespace Modules\Organization\Actions\Backend;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\Organization;

class UpdateOrganizationAction
{
    use AsAction;

    public function handle(Organization $organization, array $validated): Organization
    {
        if (array_key_exists('description', $validated)) {
            $validated['description'] = sanitize_rich_text($validated['description']);
        }

        $organization->update($validated);

        return $organization;
    }
}

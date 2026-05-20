<?php

namespace Modules\Organization\Actions\Backend;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\Organization;

class StoreOrganizationAction
{
    use AsAction;

    public function handle(array $validated): Organization
    {
        if (empty($validated['slug'])) {
            $validated['slug'] = Organization::generateSlug($validated['name']);
        }

        $validated['description'] = sanitize_rich_text($validated['description'] ?? null);

        return Organization::create($validated);
    }
}

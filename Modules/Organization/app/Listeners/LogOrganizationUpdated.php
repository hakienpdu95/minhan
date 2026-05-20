<?php

namespace Modules\Organization\Listeners;

use Illuminate\Support\Facades\Auth;
use Modules\Organization\Events\OrganizationUpdated;

class LogOrganizationUpdated
{
    public function handle(OrganizationUpdated $event): void
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($event->organization)
            ->withProperties([
                'organization_id' => $event->organization->id,
                'name'            => $event->organization->name,
                'changes'         => $event->organization->getChanges(),
            ])
            ->log('organization.updated');
    }
}

<?php

namespace Modules\Organization\Listeners;

use Illuminate\Support\Facades\Auth;
use Modules\Organization\Events\OrganizationCreated;

class LogOrganizationCreated
{
    public function handle(OrganizationCreated $event): void
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($event->organization)
            ->withProperties([
                'organization_id' => $event->organization->id,
                'name'            => $event->organization->name,
            ])
            ->log('organization.created');
    }
}

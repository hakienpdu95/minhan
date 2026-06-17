<?php

namespace App\View\Composers;

use App\Foundation\Vertical\OrganizationVertical;
use App\Foundation\VerticalRegistry;
use App\Shared\Tenancy\TenantContext;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $orgId = TenantContext::getOrganizationId();

        if (! $orgId) {
            $view->with('activeVerticals', collect());
            return;
        }

        // BelongsToOrganization global scope tự filter theo $orgId — không cần explicit where
        $verticals = OrganizationVertical::where('status', 'active')
            ->get()
            ->map(fn ($ov) => VerticalRegistry::resolve($ov->vertical_code))
            ->filter()
            ->values();

        $view->with('activeVerticals', $verticals);
    }
}

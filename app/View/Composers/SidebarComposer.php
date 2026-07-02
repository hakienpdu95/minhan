<?php

namespace App\View\Composers;

use App\Foundation\Vertical\DatabaseVertical;
use App\Foundation\Vertical\VerticalTemplate;
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

        $verticals = VerticalTemplate::where('organization_id', $orgId)
            ->where('status', 'active')
            ->where('is_active', true)
            ->get()
            ->map(fn (VerticalTemplate $template) => new DatabaseVertical($template))
            ->values();

        $view->with('activeVerticals', $verticals);
    }
}

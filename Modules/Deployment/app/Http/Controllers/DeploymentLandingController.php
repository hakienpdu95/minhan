<?php

namespace Modules\Deployment\Http\Controllers;

use App\Foundation\Vertical\OrganizationVertical;
use App\Foundation\VerticalRegistry;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DeploymentLandingController extends Controller
{
    public function index(Request $request)
    {
        $orgId = TenantContext::getOrganizationId();

        $verticals = collect();

        if ($orgId) {
            $verticals = OrganizationVertical::where('status', 'active')
                ->get()
                ->map(fn ($ov) => VerticalRegistry::resolve($ov->vertical_code))
                ->filter()
                ->values();
        }

        return view('deployment::landing', compact('verticals'));
    }
}

<?php

namespace Modules\Deployment\Http\Controllers;

use App\Foundation\Vertical\OrganizationVertical;
use App\Foundation\VerticalRegistry;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Project\Models\Project;

class DeploymentLandingController extends Controller
{
    public function index(Request $request)
    {
        $orgId        = TenantContext::getOrganizationId();
        $isSuperAdmin = $request->user()?->hasRole('super-admin') ?? false;

        $verticals         = collect();
        $targetsByVertical = [];
        $projectsByVertical = [];

        if ($orgId || $isSuperAdmin) {
            // Super-admin sees all active verticals across orgs; others see their own org's
            $verticals = $isSuperAdmin
                ? OrganizationVertical::withoutTenant()
                    ->where('status', 'active')
                    ->get()
                    ->map(fn ($ov) => VerticalRegistry::resolve($ov->vertical_code))
                    ->filter()
                    ->unique(fn ($v) => $v->code())   // dedupe (multiple orgs same code)
                    ->values()
                : OrganizationVertical::where('status', 'active')
                    ->get()
                    ->map(fn ($ov) => VerticalRegistry::resolve($ov->vertical_code))
                    ->filter()
                    ->values();

            foreach ($verticals as $v) {
                $targetQuery = $isSuperAdmin
                    ? DeploymentTarget::withoutTenant()
                        ->where('vertical_code', $v->code())
                        ->with('targetOrganization')
                    : DeploymentTarget::where('vertical_code', $v->code())
                        ->with('targetOrganization');

                $targets = $targetQuery
                    ->orderByDesc('readiness_score')
                    ->limit(10)
                    ->get();

                if ($targets->isNotEmpty()) {
                    $targetsByVertical[$v->code()] = $targets;
                }

                $projectQuery = $isSuperAdmin
                    ? Project::withoutTenant()->where('vertical_code', $v->code())
                    : Project::where('vertical_code', $v->code());

                $projects = $projectQuery->orderByDesc('created_at')->limit(5)->get(['id', 'name', 'code', 'status']);
                if ($projects->isNotEmpty()) {
                    $projectsByVertical[$v->code()] = $projects;
                }
            }
        }

        return view('deployment::landing', compact('verticals', 'targetsByVertical', 'projectsByVertical', 'isSuperAdmin'));
    }
}

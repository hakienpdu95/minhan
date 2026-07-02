<?php

namespace Modules\Deployment\Http\Controllers;

use App\Foundation\Vertical\DatabaseVertical;
use App\Foundation\Vertical\VerticalTemplate;
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

        $verticals          = collect();
        $targetsByVertical  = [];
        $projectsByVertical = [];
        $openIssuesByVertical = [];

        if ($orgId || $isSuperAdmin) {
            // Super-admin sees all active verticals across orgs; others see their own org's
            $verticals = $isSuperAdmin
                ? VerticalTemplate::whereNotNull('organization_id')
                    ->where('status', 'active')
                    ->where('is_active', true)
                    ->get()
                    ->map(fn (VerticalTemplate $t) => new DatabaseVertical($t))
                    ->unique(fn ($v) => $v->code())   // dedupe (multiple orgs same code)
                    ->values()
                : VerticalTemplate::where('organization_id', $orgId)
                    ->where('status', 'active')
                    ->where('is_active', true)
                    ->get()
                    ->map(fn (VerticalTemplate $t) => new DatabaseVertical($t))
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

                $targetIds = $targets->pluck('id');
                $openIssuesByVertical[$v->code()] = $targetIds->isNotEmpty()
                    ? \Modules\Deployment\Models\DeploymentIssue::whereIn('deployment_target_id', $targetIds)
                        ->whereIn('status', [\Modules\Deployment\Enums\IssueStatus::Open->value, \Modules\Deployment\Enums\IssueStatus::InProgress->value])
                        ->count()
                    : 0;

                $projectQuery = $isSuperAdmin
                    ? Project::withoutTenant()->where('vertical_code', $v->code())
                    : Project::where('vertical_code', $v->code());

                $projects = $projectQuery->orderByDesc('created_at')->limit(5)->get(['id', 'name', 'code', 'status']);
                if ($projects->isNotEmpty()) {
                    $projectsByVertical[$v->code()] = $projects;
                }
            }
        }

        return view('deployment::landing', compact('verticals', 'targetsByVertical', 'projectsByVertical', 'openIssuesByVertical', 'isSuperAdmin'));
    }
}

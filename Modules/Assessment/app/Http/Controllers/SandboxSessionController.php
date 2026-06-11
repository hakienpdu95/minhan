<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Models\SandboxEnvironment;
use Modules\Assessment\Models\SandboxSession;
use Modules\Assessment\Models\WorkforceProfile;

class SandboxSessionController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $orgId = TenantContext::getOrganizationId();

        $profile = WorkforceProfile::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        $environments = SandboxEnvironment::whereNull('organization_id')
            ->orWhere('organization_id', $orgId)
            ->where('is_active', true)
            ->with('tasks')
            ->orderBy('tier')
            ->get();

        $mySessions = $profile
            ? SandboxSession::where('workforce_profile_id', $profile->id)
                ->with('task.environment')
                ->orderByDesc('started_at')
                ->limit(20)
                ->get()
            : collect();

        $stats = $profile ? [
            'total'   => $profile->sandbox_sessions_total ?? 0,
            'hours'   => $profile->sandbox_hours_total ?? 0,
            'avg'     => $profile->sandbox_score_avg,
            'passed'  => $mySessions->where('passed', true)->count(),
        ] : ['total' => 0, 'hours' => 0, 'avg' => null, 'passed' => 0];

        return view('assessment::sandbox.index', compact(
            'profile', 'environments', 'mySessions', 'stats'
        ));
    }

    public function show(SandboxSession $sandboxSession): View
    {
        $this->authorize('assessment.results');
        $sandboxSession->load(['task.environment', 'submission', 'activities']);
        return view('assessment::sandbox.show', ['session' => $sandboxSession]);
    }
}

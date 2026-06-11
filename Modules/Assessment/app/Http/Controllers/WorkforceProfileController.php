<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Models\CareerPathwayStep;
use Modules\Assessment\Models\WorkforceCertification;
use Modules\Assessment\Models\WorkforceProfile;
use Modules\Assessment\Models\WorkforceProfileHistory;
use Modules\Assessment\Models\SandboxSession;
use Modules\Employee\Models\Employee;

class WorkforceProfileController extends Controller
{
    /** Dashboard cá nhân — Digital Twin của user đang đăng nhập */
    public function me(Request $request): View
    {
        $user     = $request->user();
        $orgId    = TenantContext::getOrganizationId();
        $employee = Employee::where('user_id', $user->id)->first();

        $profile = WorkforceProfile::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        $certifications = $profile
            ? WorkforceCertification::where('workforce_profile_id', $profile->id)
                ->orderByDesc('issued_at')
                ->get()
            : collect();

        $recentHistory = $profile
            ? WorkforceProfileHistory::where('workforce_profile_id', $profile->id)
                ->orderByDesc('recorded_at')
                ->limit(5)
                ->get()
            : collect();

        $sandboxStats = $profile
            ? SandboxSession::where('workforce_profile_id', $profile->id)
                ->where('status', 'completed')
                ->selectRaw('COUNT(*) as total, SUM(duration_minutes) as minutes, AVG(final_score) as avg_score')
                ->first()
            : null;

        $currentPathwayStep = $profile
            ? CareerPathwayStep::whereNull('organization_id')
                ->where('from_level', $profile->tdwcf_maturity_level)
                ->first()
            : null;

        $allPathwaySteps = CareerPathwayStep::whereNull('organization_id')
            ->orderBy('step_order')
            ->get();

        return view('assessment::workforce.me', compact(
            'user', 'employee', 'profile', 'certifications',
            'recentHistory', 'sandboxStats', 'currentPathwayStep', 'allPathwaySteps'
        ));
    }

    /** Danh sách workforce profiles — Admin / HR view */
    public function index(Request $request): View
    {
        $this->authorize('assessment.results');

        $maturityLevels = [
            'DIGITAL_BEGINNER', 'DIGITAL_AWARE', 'DIGITAL_PRACTITIONER',
            'DIGITAL_PROFESSIONAL', 'DIGITAL_LEADER',
        ];

        $total    = WorkforceProfile::count();
        $byLevel  = WorkforceProfile::selectRaw('tdwcf_maturity_level, count(*) as cnt')
            ->whereNotNull('tdwcf_maturity_level')
            ->groupBy('tdwcf_maturity_level')
            ->pluck('cnt', 'tdwcf_maturity_level');

        return view('assessment::workforce.index', compact('maturityLevels', 'total', 'byLevel'));
    }

    /** Chi tiết 1 profile — Admin view */
    public function show(WorkforceProfile $workforceProfile): View
    {
        $this->authorize('assessment.results');

        $workforceProfile->load(['employee', 'certifications.definition', 'sandboxSessions']);
        $history = WorkforceProfileHistory::where('workforce_profile_id', $workforceProfile->id)
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get();

        return view('assessment::workforce.show', [
            'workforceProfile' => $workforceProfile,
            'history'          => $history,
        ]);
    }

    /** API: danh sách profiles cho Tabulator */
    public function apiIndex(Request $request)
    {
        $this->authorize('assessment.results');

        $q = WorkforceProfile::query()
            ->with(['employee:id,user_id,full_name,department_id', 'employee.department:id,name', 'certifications:id,workforce_profile_id,status']);

        if ($level = $request->get('maturity_level')) {
            $q->where('tdwcf_maturity_level', $level);
        }
        if ($search = $request->get('search')) {
            $q->whereHas('employee', fn($e) => $e->where('full_name', 'like', "%{$search}%"));
        }

        $profiles = $q->orderByDesc('workforce_trust_score')
            ->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $profiles->map(fn($p) => [
                'id'                => $p->id,
                'employee_name'          => $p->employee?->full_name ?? '—',
                'department'             => $p->employee?->department?->name ?? null,
                'tdwcf_maturity_level'   => $p->tdwcf_maturity_level,
                'tdwcf_score'            => $p->tdwcf_score,
                'ai_readiness_score'     => $p->ai_readiness_score,
                'workforce_trust_score'  => $p->workforce_trust_score,
                'sandbox_sessions_total' => $p->sandbox_sessions_total ?? 0,
                'last_assessed_at'       => $p->tdwcf_assessed_at?->format('d/m/Y'),
            ]),
            'last_page' => $profiles->lastPage(),
            'total'     => $profiles->total(),
        ]);
    }
}

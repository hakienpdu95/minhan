<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Models\CareerPathwayStep;
use Modules\Assessment\Models\WorkforceCertification;
use Modules\Assessment\Models\WorkforcePortfolio;
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
            ? WorkforceCertification::withoutTenant()
                ->where('workforce_profile_id', $profile->id)
                ->with('definition')
                ->orderByDesc('issued_at')
                ->get()
            : collect();

        $recentHistory = $profile
            ? WorkforceProfileHistory::where('workforce_profile_id', $profile->id)
                ->orderByDesc('recorded_at')
                ->limit(8)
                ->get()
            : collect();

        $sandboxStats = $profile
            ? SandboxSession::withoutTenant()
                ->where('workforce_profile_id', $profile->id)
                ->where('status', 'completed')
                ->selectRaw('COUNT(*) as total, SUM(duration_minutes) as minutes, AVG(final_score) as avg_score')
                ->first()
            : null;

        $portfolios = $profile
            ? WorkforcePortfolio::withoutTenant()
                ->where('workforce_profile_id', $profile->id)
                ->orderBy('sort_order')
                ->get()
            : collect();

        $currentPathwayStep = $profile
            ? CareerPathwayStep::whereNull('organization_id')
                ->where('from_level', $profile->tdwcf_maturity_level)
                ->first()
            : null;

        $allPathwaySteps = CareerPathwayStep::whereNull('organization_id')
            ->orderBy('step_order')
            ->get();

        // Profile completeness — 5 dimensions × 20% each
        $completeness = 0;
        if ($profile) {
            if ($profile->tdwcf_score)          $completeness += 20;
            if ($profile->certifications_count) $completeness += 20;
            if ($profile->sandbox_sessions_total) $completeness += 20;
            if ($profile->impact_score)          $completeness += 20;
            if ($profile->kpi_achievement_avg)   $completeness += 20;

            if ($profile->profile_completeness_pct !== $completeness) {
                $profile->updateQuietly(['profile_completeness_pct' => $completeness]);
                $profile->profile_completeness_pct = $completeness;
            }
        }

        // Trust score breakdown for display
        $trustBreakdown = $profile ? $this->buildTrustBreakdown($profile) : [];

        return view('assessment::workforce.me', compact(
            'user', 'employee', 'profile', 'certifications',
            'recentHistory', 'sandboxStats', 'currentPathwayStep', 'allPathwaySteps',
            'portfolios', 'completeness', 'trustBreakdown'
        ));
    }

    /** Cập nhật career goal + learning path */
    public function updateGoal(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $orgId = TenantContext::getOrganizationId();

        $data = $request->validate([
            'career_goal'          => 'nullable|string|max:500',
            'current_learning_path' => 'nullable|string|max:300',
        ]);

        $profile = WorkforceProfile::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('user_id', $user->id)
            ->first();

        if ($profile) {
            $profile->update($data);
        }

        return back()->with('success', 'Đã cập nhật mục tiêu nghề nghiệp.');
    }

    private function buildTrustBreakdown(WorkforceProfile $profile): array
    {
        $certScore = match ($profile->highest_cert_level) {
            'LEADER'       => 100.0,
            'PROFESSIONAL' => 75.0,
            'PRACTITIONER' => 50.0,
            'FOUNDATION'   => 25.0,
            default        => 0.0,
        };

        return [
            ['label' => 'TDWCF Score',  'weight' => 30, 'raw' => $profile->tdwcf_score ?? 0,        'contribution' => round(($profile->tdwcf_score ?? 0) * 0.30, 1)],
            ['label' => 'Chứng nhận',   'weight' => 25, 'raw' => $certScore,                         'contribution' => round($certScore * 0.25, 1)],
            ['label' => 'KPI',          'weight' => 20, 'raw' => $profile->kpi_achievement_avg ?? 0, 'contribution' => round(($profile->kpi_achievement_avg ?? 0) * 0.20, 1)],
            ['label' => 'Sandbox',      'weight' => 15, 'raw' => $profile->sandbox_score_avg ?? 0,   'contribution' => round(($profile->sandbox_score_avg ?? 0) * 0.15, 1)],
            ['label' => 'Portfolio',    'weight' => 10, 'raw' => 0,                                   'contribution' => 0.0],
        ];
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

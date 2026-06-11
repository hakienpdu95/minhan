<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Assessment\Enums\ImpactCategory;
use Modules\Employee\Models\Employee;
use Modules\KpiGoal\Models\AiImpactSnapshot;

class AiImpactController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('assessment.results');

        $snapshots = AiImpactSnapshot::orderByDesc('period_start')
            ->with('employee:id,full_name,position')
            ->paginate(20);

        $stats = [
            'avgImprovement' => AiImpactSnapshot::whereNotNull('improvement_pct')->avg('improvement_pct'),
            'avgRoi'         => AiImpactSnapshot::whereNotNull('roi_pct')->where('roi_pct', '>', 0)->avg('roi_pct'),
            'thisWeek'       => AiImpactSnapshot::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('assessment::ai-impact.index', compact('snapshots', 'stats'));
    }

    public function create(Request $request): View
    {
        $this->authorize('assessment.results');

        $categories = ImpactCategory::cases();
        $employees  = Employee::orderBy('full_name')->get(['id', 'full_name']);

        return view('assessment::ai-impact.create', compact('categories', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('assessment.results');

        $data = $request->validate([
            'impact_category' => 'required|string',
            'impact_type'     => 'required|string|max:100',
            'period_start'    => 'required|date',
            'period_end'      => 'required|date|after_or_equal:period_start',
            'baseline_value'  => 'required|numeric|min:0',
            'achieved_value'  => 'required|numeric|min:0',
            'investment_cost' => 'nullable|numeric|min:0',
            'benefit_value'   => 'nullable|numeric|min:0',
            'employee_id'     => 'nullable|exists:employees,id',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $data['organization_id'] = TenantContext::getOrganizationId();
        $data['created_by']      = $request->user()->id;

        AiImpactSnapshot::create($data);

        return redirect()->route('backend.ai-impact.index')
            ->with('success', 'Đã lưu chỉ số tác động AI thành công.');
    }
}

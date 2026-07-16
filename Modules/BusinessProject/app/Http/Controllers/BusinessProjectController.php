<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\StageGate\AdvanceBusinessProjectStageAction;
use Modules\BusinessProject\Exceptions\GateViolationException;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;

class BusinessProjectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BusinessProject::class, 'businessProject');
    }

    public function index(): View
    {
        $projects = BusinessProject::with(['customer', 'members'])
            ->latest()
            ->paginate(20);

        return view('businessproject::business-projects.index', compact('projects'));
    }

    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $businessProject->load(['customer', 'context.deliverable.versions', 'members.user']);

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.show', [
            'businessProject' => $businessProject,
            'gateResult' => $gateResult,
        ]);
    }

    public function advanceStage(BusinessProject $businessProject): RedirectResponse
    {
        $this->authorize('update', $businessProject);

        try {
            AdvanceBusinessProjectStageAction::run($businessProject);
        } catch (GateViolationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã chuyển sang giai đoạn tiếp theo.');
    }
}

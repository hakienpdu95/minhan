<?php

namespace Modules\Deployment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Deployment\Actions\ResolveDeploymentIssueAction;
use Modules\Deployment\Actions\StoreDeploymentIssueAction;
use Modules\Deployment\Actions\UpdateDeploymentIssueAction;
use Modules\Deployment\Data\StoreDeploymentIssueData;
use Modules\Deployment\Data\UpdateDeploymentIssueData;
use Modules\Deployment\Enums\IssueSeverity;
use Modules\Deployment\Enums\IssueStatus;
use Modules\Deployment\Models\DeploymentIssue;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Deployment\Queries\ListDeploymentIssuesHandler;
use Modules\Deployment\Queries\ListDeploymentIssuesQuery;

class DeploymentIssueController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DeploymentIssue::class);

        $vertical = $request->attributes->get('_vertical');

        $targetIds = DeploymentTarget::where('vertical_code', $vertical->code())->pluck('id');

        $query = new ListDeploymentIssuesQuery(
            target_id:  $request->integer('target_id') ?: null,
            project_id: $request->integer('project_id') ?: null,
            severity:   $request->input('severity'),
            status:     $request->input('status'),
        );

        $issues    = (new ListDeploymentIssuesHandler)->handle($query)
            ->whereIn('deployment_target_id', $targetIds)
            ->paginate(25)->withQueryString();

        $severities = IssueSeverity::cases();
        $statuses   = IssueStatus::cases();
        $targets    = DeploymentTarget::where('vertical_code', $vertical->code())
            ->with('targetOrganization')->get();

        return view('deployment::issues.index', compact('vertical', 'issues', 'severities', 'statuses', 'targets'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', DeploymentIssue::class);

        $vertical = $request->attributes->get('_vertical');
        $targets  = DeploymentTarget::where('vertical_code', $vertical->code())
            ->with('targetOrganization')->get();
        $severities = IssueSeverity::cases();

        return view('deployment::issues.create', compact('vertical', 'targets', 'severities'));
    }

    public function store(Request $request, StoreDeploymentIssueAction $action): RedirectResponse
    {
        $this->authorize('create', DeploymentIssue::class);

        $vertical = $request->attributes->get('_vertical');
        $data     = StoreDeploymentIssueData::validateAndCreate($request->all());
        $issue    = $action->handle($data);

        return redirect()
            ->route('deployment.issues.index', ['vertical' => $vertical->code()])
            ->with('success', 'Đã tạo issue thành công.');
    }

    public function show(Request $request, DeploymentIssue $issue): View
    {
        $this->authorize('view', $issue);

        $vertical = $request->attributes->get('_vertical');
        $issue->load(['target.targetOrganization', 'owner', 'createdBy']);

        return view('deployment::issues.show', compact('vertical', 'issue'));
    }

    public function update(Request $request, DeploymentIssue $issue, UpdateDeploymentIssueAction $action): RedirectResponse
    {
        $this->authorize('update', $issue);

        $data = UpdateDeploymentIssueData::validateAndCreate($request->all());
        $action->handle($issue, $data);

        return back()->with('success', 'Đã cập nhật issue.');
    }

    public function resolve(Request $request, DeploymentIssue $issue, ResolveDeploymentIssueAction $action): RedirectResponse
    {
        $this->authorize('resolve', $issue);

        try {
            $action->handle($issue);
            return back()->with('success', 'Đã đánh dấu issue đã giải quyết.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['resolve' => $e->getMessage()]);
        }
    }
}

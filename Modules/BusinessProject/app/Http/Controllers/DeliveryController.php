<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Delivery\ApproveChangeRequestAction;
use Modules\BusinessProject\Actions\Delivery\AttachTaskToProjectAction;
use Modules\BusinessProject\Actions\Delivery\CreateWeeklyReportAction;
use Modules\BusinessProject\Actions\Delivery\EscalateToChangeRequestAction;
use Modules\BusinessProject\Actions\Delivery\RecordIssueAction;
use Modules\BusinessProject\Actions\Delivery\RecordMeetingAction;
use Modules\BusinessProject\Actions\Delivery\RecordRiskAction;
use Modules\BusinessProject\Actions\Delivery\RejectChangeRequestAction;
use Modules\BusinessProject\Actions\Delivery\SaveMeetingMinutesAction;
use Modules\BusinessProject\Actions\Delivery\SubmitChangeRequestForApprovalAction;
use Modules\BusinessProject\Data\Requests\AttachTaskData;
use Modules\BusinessProject\Data\Requests\SaveMeetingMinutesData;
use Modules\BusinessProject\Data\Requests\StoreChangeRequestData;
use Modules\BusinessProject\Data\Requests\StoreIssueData;
use Modules\BusinessProject\Data\Requests\StoreMeetingData;
use Modules\BusinessProject\Data\Requests\StoreRiskData;
use Modules\BusinessProject\Data\Requests\StoreWeeklyReportData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\ChangeRequest;
use Modules\BusinessProject\Models\Issue;
use Modules\BusinessProject\Models\Meeting;
use Modules\BusinessProject\Models\Risk;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Modules\Task\Models\Task;

class DeliveryController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $tasks = $businessProject->tasks()->with('employee')->orderByDesc('id')->get();

        $attachableTasks = Task::where('organization_id', $businessProject->organization_id)
            ->whereNull('business_project_id')
            ->where('is_archived', false)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title']);

        $meetings = $businessProject->meetings()->with('deliverable.versions')->orderByDesc('held_at')->get();

        $weeklyReports = $businessProject->deliverables()
            ->where('type', DeliverableType::WeeklyReport->value)
            ->with('versions')
            ->orderByDesc('created_at')
            ->get();

        $issues = $businessProject->issues()->orderByDesc('created_at')->get();
        $risks = $businessProject->risks()->orderByDesc('created_at')->get();
        $changeRequests = $businessProject->changeRequests()->with(['issue', 'risk'])->orderByDesc('created_at')->get();

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.delivery.show', [
            'businessProject' => $businessProject,
            'tasks' => $tasks,
            'attachableTasks' => $attachableTasks,
            'meetings' => $meetings,
            'weeklyReports' => $weeklyReports,
            'issues' => $issues,
            'risks' => $risks,
            'changeRequests' => $changeRequests,
            'gateResult' => $gateResult,
        ]);
    }

    public function storeMeeting(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreMeetingData::validateAndCreate($request->all());
        RecordMeetingAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã ghi nhận Meeting.');
    }

    public function saveMeetingMinutes(BusinessProject $businessProject, Meeting $meeting, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = SaveMeetingMinutesData::validateAndCreate($request->all());
        SaveMeetingMinutesAction::run($meeting, $data);

        return $this->back($businessProject, 'Đã lưu Meeting Minutes.');
    }

    public function addWeeklyReport(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreWeeklyReportData::validateAndCreate($request->all());
        CreateWeeklyReportAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã tạo Weekly Report.');
    }

    public function storeIssue(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreIssueData::validateAndCreate($request->all());
        RecordIssueAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã ghi nhận Issue.');
    }

    public function storeRisk(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreRiskData::validateAndCreate($request->all());
        RecordRiskAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã ghi nhận Risk.');
    }

    public function escalateIssue(BusinessProject $businessProject, Issue $issue, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreChangeRequestData::validateAndCreate($request->all());
        EscalateToChangeRequestAction::run($issue, $data);

        return $this->back($businessProject, 'Đã escalate Issue thành Change Request.');
    }

    public function escalateRisk(BusinessProject $businessProject, Risk $risk, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = StoreChangeRequestData::validateAndCreate($request->all());
        EscalateToChangeRequestAction::run($risk, $data);

        return $this->back($businessProject, 'Đã escalate Risk thành Change Request.');
    }

    public function submitChangeRequest(BusinessProject $businessProject, ChangeRequest $changeRequest): RedirectResponse
    {
        $this->authorize('manage', $changeRequest);

        SubmitChangeRequestForApprovalAction::run($changeRequest);

        return $this->back($businessProject, 'Đã gửi Change Request để phê duyệt.');
    }

    public function approveChangeRequest(BusinessProject $businessProject, ChangeRequest $changeRequest, Request $request): RedirectResponse
    {
        ApproveChangeRequestAction::run($changeRequest, $request->input('comment'));

        return $this->back($businessProject, 'Đã duyệt Change Request.');
    }

    public function rejectChangeRequest(BusinessProject $businessProject, ChangeRequest $changeRequest, Request $request): RedirectResponse
    {
        RejectChangeRequestAction::run($changeRequest, $request->input('comment'));

        return $this->back($businessProject, 'Đã từ chối Change Request.');
    }

    public function attachTask(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDelivery', $businessProject);

        $data = AttachTaskData::validateAndCreate($request->all());
        AttachTaskToProjectAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã gắn Task vào Business Project.');
    }

    private function back(BusinessProject $businessProject, string $message): RedirectResponse
    {
        return redirect()
            ->route('backend.business-projects.delivery.show', $businessProject)
            ->with('success', $message);
    }
}

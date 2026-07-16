<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Closing\AttachKnowledgeAssetAction;
use Modules\BusinessProject\Actions\Closing\SaveFinalReportAction;
use Modules\BusinessProject\Actions\Deliverable\ApproveDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\RejectDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\SubmitDeliverableForApprovalAction;
use Modules\BusinessProject\Data\Requests\AttachKnowledgeAssetData;
use Modules\BusinessProject\Data\Requests\StoreFinalReportData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Modules\KcItem\Models\KcItem;

class ClosingController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $finalReport = $businessProject->deliverables()
            ->where('type', DeliverableType::FinalReport->value)
            ->whereNull('parent_id')
            ->with('versions')
            ->first();

        $knowledgeAssets = $businessProject->kcItems()->with('category')->orderByDesc('id')->get();

        $attachableKcItems = KcItem::where('organization_id', $businessProject->organization_id)
            ->whereNull('business_project_id')
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title']);

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.closing.show', [
            'businessProject' => $businessProject,
            'finalReport' => $finalReport,
            'knowledgeAssets' => $knowledgeAssets,
            'attachableKcItems' => $attachableKcItems,
            'gateResult' => $gateResult,
        ]);
    }

    public function saveFinalReport(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageClosing', $businessProject);

        $data = StoreFinalReportData::validateAndCreate($request->all());
        SaveFinalReportAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Final Project Report.');
    }

    public function submitFinalReport(BusinessProject $businessProject): RedirectResponse
    {
        $deliverable = $this->findFinalReport($businessProject);

        $this->authorize('manage', $deliverable);

        SubmitDeliverableForApprovalAction::run($deliverable);

        return $this->back($businessProject, 'Đã gửi Final Project Report để phê duyệt.');
    }

    public function approveFinalReport(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $this->findFinalReport($businessProject);

        ApproveDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, 'Đã duyệt Final Project Report.');
    }

    public function rejectFinalReport(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $this->findFinalReport($businessProject);

        RejectDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, 'Đã từ chối Final Project Report.');
    }

    public function attachKnowledgeAsset(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageClosing', $businessProject);

        $data = AttachKnowledgeAssetData::validateAndCreate($request->all());
        AttachKnowledgeAssetAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã gắn Knowledge Asset vào Business Project.');
    }

    private function findFinalReport(BusinessProject $businessProject)
    {
        return $businessProject->deliverables()
            ->where('type', DeliverableType::FinalReport->value)
            ->whereNull('parent_id')
            ->firstOrFail();
    }

    private function back(BusinessProject $businessProject, string $message): RedirectResponse
    {
        return redirect()
            ->route('backend.business-projects.closing.show', $businessProject)
            ->with('success', $message);
    }
}

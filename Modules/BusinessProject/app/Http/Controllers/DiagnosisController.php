<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Deliverable\ApproveDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\RejectDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\SubmitDeliverableForApprovalAction;
use Modules\BusinessProject\Actions\Diagnosis\AddDiagnosisFindingAction;
use Modules\BusinessProject\Actions\Diagnosis\AttachEvidenceToDiagnosisAction;
use Modules\BusinessProject\Actions\Diagnosis\RemoveDiagnosisFindingAction;
use Modules\BusinessProject\Actions\Diagnosis\SaveDiagnosisOverviewAction;
use Modules\BusinessProject\Data\Requests\AttachDiagnosisEvidenceData;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisFindingData;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisOverviewData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Enums\DiagnosisCategory;
use Modules\BusinessProject\Enums\DiagnosisEffort;
use Modules\BusinessProject\Enums\DiagnosisImpact;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;

class DiagnosisController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $diagnosisReport = $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->with(['versions', 'evidenceFor'])
            ->first();

        // Evidence khả dụng: mọi Deliverable của Discovery Workspace trong project này (Interview/
        // Observation/Document Review/Data Review/Process Map + Business Discovery Report) — đúng
        // spec Giai đoạn 3 "trích từ Deliverable Discovery".
        $evidenceCandidates = $businessProject->deliverables()
            ->whereIn('type', array_map(
                fn ($t) => $t->value,
                [...DeliverableType::discoveryRecordTypes(), DeliverableType::BusinessDiscoveryReport]
            ))
            ->orderByDesc('id')
            ->get(['id', 'title', 'type']);

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.diagnosis.show', [
            'businessProject' => $businessProject,
            'diagnosisReport' => $diagnosisReport,
            'evidenceCandidates' => $evidenceCandidates,
            'categories' => DiagnosisCategory::cases(),
            'impacts' => DiagnosisImpact::cases(),
            'efforts' => DiagnosisEffort::cases(),
            'gateResult' => $gateResult,
        ]);
    }

    public function saveOverview(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiagnosis', $businessProject);

        $data = StoreDiagnosisOverviewData::validateAndCreate($request->all());
        SaveDiagnosisOverviewAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Diagnosis Report overview.');
    }

    public function addFinding(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiagnosis', $businessProject);

        $data = StoreDiagnosisFindingData::validateAndCreate($request->all());
        AddDiagnosisFindingAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã thêm finding vào Diagnosis Matrix.');
    }

    public function removeFinding(BusinessProject $businessProject, int $index): RedirectResponse
    {
        $this->authorize('manageDiagnosis', $businessProject);

        RemoveDiagnosisFindingAction::run($businessProject, $index);

        return $this->back($businessProject, 'Đã xóa finding khỏi Diagnosis Matrix.');
    }

    public function attachEvidence(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiagnosis', $businessProject);

        $data = AttachDiagnosisEvidenceData::validateAndCreate($request->all());
        AttachEvidenceToDiagnosisAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã đính evidence vào Diagnosis Report.');
    }

    public function submit(BusinessProject $businessProject): RedirectResponse
    {
        $deliverable = $this->findDiagnosisReport($businessProject);

        $this->authorize('manage', $deliverable);

        SubmitDeliverableForApprovalAction::run($deliverable);

        return $this->back($businessProject, 'Đã gửi Diagnosis Report để phê duyệt.');
    }

    public function approve(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $this->findDiagnosisReport($businessProject);

        ApproveDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, 'Đã duyệt Diagnosis Report.');
    }

    public function reject(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $this->findDiagnosisReport($businessProject);

        RejectDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, 'Đã từ chối Diagnosis Report.');
    }

    private function findDiagnosisReport(BusinessProject $businessProject)
    {
        return $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->firstOrFail();
    }

    private function back(BusinessProject $businessProject, string $message): RedirectResponse
    {
        return redirect()
            ->route('backend.business-projects.diagnosis.show', $businessProject)
            ->with('success', $message);
    }
}

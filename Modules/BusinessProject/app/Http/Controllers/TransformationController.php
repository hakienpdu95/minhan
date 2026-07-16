<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Deliverable\ApproveDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\ConfirmDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\RejectDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\SubmitDeliverableForApprovalAction;
use Modules\BusinessProject\Actions\Transformation\AddMilestoneAction;
use Modules\BusinessProject\Actions\Transformation\SaveProposalAction;
use Modules\BusinessProject\Actions\Transformation\SaveSowAction;
use Modules\BusinessProject\Actions\Transformation\SaveTransformationDesignCanvasAction;
use Modules\BusinessProject\Actions\Transformation\SaveTransformationRoadmapAction;
use Modules\BusinessProject\Data\Requests\StoreMilestoneData;
use Modules\BusinessProject\Data\Requests\StoreProposalData;
use Modules\BusinessProject\Data\Requests\StoreSowData;
use Modules\BusinessProject\Data\Requests\StoreTransformationDesignCanvasData;
use Modules\BusinessProject\Data\Requests\StoreTransformationRoadmapData;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Enums\MilestoneCategory;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TransformationController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $canvas = $this->findSingleton($businessProject, DeliverableType::TransformationDesignCanvas);
        $roadmap = $this->findSingleton($businessProject, DeliverableType::TransformationRoadmap);
        $proposal = $this->findSingleton($businessProject, DeliverableType::Proposal);
        $sow = $this->findSingleton($businessProject, DeliverableType::Sow);

        $milestonesByCategory = $businessProject->milestones()
            ->orderBy('target_date')
            ->get()
            ->groupBy(fn ($m) => $m->category->value);

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.transformation.show', [
            'businessProject' => $businessProject,
            'canvas' => $canvas,
            'roadmap' => $roadmap,
            'proposal' => $proposal,
            'sow' => $sow,
            'milestonesByCategory' => $milestonesByCategory,
            'milestoneCategories' => MilestoneCategory::ordered(),
            'gateResult' => $gateResult,
        ]);
    }

    public function saveCanvas(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageTransformation', $businessProject);

        $data = StoreTransformationDesignCanvasData::validateAndCreate($request->all());
        SaveTransformationDesignCanvasAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Transformation Design Canvas.');
    }

    public function saveRoadmap(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageTransformation', $businessProject);

        $data = StoreTransformationRoadmapData::validateAndCreate($request->all());
        SaveTransformationRoadmapAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Transformation Roadmap.');
    }

    public function storeMilestone(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageTransformation', $businessProject);

        $data = StoreMilestoneData::validateAndCreate($request->all());
        AddMilestoneAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã thêm mốc lộ trình.');
    }

    public function saveProposal(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageTransformation', $businessProject);

        $data = StoreProposalData::validateAndCreate($request->all());
        SaveProposalAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Proposal.');
    }

    public function saveSow(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageTransformation', $businessProject);

        $data = StoreSowData::validateAndCreate($request->all());
        SaveSowAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã lưu Statement of Work.');
    }

    public function submit(BusinessProject $businessProject, string $type): RedirectResponse
    {
        $deliverable = $this->resolveDeliverable($businessProject, $type);

        $this->authorize('manage', $deliverable);
        $this->assertDiagnosisApprovedForPublish($businessProject);

        SubmitDeliverableForApprovalAction::run($deliverable);

        return $this->back($businessProject, $deliverable->title.' đã gửi phê duyệt nội bộ.');
    }

    public function approve(BusinessProject $businessProject, string $type, Request $request): RedirectResponse
    {
        $deliverable = $this->resolveDeliverable($businessProject, $type);

        ApproveDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, $deliverable->title.' đã được duyệt nội bộ.');
    }

    public function reject(BusinessProject $businessProject, string $type, Request $request): RedirectResponse
    {
        $deliverable = $this->resolveDeliverable($businessProject, $type);

        RejectDeliverableAction::run($deliverable, $request->input('comment'));

        return $this->back($businessProject, $deliverable->title.' đã bị từ chối.');
    }

    public function confirm(BusinessProject $businessProject, string $type): RedirectResponse
    {
        $deliverable = $this->resolveDeliverable($businessProject, $type);

        $this->authorize('manage', $deliverable);

        ConfirmDeliverableAction::run($deliverable);

        return $this->back($businessProject, $deliverable->title.' đã được xác nhận (Confirmed).');
    }

    /**
     * Spec Giai đoạn 3 — "Tách xem trước và kích hoạt": khi Diagnosis chưa duyệt, Consultant vẫn
     * soạn được nháp Proposal/SOW (saveProposal/saveSow không bị chặn) nhưng KHÔNG được "Gửi phê
     * duyệt nội bộ" (= publish chính thức) — quyền publish khóa theo trạng thái gate R3, không
     * khóa quyền soạn thảo. Chỉ áp dụng khi flag `stage_gates.diagnosis.enforced` bật (Phase 2).
     */
    private function assertDiagnosisApprovedForPublish(BusinessProject $businessProject): void
    {
        if (! config('businessproject.stage_gates.diagnosis.enforced', false)) {
            return;
        }

        $diagnosisReport = $this->findSingleton($businessProject, DeliverableType::DiagnosisReport);

        if ($diagnosisReport?->status?->value !== DeliverableStatus::Approved->value) {
            throw new HttpException(422, 'Diagnosis Report chưa được phê duyệt (Rule R3) — chưa thể publish Proposal/SOW, chỉ soạn nháp được.');
        }
    }

    private function resolveDeliverable(BusinessProject $businessProject, string $type): Deliverable
    {
        $deliverableType = match ($type) {
            'proposal' => DeliverableType::Proposal,
            'sow' => DeliverableType::Sow,
            default => abort(404),
        };

        return $this->findSingleton($businessProject, $deliverableType) ?? abort(404);
    }

    private function findSingleton(BusinessProject $businessProject, DeliverableType $type): ?Deliverable
    {
        return $businessProject->deliverables()
            ->where('type', $type->value)
            ->whereNull('parent_id')
            ->with('versions')
            ->first();
    }

    private function back(BusinessProject $businessProject, string $message): RedirectResponse
    {
        return redirect()
            ->route('backend.business-projects.transformation.show', $businessProject)
            ->with('success', $message);
    }
}

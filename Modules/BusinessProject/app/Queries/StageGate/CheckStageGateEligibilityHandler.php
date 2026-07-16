<?php

namespace Modules\BusinessProject\Queries\StageGate;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Modules\BusinessProject\Data\StageGateConditionData;
use Modules\BusinessProject\Data\StageGateResultData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;

/**
 * Nơi DUY NHẤT quyết định "đủ điều kiện chuyển giai đoạn hay chưa" (Rule R1-R7).
 * Phải có đủ 8 nhánh match() ngay từ Vertical Slice 1 (spec Phần 9 — bypass Diagnosis
 * ở Phase 1 KHÔNG được code cứng bỏ qua state, chỉ được tắt qua điều kiện chưa triển khai
 * ở đây, để Phase 2 chỉ cần thay nội dung 1 case, không đổi cấu trúc).
 */
class CheckStageGateEligibilityHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): StageGateResultData
    {
        /** @var CheckStageGateEligibilityQuery $query */
        $project = $query->businessProject;
        $stage = $project->current_stage instanceof BusinessProjectStage
            ? $project->current_stage
            : BusinessProjectStage::from($project->current_stage);

        $conditions = match ($stage) {
            BusinessProjectStage::Context => $this->contextConditions($project),
            BusinessProjectStage::Discovery => $this->discoveryConditions($project),
            BusinessProjectStage::Diagnosis => $this->diagnosisConditions($project),
            BusinessProjectStage::Transformation => $this->transformationConditions($project),
            BusinessProjectStage::Delivery => $this->deliveryConditions(),
            BusinessProjectStage::Closing => $this->closingConditions($project),
            BusinessProjectStage::Knowledge => $this->knowledgeConditions(),
            BusinessProjectStage::CustomerSuccess => $this->customerSuccessConditions(),
            default => $this->notImplementedConditions(),
        };

        $canAdvance = collect($conditions)->every(fn (StageGateConditionData $c) => $c->met);

        return new StageGateResultData(
            stage: $stage->value,
            nextStage: $stage->next()?->value,
            canAdvance: $canAdvance,
            conditions: $conditions,
        );
    }

    /**
     * Rule R1/R2 tại gate Context -> Discovery: phải có Business Context VÀ
     * Deliverable Context Report đã được duyệt (approved) qua Approval Service.
     *
     * @return StageGateConditionData[]
     */
    private function contextConditions($project): array
    {
        $context = $project->context;

        $contextReport = $context?->deliverable_id
            ? $project->deliverables()
                ->where('id', $context->deliverable_id)
                ->where('type', DeliverableType::BusinessContextReport->value)
                ->first()
            : null;

        return [
            new StageGateConditionData(
                label: 'Đã nhập Business Context (Company Profile, Stakeholder, Strategic Goals)',
                met: $context !== null,
            ),
            new StageGateConditionData(
                label: 'Business Context Report đã được phê duyệt',
                met: $contextReport !== null
                    && $contextReport->status?->value === DeliverableStatus::Approved->value,
            ),
        ];
    }

    /**
     * Gate R2 tại Discovery -> Diagnosis (spec Giai đoạn 2 + thaotac.md): checklist "(a) có
     * Business Discovery Report, (b) TPS Canvas đã điền đủ" — không yêu cầu approval (Ma trận
     * Phần 4: cột Approval Service ở Discovery là "—", khác Context/Diagnosis).
     *
     * @return StageGateConditionData[]
     */
    private function discoveryConditions($project): array
    {
        $report = $project->deliverables()
            ->where('type', DeliverableType::BusinessDiscoveryReport->value)
            ->whereNull('parent_id')
            ->first();

        $tpsCanvas = $project->deliverables()
            ->where('type', DeliverableType::TpsCanvas->value)
            ->whereNull('parent_id')
            ->first();

        $tpsCanvasContent = ($tpsCanvas !== null && $tpsCanvas->current_version > 0)
            ? ($tpsCanvas->versions()->first()?->content ?? [])
            : [];

        $tpsCanvasFilled = filled($tpsCanvasContent['problem'] ?? null)
            && filled($tpsCanvasContent['goal'] ?? null)
            && filled($tpsCanvasContent['scope'] ?? null);

        return [
            new StageGateConditionData(
                label: 'Đã có Business Discovery Report',
                met: $report !== null && $report->current_version >= 1,
            ),
            new StageGateConditionData(
                label: 'TPS Canvas đã điền đủ (Vấn đề, Mục tiêu, Phạm vi)',
                met: $tpsCanvasFilled,
            ),
        ];
    }

    /**
     * Gate R3 tại Diagnosis -> Transformation (spec Giai đoạn 3): "Gate sang Transformation:
     * Diagnosis Report approved" — duy nhất 1 điều kiện, dùng nguyên Approval Service (Ringlesoft)
     * như Context/Proposal/SOW/Final Report, không tự chế. Feature flag
     * `businessproject.stage_gates.diagnosis.enforced` (Phần 9 — "Bypass Diagnosis ở Phase 1") giờ
     * bật `true` ở Phase 2 — giữ lại nhánh bypass (dead nếu flag true) để rollback an toàn nếu cần,
     * không xóa để tránh phải đổi lại cấu trúc match() nếu có sự cố.
     *
     * @return StageGateConditionData[]
     */
    private function diagnosisConditions($project): array
    {
        $enforced = config('businessproject.stage_gates.diagnosis.enforced', false);

        if (! $enforced) {
            return [
                new StageGateConditionData(
                    label: 'Diagnosis Workspace: bypass (feature flag tắt) — không phải hành vi Phase 2 mặc định',
                    met: true,
                ),
            ];
        }

        $diagnosisReport = $project->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->first();

        return [
            new StageGateConditionData(
                label: 'Diagnosis Report đã được phê duyệt (Rule R3)',
                met: $diagnosisReport?->status?->value === DeliverableStatus::Approved->value,
            ),
        ];
    }

    /**
     * Gate R4 tại Transformation -> Delivery (spec Giai đoạn 4): cả Proposal VÀ SOW cùng
     * `confirmed` (Consultant/PM tick sau khi khách ký duyệt ngoài hệ thống) — thiếu 1 trong 2
     * thì chặn, không yêu cầu Transformation Design Canvas/Roadmap (không nằm trong điều kiện
     * gate, chỉ là bước chuẩn bị trước Proposal/SOW).
     *
     * @return StageGateConditionData[]
     */
    private function transformationConditions($project): array
    {
        $proposal = $project->deliverables()
            ->where('type', DeliverableType::Proposal->value)
            ->whereNull('parent_id')
            ->first();

        $sow = $project->deliverables()
            ->where('type', DeliverableType::Sow->value)
            ->whereNull('parent_id')
            ->first();

        return [
            new StageGateConditionData(
                label: 'Proposal đã confirmed (khách đã ký duyệt)',
                met: $proposal?->status?->value === DeliverableStatus::Confirmed->value,
            ),
            new StageGateConditionData(
                label: 'SOW đã confirmed (khách đã ký duyệt)',
                met: $sow?->status?->value === DeliverableStatus::Confirmed->value,
            ),
        ];
    }

    /**
     * Gate tại Delivery -> Closing. Rule R5 ("Weekly Report luôn gắn Project") KHÔNG phải điều
     * kiện chuyển giai đoạn (khác R1/R2/R4/R6/R7 — xem Phần 5 spec, R5 không có dòng "Gate sang
     * X") — chỉ là rule toàn vẹn dữ liệu, enforce ở tầng tạo Weekly Report. Vì vậy Delivery luôn
     * cho advance tự do sang Closing, không rơi vào `notImplementedConditions()` (nếu không sẽ
     * kẹt project ở Delivery vĩnh viễn — bug thật đã tự phát hiện khi verify Closing Workspace).
     *
     * @return StageGateConditionData[]
     */
    private function deliveryConditions(): array
    {
        return [
            new StageGateConditionData(
                label: 'Không có điều kiện gate bắt buộc (Rule R5 không phải stage gate)',
                met: true,
            ),
        ];
    }

    /**
     * Gate R6/R7 tại Closing -> Knowledge (spec Giai đoạn 6) — rào cản cứng có chủ đích, biến
     * việc viết tri thức thành điều kiện đóng dự án: (R6) Final Project Report đã approved,
     * (R7) ≥1 Knowledge Asset (KcItem) gắn business_project_id. Advance ra khỏi Closing chính là
     * "Đóng dự án" (bắn event BusinessProjectClosed — xem AdvanceBusinessProjectStageAction).
     *
     * @return StageGateConditionData[]
     */
    private function closingConditions($project): array
    {
        $finalReport = $project->deliverables()
            ->where('type', DeliverableType::FinalReport->value)
            ->whereNull('parent_id')
            ->first();

        $knowledgeAssetCount = $project->kcItems()->count();

        return [
            new StageGateConditionData(
                label: 'Final Project Report đã được phê duyệt',
                met: $finalReport?->status?->value === DeliverableStatus::Approved->value,
            ),
            new StageGateConditionData(
                label: '≥ 1 Knowledge Asset (Case Study/Lessons Learned...) gắn với dự án',
                met: $knowledgeAssetCount >= 1,
            ),
        ];
    }

    /**
     * Gate tại Knowledge -> Customer Success (spec Giai đoạn 7): không có rule R-nào yêu cầu
     * điều kiện ở đây (Handbook chỉ định nghĩa Rule R1-R7, dừng ở Closing/Knowledge) — cùng lý do
     * `deliveryConditions()` luôn cho advance tự do, KHÔNG rơi vào `notImplementedConditions()`.
     *
     * @return StageGateConditionData[]
     */
    private function knowledgeConditions(): array
    {
        return [
            new StageGateConditionData(
                label: 'Không có điều kiện gate bắt buộc (Knowledge Workspace không phải stage gate)',
                met: true,
            ),
        ];
    }

    /**
     * Customer Success là stage CUỐI (`BusinessProjectStage::CustomerSuccess->next()` === null) —
     * "vòng đời không kết thúc ở Closed" (spec Giai đoạn 8): không có gate chuyển tiếp (gate-checklist
     * ẩn nút advance khi `nextStage` null), chỉ hiện 1 dòng giải thích thay vì rơi vào
     * `notImplementedConditions()` (dễ hiểu lầm là "chưa triển khai" trong khi workspace đã có thật).
     *
     * @return StageGateConditionData[]
     */
    private function customerSuccessConditions(): array
    {
        return [
            new StageGateConditionData(
                label: 'Customer Success là giai đoạn cuối — vòng đời dự án tiếp diễn qua CSAT/NPS, Follow-up, Renewal, tạo Lead mới (không có gate chuyển tiếp)',
                met: true,
            ),
        ];
    }

    /**
     * @return StageGateConditionData[]
     */
    private function notImplementedConditions(): array
    {
        return [
            new StageGateConditionData(
                label: 'Workspace chưa triển khai ở Phase này',
                met: false,
            ),
        ];
    }
}

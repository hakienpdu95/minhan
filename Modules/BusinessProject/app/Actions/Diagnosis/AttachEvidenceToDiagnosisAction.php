<?php

namespace Modules\BusinessProject\Actions\Diagnosis;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\AttachEvidenceAction;
use Modules\BusinessProject\Data\Requests\AttachDiagnosisEvidenceData;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Handbook 4.6: "Diagnosis Matrix là tài liệu trung tâm" — evidence trích dẫn ở CẤP REPORT
 * (deliverable_evidence_links.deliverable_id = Diagnosis Report), không phải per-finding (mảng
 * findings không phải deliverable riêng, xem spec Phần 6.2 + comment DeliverableType::DiagnosisReport).
 * Tái dùng nguyên AttachEvidenceAction đã tạo sẵn từ Vertical Slice 1 (Phần 6.2), không viết lại.
 */
class AttachEvidenceToDiagnosisAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, AttachDiagnosisEvidenceData $data): void
    {
        $diagnosisReport = $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->first();

        if ($diagnosisReport === null) {
            $diagnosisReport = Deliverable::create([
                'organization_id' => $businessProject->organization_id,
                'uuid' => Str::uuid(),
                'business_project_id' => $businessProject->id,
                'workspace' => DeliverableType::DiagnosisReport->workspace()->value,
                'type' => DeliverableType::DiagnosisReport->value,
                'title' => 'Diagnosis Report',
                'current_version' => 0,
                'status' => DeliverableStatus::Draft->value,
                'created_by' => Auth::id(),
            ]);
        }

        $evidence = Deliverable::where('business_project_id', $businessProject->id)
            ->findOrFail($data->evidence_deliverable_id);

        AttachEvidenceAction::run($diagnosisReport, $evidence, $data->evidence_type, $data->note);
    }
}

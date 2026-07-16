<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableVersion;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Deliverable Engine — upsert dùng chung cho MỌI deliverable "1 bản duy nhất mỗi project,
 * nhiều version" (Business Context Report, TPS Canvas, Business Discovery Report, và các
 * Canvas/Report tương tự ở Phase sau). Khác với Interview/Observation... (deliverable CON,
 * nhiều bản ghi qua AddDiscoveryRecordAction). Đúng nguyên tắc "1 Service — dùng mọi nơi"
 * (spec Phần 1) — không tự chế version logic riêng ở từng workspace.
 */
class UpsertSingletonDeliverableAction
{
    use AsAction;

    public function handle(
        BusinessProject $businessProject,
        DeliverableType $type,
        string $title,
        array $content,
        string $changeSummary,
        ?int $templateId = null,
    ): Deliverable {
        return DB::transaction(function () use ($businessProject, $type, $title, $content, $changeSummary, $templateId): Deliverable {
            $deliverable = $businessProject->deliverables()
                ->where('type', $type->value)
                ->whereNull('parent_id')
                ->first();

            if ($deliverable === null) {
                $deliverable = Deliverable::create([
                    'organization_id' => $businessProject->organization_id,
                    'uuid' => Str::uuid(),
                    'business_project_id' => $businessProject->id,
                    'workspace' => $type->workspace()->value,
                    'type' => $type->value,
                    'title' => $title,
                    // Template Library (Phase 2 mảng 5/5) — chỉ ghi nhận template lúc TẠO MỚI,
                    // không đổi lại nếu deliverable đã tồn tại (đây là nguồn gốc, không phải
                    // "lần cuối áp dụng").
                    'template_id' => $templateId,
                    'current_version' => 0,
                    'status' => DeliverableStatus::Draft->value,
                    'created_by' => Auth::id(),
                ]);
            } elseif ($deliverable->status?->value === DeliverableStatus::Confirmed->value) {
                // Rule R4 — Proposal/SOW đã confirmed (khách đã ký ngoài hệ thống) coi như chốt,
                // giống UpdateBusinessContextAction chặn sửa Context đã confirmed.
                throw new HttpException(422, $title.' đã confirmed, không thể sửa trực tiếp.');
            }

            $nextVersion = $deliverable->current_version + 1;

            DeliverableVersion::create([
                'deliverable_id' => $deliverable->id,
                'version_number' => $nextVersion,
                'content' => $content,
                'change_summary' => $changeSummary,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            $deliverable->update([
                'current_version' => $nextVersion,
                'status' => DeliverableStatus::Draft->value,
                'updated_by' => Auth::id(),
            ]);

            return $deliverable;
        });
    }
}

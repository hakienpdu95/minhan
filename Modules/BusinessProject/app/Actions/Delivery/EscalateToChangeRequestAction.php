<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreChangeRequestData;
use Modules\BusinessProject\Enums\ChangeRequestSourceType;
use Modules\BusinessProject\Models\ChangeRequest;
use Modules\BusinessProject\Models\Issue;
use Modules\BusinessProject\Models\Risk;

/**
 * Spec Giai đoạn 5: "Issue/Risk nghiêm trọng escalate thành Change Request". Nguồn ($source)
 * luôn là 1 trong 2: Issue hoặc Risk — chuyển trạng thái nguồn sang `escalated` cùng transaction
 * để tránh escalate trùng lặp (UI cũng ẩn nút Escalate khi status đã escalated).
 */
class EscalateToChangeRequestAction
{
    use AsAction;

    public function handle(Issue|Risk $source, StoreChangeRequestData $data): ChangeRequest
    {
        return DB::transaction(function () use ($source, $data): ChangeRequest {
            $isIssue = $source instanceof Issue;

            $changeRequest = ChangeRequest::create([
                'organization_id' => $source->organization_id,
                'uuid' => Str::uuid(),
                'business_project_id' => $source->business_project_id,
                'source_type' => $isIssue ? ChangeRequestSourceType::Issue->value : ChangeRequestSourceType::Risk->value,
                'issue_id' => $isIssue ? $source->id : null,
                'risk_id' => $isIssue ? null : $source->id,
                'title' => $data->title,
                'description' => $data->description,
                'impacts_scope' => $data->impacts_scope,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $source->update(['status' => 'escalated']);

            return $changeRequest;
        });
    }
}

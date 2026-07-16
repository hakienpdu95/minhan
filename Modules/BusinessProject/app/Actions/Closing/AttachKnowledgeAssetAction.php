<?php

namespace Modules\BusinessProject\Actions\Closing;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\AttachKnowledgeAssetData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\KcItem\Models\KcItem;

/**
 * Rule R7 — KcItem module bắt buộc `category_id` (Modules/KcCategory, khái niệm khác BusinessProject)
 * nên không tạo KcItem "rút gọn" chỉ bằng dữ liệu BCOS — giống hệt lý do AttachTaskToProjectAction
 * không tạo Task rút gọn (Modules/Project bắt buộc). Action này chỉ GẮN THẺ business_project_id
 * lên 1 KcItem đã tồn tại; KcItem mới hoàn toàn phải tạo qua route `backend.kc-items.create`
 * (prefill business_project_id qua query string, xem KcItemController).
 */
class AttachKnowledgeAssetAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, AttachKnowledgeAssetData $data): KcItem
    {
        $kcItem = KcItem::where('organization_id', $businessProject->organization_id)
            ->findOrFail($data->kc_item_id);

        $kcItem->update([
            'business_project_id' => $businessProject->id,
            'updated_by' => Auth::id(),
        ]);

        return $kcItem;
    }
}

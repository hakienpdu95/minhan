<?php

namespace Modules\KcItem\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\KcItem\Actions\Backend\DestroyKcAttachmentAction;
use Modules\KcItem\Actions\Backend\StoreKcAttachmentAction;
use Modules\KcItem\Models\KcItem;
use Modules\KcItem\Models\KcItemAttachment;

class KcAttachmentApiController extends Controller
{
    public function store(Request $request, KcItem $kcItem, StoreKcAttachmentAction $action): JsonResponse
    {
        $this->authorize('update', $kcItem);

        $maxMb      = config('kc.attachments.max_file_size_mb', 50);
        $maxItemMb  = config('kc.attachments.max_item_total_mb', 200);
        $allowedExt = implode(',', config('kc.attachments.allowed_extensions', []));

        $request->validate([
            'file' => [
                'required', 'file',
                'max:' . ($maxMb * 1024),
                'mimes:' . $allowedExt,
            ],
        ]);

        // Kiểm tra tổng dung lượng của tài liệu
        $currentTotalKb = $kcItem->attachments()->sum('file_size_kb');
        $newSizeKb      = (int) ceil($request->file('file')->getSize() / 1024);

        if (($currentTotalKb + $newSizeKb) > $maxItemMb * 1024) {
            return response()->json([
                'message' => 'Tổng dung lượng file đính kèm vượt quá ' . $maxItemMb . 'MB cho phép.',
            ], 422);
        }

        $attachment = $action->handle($kcItem, $request->file('file'));

        return response()->json([
            'id'           => $attachment->id,
            'uuid'         => $attachment->uuid,
            'file_name'    => $attachment->file_name,
            'file_url'     => $attachment->file_url,
            'file_type'    => $attachment->file_type,
            'file_size_kb' => $attachment->file_size_kb,
            'delete_url'   => route('backend.api.kc-items.attachments.destroy', [$kcItem, $attachment]),
        ], 201);
    }

    public function destroy(KcItem $kcItem, KcItemAttachment $attachment, DestroyKcAttachmentAction $action): JsonResponse
    {
        $this->authorize('update', $kcItem);

        if ($attachment->item_id !== $kcItem->id) {
            abort(404);
        }

        $action->handle($attachment);

        return response()->json(['message' => 'Đã xóa file đính kèm.']);
    }
}

<?php

namespace Modules\KcItem\Actions\Backend;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\KcItem\Models\KcItem;
use Modules\KcItem\Models\KcItemAttachment;

class StoreKcAttachmentAction
{
    use AsAction;

    public function handle(KcItem $kcItem, UploadedFile $file): KcItemAttachment
    {
        $disk     = config('kc.storage.disk', 'local');
        $basePath = config('kc.storage.path', 'kc/attachments');

        $storageKey = $basePath . '/' . $kcItem->id . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk($disk)->putFileAs(
            dirname($storageKey),
            $file,
            basename($storageKey)
        );

        $fileUrl = Storage::disk($disk)->url($storageKey);

        $sortOrder = $kcItem->attachments()->max('sort_order') + 1;

        return KcItemAttachment::create([
            'uuid'             => Str::uuid(),
            'item_id'          => $kcItem->id,
            'file_name'        => $file->getClientOriginalName(),
            'file_url'         => $fileUrl,
            'file_type'        => $file->getMimeType(),
            'file_size_kb'     => (int) ceil($file->getSize() / 1024),
            'storage_provider' => $disk,
            'storage_key'      => $storageKey,
            'sort_order'       => $sortOrder,
            'uploaded_by'      => auth()->id(),
        ]);
    }
}

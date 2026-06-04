<?php

namespace Modules\KcItem\Actions\Backend;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\KcItem\Models\KcItemAttachment;

class DestroyKcAttachmentAction
{
    use AsAction;

    public function handle(KcItemAttachment $attachment): string
    {
        $fileName = $attachment->file_name;

        $disk = $attachment->storage_provider ?: config('kc.storage.disk', 'local');
        if (Storage::disk($disk)->exists($attachment->storage_key)) {
            Storage::disk($disk)->delete($attachment->storage_key);
        }

        $attachment->delete();

        return $fileName;
    }
}

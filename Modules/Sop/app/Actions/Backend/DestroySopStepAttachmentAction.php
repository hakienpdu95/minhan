<?php

namespace Modules\Sop\Actions\Backend;

use Illuminate\Support\Facades\Storage;
use Modules\Sop\Models\SopStepAttachment;
use Modules\Sop\Repositories\SopFlowchartRepository;

class DestroySopStepAttachmentAction
{
    public function __construct(private readonly SopFlowchartRepository $repo) {}

    public function handle(SopStepAttachment $attachment): void
    {
        $sopId = $attachment->step->sop_id;
        $disk  = $attachment->storage_provider === 's3' ? 's3' : config('sop.attachments.storage_disk', 'local');

        if (Storage::disk($disk)->exists($attachment->storage_key)) {
            Storage::disk($disk)->delete($attachment->storage_key);
        }

        $attachment->delete();

        $this->repo->invalidate($sopId);
    }
}

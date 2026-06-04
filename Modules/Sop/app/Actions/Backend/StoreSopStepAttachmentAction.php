<?php

namespace Modules\Sop\Actions\Backend;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Sop\Models\SopStep;
use Modules\Sop\Models\SopStepAttachment;
use Modules\Sop\Repositories\SopFlowchartRepository;

class StoreSopStepAttachmentAction
{
    public function __construct(private readonly SopFlowchartRepository $repo) {}

    public function handle(SopStep $step, UploadedFile $file): SopStepAttachment
    {
        $disk   = config('sop.attachments.storage_disk', 'local');
        $prefix = config('sop.attachments.storage_prefix', 'sop-attachments');

        $orgId  = $step->sop->organization_id;
        $dir    = "{$prefix}/{$orgId}/{$step->sop_id}/{$step->id}";
        $key    = $dir . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk($disk)->put($key, file_get_contents($file->getRealPath()));

        $url = Storage::disk($disk)->url($key);

        $maxSort = SopStepAttachment::where('step_id', $step->id)->max('sort_order') ?? -1;

        $attachment = SopStepAttachment::create([
            'uuid'             => Str::uuid(),
            'step_id'          => $step->id,
            'file_name'        => $file->getClientOriginalName(),
            'file_url'         => $url,
            'file_type'        => $file->getMimeType(),
            'file_size_kb'     => (int) ceil($file->getSize() / 1024),
            'storage_provider' => $disk === 's3' ? 's3' : 'local',
            'storage_key'      => $key,
            'alt_text'         => null,
            'sort_order'       => $maxSort + 1,
            'uploaded_by'      => auth()->id(),
            'uploaded_at'      => now(),
        ]);

        $this->repo->invalidate($step->sop_id);

        return $attachment;
    }
}

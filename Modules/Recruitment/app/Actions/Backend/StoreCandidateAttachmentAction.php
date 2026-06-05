<?php

namespace Modules\Recruitment\Actions\Backend;

use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Recruitment\Models\RcCandidate;
use Modules\Recruitment\Models\RcCandidateAttachment;

class StoreCandidateAttachmentAction
{
    use AsAction;

    public function handle(RcCandidate $candidate, UploadedFile $file, array $data): RcCandidateAttachment
    {
        $storageKey = 'recruitment/candidates/' . $candidate->uuid . '/' . uniqid() . '_' . $file->getClientOriginalName();

        $path = $file->storeAs(
            'recruitment/candidates/' . $candidate->uuid,
            uniqid() . '_' . $file->getClientOriginalName(),
            'public'
        );

        return RcCandidateAttachment::create([
            'candidate_id'    => $candidate->id,
            'application_id'  => $data['application_id'] ?? null,
            'file_type'       => $data['file_type'] ?? 'cv',
            'file_name'       => $file->getClientOriginalName(),
            'file_url'        => '/storage/' . $path,
            'file_size_kb'    => (int) ceil($file->getSize() / 1024),
            'storage_provider'=> 'local',
            'storage_key'     => $path,
            'uploaded_by'     => auth()->id(),
        ]);
    }
}

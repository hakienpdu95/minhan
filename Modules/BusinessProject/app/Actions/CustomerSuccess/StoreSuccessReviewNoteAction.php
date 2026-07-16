<?php

namespace Modules\BusinessProject\Actions\CustomerSuccess;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\StoreSuccessReviewNoteData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\SuccessReview;

class StoreSuccessReviewNoteAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreSuccessReviewNoteData $data): SuccessReview
    {
        if ($data->follow_up_at === null && $data->renewal_status === null && $data->renewal_note === null) {
            throw ValidationException::withMessages([
                'follow_up_at' => 'Vui lòng điền ít nhất 1 trong 2: lịch follow-up hoặc renewal.',
            ]);
        }

        return SuccessReview::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'follow_up_at' => $data->follow_up_at,
            'follow_up_note' => $data->follow_up_note,
            'renewal_status' => $data->renewal_status,
            'renewal_note' => $data->renewal_note,
            'created_by' => Auth::id(),
        ]);
    }
}

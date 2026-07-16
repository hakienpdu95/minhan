<?php

namespace Modules\BusinessProject\Actions\CustomerSuccess;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Models\SuccessReview;

class MarkFollowUpDoneAction
{
    use AsAction;

    public function handle(SuccessReview $successReview): SuccessReview
    {
        $successReview->update([
            'followed_up_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return $successReview;
    }
}

<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Models\SurveyToken;

class DeleteSurveyTokenAction
{
    use AsAction;

    public function handle(SurveyToken $token): void
    {
        // Log trước khi xóa — model ID vẫn còn tồn tại lúc này
        activity()
            ->performedOn($token)
            ->withProperties(['survey_id' => $token->survey_id, 'name' => $token->name])
            ->log('token.deleted');

        $token->delete();
    }
}

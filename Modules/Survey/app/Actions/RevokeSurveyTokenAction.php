<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Models\SurveyToken;

class RevokeSurveyTokenAction
{
    use AsAction;

    public function handle(SurveyToken $token): void
    {
        $token->update(['is_active' => false]);

        activity()
            ->performedOn($token)
            ->withProperties(['survey_id' => $token->survey_id, 'name' => $token->name])
            ->log('token.revoked');
    }
}

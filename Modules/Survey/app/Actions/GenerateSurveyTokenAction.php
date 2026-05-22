<?php

namespace Modules\Survey\Actions;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\TokenFormData;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyToken;

class GenerateSurveyTokenAction
{
    use AsAction;

    /**
     * Tạo token mới cho survey.
     * Trả về plaintext DUY NHẤT 1 lần — không lưu vào DB.
     * DB chỉ lưu SHA-256 hash.
     *
     * @return array{ token: SurveyToken, plain: string }
     */
    public function handle(Survey $survey, TokenFormData $data): array
    {
        $plain     = Str::random(64);
        $hashed    = hash('sha256', $plain);
        $encrypted = Crypt::encryptString($plain);

        $token = SurveyToken::create([
            'survey_id'       => $survey->id,
            'name'            => $data->name,
            'token'           => $hashed,
            'token_encrypted' => $encrypted,
            'is_active'       => true,
            'expires_at'      => $data->expires_at,
        ]);

        activity()
            ->performedOn($token)
            ->withProperties(['survey_id' => $survey->id, 'name' => $data->name])
            ->log('token.created');

        return ['token' => $token, 'plain' => $plain];
    }
}

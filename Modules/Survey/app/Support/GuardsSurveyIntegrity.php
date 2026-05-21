<?php

namespace Modules\Survey\Support;

use Illuminate\Validation\ValidationException;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;

/**
 * Chặn sửa/xóa field + option khi survey đã active và có ít nhất 1 response.
 * Dùng trong: UpdateFieldAction, DeactivateFieldAction, DestroySectionAction,
 *             UpdateOptionAction, DestroyOptionAction.
 */
trait GuardsSurveyIntegrity
{
    /**
     * Throw 422 nếu survey đang active VÀ đã có responses.
     * Nếu chỉ active nhưng chưa có response → vẫn cho chỉnh sửa.
     */
    protected function guardLockedSurvey(Survey $survey): void
    {
        if ($survey->status !== SurveyStatus::Active) {
            return;
        }

        $hasResponses = $survey->responses()->complete()->exists();

        if ($hasResponses) {
            throw ValidationException::withMessages([
                'survey' => 'Survey đã active và đã có responses. Không thể sửa hoặc xóa field/option. Chỉ được phép deactivate field.',
            ]);
        }
    }
}

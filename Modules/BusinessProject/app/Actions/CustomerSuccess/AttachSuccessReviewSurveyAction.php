<?php

namespace Modules\BusinessProject\Actions\CustomerSuccess;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Data\Requests\AttachSuccessReviewSurveyData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\SuccessReview;
use Modules\Survey\Enums\FieldType;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;

/**
 * Giai đoạn 8 — gắn 1 SurveyResponse ĐÃ TỒN TẠI (đã điền qua trang "take" chuẩn của Survey
 * engine, KHÔNG tạo response mới ở đây) vào 1 Business Project, tạo 1 SuccessReview mới.
 * csat_score/nps_score denormalize từ SurveyAnswer tại thời điểm gắn (đọc theo field_type,
 * không hard-code field_key vì field_key sinh ngẫu nhiên lúc tạo field).
 */
class AttachSuccessReviewSurveyAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, AttachSuccessReviewSurveyData $data): SuccessReview
    {
        $orgSurveyIds = Survey::where('organization_id', $businessProject->organization_id)->pluck('id');

        $response = SurveyResponse::whereIn('survey_id', $orgSurveyIds)
            ->findOrFail($data->survey_response_id);

        $npsField = SurveyField::where('survey_id', $response->survey_id)
            ->where('field_type', FieldType::Nps->value)
            ->first();

        $csatField = SurveyField::where('survey_id', $response->survey_id)
            ->where('field_type', FieldType::Rating->value)
            ->first();

        $npsScore = $npsField
            ? SurveyAnswer::where('response_id', $response->id)->where('field_id', $npsField->id)->value('value_number')
            : null;

        $csatScore = $csatField
            ? SurveyAnswer::where('response_id', $response->id)->where('field_id', $csatField->id)->value('value_number')
            : null;

        return SuccessReview::create([
            'organization_id' => $businessProject->organization_id,
            'uuid' => Str::uuid(),
            'business_project_id' => $businessProject->id,
            'survey_response_id' => $response->id,
            'csat_score' => $csatScore,
            'nps_score' => $npsScore,
            'created_by' => Auth::id(),
        ]);
    }
}

<?php

namespace Modules\BusinessProject\Actions\CustomerSuccess;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Actions\ActivateSurveyAction;
use Modules\Survey\Actions\CreateFieldAction;
use Modules\Survey\Actions\CreateSectionAction;
use Modules\Survey\Actions\CreateSurveyAction;
use Modules\Survey\Data\FieldFormData;
use Modules\Survey\Data\SectionFormData;
use Modules\Survey\Data\SurveyFormData;
use Modules\Survey\Enums\FieldType;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;

/**
 * Giai đoạn 8 spec — "tạo khảo sát bằng Survey engine hiện có ... không xây form khảo sát
 * mới": 1 Survey CSAT/NPS DÙNG CHUNG cho mọi Business Project của 1 tổ chức (không tạo 1
 * Survey riêng mỗi dự án) — mỗi lần Customer Success điền là 1 SurveyResponse riêng
 * (allow_multiple_responses=true), sau đó gắn response đó vào đúng project qua
 * AttachSuccessReviewSurveyAction. Idempotent theo `organization_id` + title cố định — gọi
 * lại nhiều lần chỉ trả về Survey đã tồn tại, không tạo trùng.
 */
class EnsureCsatNpsSurveyAction
{
    use AsAction;

    public const TITLE = 'Khảo sát CSAT/NPS - Customer Success';

    public function handle(int $organizationId): Survey
    {
        $survey = Survey::where('organization_id', $organizationId)
            ->where('title', self::TITLE)
            ->first();

        if ($survey !== null) {
            return $survey;
        }

        $survey = CreateSurveyAction::run(SurveyFormData::from([
            'organization_id' => $organizationId,
            'title' => self::TITLE,
            'description' => 'Khảo sát chuẩn cho Customer Success Workspace (BCOS Giai đoạn 8) — '
                .'dùng chung cho mọi Business Project, mỗi lần điền gắn vào 1 dự án cụ thể.',
        ]));

        $section = CreateSectionAction::run($survey, SectionFormData::from([
            'title' => 'Đánh giá mức độ hài lòng',
        ]));

        CreateFieldAction::run($survey, FieldFormData::from([
            'label' => 'Trên thang điểm 0-10, bạn có sẵn lòng giới thiệu dịch vụ của chúng tôi cho đồng nghiệp/đối tác? (NPS)',
            'field_type' => FieldType::Nps,
            'is_required' => true,
            'section_id' => $section->id,
            'rule_min' => 0,
            'rule_max' => 10,
        ]));

        CreateFieldAction::run($survey, FieldFormData::from([
            'label' => 'Mức độ hài lòng chung với dự án (1 = rất không hài lòng, 5 = rất hài lòng) (CSAT)',
            'field_type' => FieldType::Rating,
            'is_required' => true,
            'section_id' => $section->id,
            'rule_min' => 1,
            'rule_max' => 5,
        ]));

        CreateFieldAction::run($survey, FieldFormData::from([
            'label' => 'Góp ý thêm (nếu có)',
            'field_type' => FieldType::Textarea,
            'is_required' => false,
            'section_id' => $section->id,
        ]));

        $survey->update(['allow_multiple_responses' => true]);

        if ($survey->status !== SurveyStatus::Active) {
            $survey = ActivateSurveyAction::run($survey);
        }

        return $survey;
    }
}

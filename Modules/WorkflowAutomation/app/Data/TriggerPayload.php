<?php
namespace Modules\WorkflowAutomation\Data;

use Spatie\LaravelData\Data;

class TriggerPayload extends Data
{
    public function __construct(
        public readonly string  $triggerType,
        public readonly string  $sourceModule,
        public readonly ?int    $organizationId,
        public readonly ?int    $actorId,
        public readonly ?string $actorEmail,
        public readonly ?string $actorName,
        public readonly ?string $actorRole,
        public readonly ?string $subjectType,
        public readonly ?int    $subjectId,
        public readonly ?string $subjectLabel,
        public readonly array   $extra = [],
        public readonly string  $requestId = '',
        public readonly \DateTimeImmutable $firedAt = new \DateTimeImmutable(),
    ) {}

    public static function forSurveySubmit(
        \Modules\Survey\Models\SurveyResponse $response,
        ?int $actorId = null,
    ): self {
        return new self(
            triggerType:    'survey.submitted',
            sourceModule:   'Survey',
            organizationId: \App\Shared\Tenancy\TenantContext::getOrganizationId(),
            actorId:        $actorId ?? auth()->id(),
            actorEmail:     $response->respondent_ref,
            actorName:      null,
            actorRole:      null,
            subjectType:    'SurveyResponse',
            subjectId:      $response->id,
            subjectLabel:   "Response #{$response->id}",
            extra: [
                'survey_id'      => $response->survey_id,
                'survey_slug'    => $response->survey?->slug,
                'respondent_ref' => $response->respondent_ref,
            ],
            requestId: request()->header('X-Request-Id', (string) \Str::uuid()),
        );
    }

    public static function forSurveyResult(
        \Modules\Survey\Models\SurveyResult $result,
    ): self {
        return new self(
            triggerType:    'survey.result_calculated',
            sourceModule:   'Survey',
            organizationId: \App\Shared\Tenancy\TenantContext::getOrganizationId(),
            actorId:        null,
            actorEmail:     $result->response?->respondent_ref,
            actorName:      null,
            actorRole:      null,
            subjectType:    'SurveyResponse',
            subjectId:      $result->response_id,
            subjectLabel:   "Result #{$result->id}",
            extra: [
                'survey_id'      => $result->response?->survey_id,
                'band_code'      => $result->maturity_level,
                'overall_score'  => $result->overall_score,
                'weight_version' => $result->weight_version,
            ],
            requestId: (string) \Str::uuid(),
        );
    }

    public static function forAssessmentResult(
        \Modules\Assessment\Models\AssessmentResult $result,
    ): self {
        return new self(
            triggerType:    'assessment.result_calculated',
            sourceModule:   'Assessment',
            organizationId: \App\Shared\Tenancy\TenantContext::getOrganizationId(),
            actorId:        null,
            actorEmail:     null,
            actorName:      null,
            actorRole:      null,
            subjectType:    $result->subject_type,
            subjectId:      $result->subject_id,
            subjectLabel:   "AssessmentResult #{$result->id}",
            extra: [
                'assessment_code' => $result->assessment_code,
                'band_code'       => $result->maturity_level,
                'overall_score'   => $result->overall_score,
                'weight_version'  => $result->weight_version,
                'subject_type'    => $result->subject_type,
                'subject_id'      => $result->subject_id,
            ],
            requestId: (string) \Str::uuid(),
        );
    }

    public function resolve(string $field): mixed
    {
        return match(true) {
            $field === 'trigger.type'          => $this->triggerType,
            $field === 'trigger.module'        => $this->sourceModule,
            $field === 'actor.id'              => $this->actorId,
            $field === 'actor.email'           => $this->actorEmail,
            $field === 'actor.role'            => $this->actorRole,
            $field === 'subject.type'          => $this->subjectType,
            $field === 'subject.id'            => $this->subjectId,
            str_starts_with($field, 'extra.')  => $this->extra[substr($field, 6)] ?? null,
            default                            => null,
        };
    }

    public function render(string $template): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', function ($m) {
            $val = $this->resolve($m[1]);
            return $val !== null ? (string) $val : $m[0];
        }, $template);
    }
}

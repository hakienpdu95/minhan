<?php

namespace Modules\Deployment\Data;

use Illuminate\Validation\Rule;
use Modules\Deployment\Enums\IssueSeverity;
use Spatie\LaravelData\Data;

class StoreDeploymentIssueData extends Data
{
    public function __construct(
        public readonly int           $deployment_target_id,
        public readonly int           $project_id,
        public readonly string        $title,
        public readonly ?string       $description,
        public readonly IssueSeverity $severity,
        public readonly ?int          $owner_id,
    ) {}

    public static function rules(): array
    {
        return [
            'deployment_target_id' => ['required', 'integer', 'exists:deployment_targets,id'],
            'project_id'           => ['required', 'integer', 'exists:projects,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:5000'],
            'severity'             => ['required', Rule::enum(IssueSeverity::class)],
            'owner_id'             => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public static function messages(): array
    {
        return [
            'title.required'                => 'Vui lòng nhập tiêu đề issue.',
            'deployment_target_id.required' => 'Vui lòng chọn đối tượng triển khai.',
            'severity.required'             => 'Vui lòng chọn mức độ nghiêm trọng.',
        ];
    }
}

<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\ProjectMemberRole;
use Spatie\LaravelData\Data;

class ConvertLeadToBusinessProjectData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $project_role,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'project_role' => ['required', 'string', Rule::in(array_column(ProjectMemberRole::cases(), 'value'))],
        ];
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Tên Business Project là bắt buộc.',
            'project_role.required' => 'Vui lòng chọn vai trò của bạn trong dự án.',
            'project_role.in' => 'Vai trò không hợp lệ.',
        ];
    }
}

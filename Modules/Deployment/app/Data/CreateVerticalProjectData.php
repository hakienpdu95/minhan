<?php

namespace Modules\Deployment\Data;

use Illuminate\Validation\Rule;
use Modules\Project\Enums\ProjectStatus;
use Spatie\LaravelData\Data;

class CreateVerticalProjectData extends Data
{
    public function __construct(
        public readonly string        $name,
        public readonly string        $code,
        public readonly ProjectStatus $status,
        public readonly ?string       $description,
        public readonly ?string       $start_date,
        public readonly ?string       $end_date,
    ) {}

    public static function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50'],
            'status'      => ['required', Rule::enum(ProjectStatus::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date'  => ['nullable', 'date_format:Y-m-d'],
            'end_date'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên dự án.',
            'code.required' => 'Vui lòng nhập mã dự án.',
        ];
    }
}

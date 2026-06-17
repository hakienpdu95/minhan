<?php

namespace Modules\Deployment\Data;

use Spatie\LaravelData\Data;

class LogProgressData extends Data
{
    public function __construct(
        public readonly int     $deployment_target_id,
        public readonly string  $phase,
        public readonly int     $percent,
        public readonly ?string $remark,
    ) {}

    public static function rules(): array
    {
        return [
            'deployment_target_id' => ['required', 'integer', 'exists:deployment_targets,id'],
            'phase'                => ['required', 'string', 'max:50'],
            'percent'              => ['required', 'integer', 'min:0', 'max:100'],
            'remark'               => ['nullable', 'string', 'max:2000'],
        ];
    }

    public static function messages(): array
    {
        return [
            'percent.min' => 'Phần trăm không được nhỏ hơn 0.',
            'percent.max' => 'Phần trăm không được lớn hơn 100.',
        ];
    }
}

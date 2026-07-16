<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

/**
 * 8 mục theo Handbook 5.5 (Transformation Design Canvas ⭐): Business Goal, Priority Problems,
 * Transformation Objectives, Key Initiatives, Quick Wins, Resources, Risks, Success Metrics —
 * dùng để thống nhất với doanh nghiệp trước khi lập Roadmap.
 */
class StoreTransformationDesignCanvasData extends Data
{
    public function __construct(
        public readonly ?string $business_goal,
        public readonly ?string $priority_problems,
        public readonly ?string $transformation_objectives,
        public readonly ?string $key_initiatives,
        public readonly ?string $quick_wins,
        public readonly ?string $resources,
        public readonly ?string $risks,
        public readonly ?string $success_metrics,
    ) {}

    public static function rules(): array
    {
        return [
            'business_goal' => ['nullable', 'string'],
            'priority_problems' => ['nullable', 'string'],
            'transformation_objectives' => ['nullable', 'string'],
            'key_initiatives' => ['nullable', 'string'],
            'quick_wins' => ['nullable', 'string'],
            'resources' => ['nullable', 'string'],
            'risks' => ['nullable', 'string'],
            'success_metrics' => ['nullable', 'string'],
        ];
    }
}

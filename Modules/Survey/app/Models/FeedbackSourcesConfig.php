<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackSourcesConfig extends Model
{
    protected $table = 'feedback_sources_config';

    protected $fillable = [
        'source_type',
        'trust_weight',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'trust_weight' => 'float',
            'is_enabled'   => 'boolean',
        ];
    }

    public static function trustWeightFor(string $sourceType): float
    {
        $config = static::where('source_type', $sourceType)->where('is_enabled', true)->first();

        return match ($sourceType) {
            'admin_review'      => $config?->trust_weight ?? 1.0,
            'observed_outcome'  => $config?->trust_weight ?? 0.7,
            'user_self_report'  => $config?->trust_weight ?? 0.4,
            default             => 0.5,
        };
    }
}

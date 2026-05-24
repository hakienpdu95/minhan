<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureWeightHistory extends Model
{
    protected $table = 'feature_weight_history';
    public $timestamps = false;

    protected $fillable = [
        'feature_weight_id',
        'old_weight',
        'new_weight',
        'delta',
        'reason',
        'cycle_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_weight'  => 'float',
            'new_weight'  => 'float',
            'delta'       => 'float',
            'created_at'  => 'datetime',
        ];
    }

    public function featureWeight(): BelongsTo
    {
        return $this->belongsTo(FeatureWeight::class, 'feature_weight_id');
    }
}

<?php

namespace Modules\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MktApplicantPortfolio extends Model
{
    protected $table = 'mkt_applicant_portfolios';

    protected $fillable = [
        'uuid', 'applicant_id', 'title', 'description',
        'project_url', 'thumbnail_url', 'tech_stack', 'completed_year', 'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(MktApplicant::class, 'applicant_id');
    }
}

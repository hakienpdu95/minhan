<?php

namespace Modules\BusinessProject\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only — cùng nguyên tắc `DeliverableVersion`/`BusinessProjectStageHistory` (không
 * sửa/xoá sau khi ghi, model THƯỜNG không TenantAwareModel/SoftDeletes).
 */
class DeliverableSignature extends Model
{
    protected $table = 'deliverable_signatures';

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected $fillable = [
        'uuid',
        'organization_id',
        'deliverable_id',
        'deliverable_version_id',
        'signed_by',
        'provider',
        'algorithm',
        'content_hash',
        'signature',
        'public_key_fingerprint',
        'signed_at',
    ];

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DeliverableVersion::class, 'deliverable_version_id');
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}

<?php

namespace Modules\BusinessProject\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 1 keypair RSA/user (self-issued, KHÔNG phải CA cấp) — xem cảnh báo an toàn đầy đủ ở
 * InternalRsaSignatureProvider. Model THƯỜNG (không TenantAwareModel/SoftDeletes) — khoá riêng
 * `private_key_encrypted` không nên có đường "khôi phục sau khi xoá mềm", xoá là xoá thật.
 */
class UserSigningKey extends Model
{
    protected $table = 'user_signing_keys';

    protected $fillable = [
        'user_id',
        'organization_id',
        'algorithm',
        'public_key',
        'private_key_encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

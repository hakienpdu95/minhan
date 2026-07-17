<?php

namespace Modules\BusinessProject\Contracts;

use App\Models\User;
use Modules\BusinessProject\Models\DeliverableSignature;
use Modules\BusinessProject\Models\DeliverableVersion;

/**
 * Seam để đổi cơ chế "chữ ký số" khi Confirmed (Rule R4) mà không sửa `ConfirmDeliverableAction`/
 * Controller/View — hiện tại chỉ có `InternalRsaSignatureProvider` (RSA self-issued, không cần
 * hạ tầng ngoài). Khi cần chữ ký số CÓ giá trị pháp lý thật (VNPT-CA, VNPT SmartCA, HSM...), thêm
 * 1 class implements interface này + đổi `config('businessproject.signature.provider')`.
 */
interface DeliverableSignatureProvider
{
    public function provider(): string;

    public function sign(DeliverableVersion $version, User $signer): DeliverableSignature;

    public function verify(DeliverableSignature $signature): bool;
}

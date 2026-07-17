<?php

namespace Modules\BusinessProject\Signature;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Modules\BusinessProject\Contracts\DeliverableSignatureProvider;
use Modules\BusinessProject\Models\DeliverableSignature;
use Modules\BusinessProject\Models\DeliverableVersion;
use Modules\BusinessProject\Models\UserSigningKey;

/**
 * Chữ ký RSA-2048 "self-issued" — mỗi user có 1 keypair sinh lười lúc ký lần đầu, private key
 * lưu MÃ HOÁ (Crypt::encryptString, dùng APP_KEY) trong bảng `user_signing_keys`.
 *
 * ⚠️ GIỚI HẠN AN TOÀN CẦN BIẾT (đã trao đổi với user — chỉ dùng nội bộ, không thay thế chữ ký số
 * pháp lý): đây KHÔNG phải chữ ký số theo Nghị định 130/2018 (không CA cấp, không HSM, không
 * user tự giữ private key) — private key nằm trên chính server ứng dụng, được mã hoá bằng cùng
 * `APP_KEY` mà ứng dụng dùng cho mọi mục đích khác. Một admin có quyền truy cập DB + APP_KEY vẫn
 * có thể giải mã private key và tạo chữ ký giả danh user đó — mô hình này chỉ chống được: (a) sửa
 * nội dung sau khi ký (tamper-evidence qua content_hash), (b) người dùng thường tự chối đã ký
 * (non-repudiation ở mức nội bộ, nhờ bắt xác thực lại mật khẩu ở Controller trước khi gọi sign()).
 * KHÔNG dùng cho chữ ký có giá trị pháp lý với bên ngoài — khi cần, viết provider mới (VNPT-CA,
 * VNPT SmartCA...) implements DeliverableSignatureProvider, đổi
 * config('businessproject.signature.provider'), không sửa ConfirmDeliverableAction/Controller/View.
 */
class InternalRsaSignatureProvider implements DeliverableSignatureProvider
{
    public function provider(): string
    {
        return 'internal_rsa';
    }

    public function sign(DeliverableVersion $version, User $signer): DeliverableSignature
    {
        $key = $this->getOrCreateKeyPair($signer);

        $contentHash = hash('sha256', $this->canonicalPayload($version, $signer));

        $privateKeyResource = openssl_pkey_get_private(Crypt::decryptString($key->private_key_encrypted));
        if ($privateKeyResource === false) {
            throw new \RuntimeException('Không đọc được private key để ký: '.openssl_error_string());
        }

        $binarySignature = '';
        $signed = openssl_sign($contentHash, $binarySignature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new \RuntimeException('Ký số thất bại: '.openssl_error_string());
        }

        return DeliverableSignature::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $version->deliverable->organization_id,
            'deliverable_id' => $version->deliverable_id,
            'deliverable_version_id' => $version->id,
            'signed_by' => $signer->id,
            'provider' => $this->provider(),
            'algorithm' => 'sha256WithRSAEncryption',
            'content_hash' => $contentHash,
            'signature' => base64_encode($binarySignature),
            'public_key_fingerprint' => hash('sha256', $key->public_key),
            'signed_at' => now(),
        ]);
    }

    public function verify(DeliverableSignature $signature): bool
    {
        $version = $signature->version;
        $signer = $signature->signedBy;

        if (! $version || ! $signer) {
            return false;
        }

        // 1. Toàn vẹn nội dung: nội dung hiện tại (đọc lại từ chính version đã ký, deliverable
        // đã confirmed thì bị khoá không sửa được — UpsertSingletonDeliverableAction) vẫn khớp
        // hash lúc ký.
        $expectedHash = hash('sha256', $this->canonicalPayload($version, $signer));
        if (! hash_equals($expectedHash, $signature->content_hash)) {
            return false;
        }

        // 2. Xác thực chữ ký: đúng là được ký bằng private key của signer tại thời điểm đó.
        $key = UserSigningKey::where('user_id', $signer->id)->first();
        if (! $key) {
            return false;
        }

        $publicKeyResource = openssl_pkey_get_public($key->public_key);
        if ($publicKeyResource === false) {
            return false;
        }

        return openssl_verify(
            $signature->content_hash,
            base64_decode($signature->signature),
            $publicKeyResource,
            OPENSSL_ALGO_SHA256,
        ) === 1;
    }

    /**
     * KHÔNG đưa `signed_at` vào payload — payload phải tái tạo được y hệt lúc verify() sau này
     * chỉ từ dữ liệu ổn định (nội dung + danh tính signer), thời điểm ký lưu riêng ở cột
     * `signed_at` (metadata hiển thị, không phải một phần nội dung được ký).
     */
    private function canonicalPayload(DeliverableVersion $version, User $signer): string
    {
        return json_encode([
            'deliverable_id' => $version->deliverable_id,
            'deliverable_version_id' => $version->id,
            'version_number' => $version->version_number,
            'content' => $version->content,
            'signer_id' => $signer->id,
            'signer_email' => $signer->email,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function getOrCreateKeyPair(User $signer): UserSigningKey
    {
        $existing = UserSigningKey::where('user_id', $signer->id)->first();
        if ($existing) {
            return $existing;
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            throw new \RuntimeException('Không tạo được RSA keypair: '.openssl_error_string());
        }

        openssl_pkey_export($resource, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($resource)['key'];

        return UserSigningKey::create([
            'user_id' => $signer->id,
            'organization_id' => $signer->organization_id,
            'algorithm' => 'rsa-2048',
            'public_key' => $publicKeyPem,
            'private_key_encrypted' => Crypt::encryptString($privateKeyPem),
        ]);
    }
}

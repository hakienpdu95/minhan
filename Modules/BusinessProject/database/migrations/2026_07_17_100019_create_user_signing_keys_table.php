<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 keypair/user — self-issued (KHÔNG phải CA cấp), sinh lười (lazy) lúc user đầu tiên
        // ký. private_key luôn lưu ĐÃ MÃ HOÁ (Crypt::encryptString, dùng APP_KEY) — không bao giờ
        // lưu plaintext. Giới hạn biết trước (ghi rõ ở InternalRsaSignatureProvider): đây là mô
        // hình "self-signed" nội bộ, không có giá trị pháp lý "chữ ký số" theo Nghị định 130/2018
        // — khi cần ký hợp lệ pháp lý với bên ngoài, thay bằng provider mới trỏ tới CA/HSM thật
        // (VNPT-CA, VNPT SmartCA...) qua cùng interface DeliverableSignatureProvider, không sửa
        // lại bảng/luồng gọi.
        Schema::create('user_signing_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('algorithm', 40)->default('rsa-2048');
            $table->text('public_key');
            $table->text('private_key_encrypted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_signing_keys');
    }
};

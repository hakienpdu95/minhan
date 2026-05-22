<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lưu plaintext mã hóa AES (Laravel Crypt) để admin có thể xem lại token.
// token (hash sha256) vẫn giữ để lookup nhanh trong middleware.
// token_encrypted = null với các token tạo trước bản này.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            $table->text('token_encrypted')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });
    }
};

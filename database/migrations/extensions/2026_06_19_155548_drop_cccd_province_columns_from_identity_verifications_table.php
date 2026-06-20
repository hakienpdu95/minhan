<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            $cols = array_filter(
                ['issuing_province_code', 'issuing_province_id'],
                fn ($c) => Schema::hasColumn('identity_verifications', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }

            $table->string('method', 30)
                ->comment('email | phone_otp | vne_id | passport')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('identity_verifications', 'issuing_province_id')) {
                $table->unsignedBigInteger('issuing_province_id')->nullable()->comment('FK provinces.id — phi nhạy cảm, dùng cho phân tích địa lý');
            }
            if (!Schema::hasColumn('identity_verifications', 'issuing_province_code')) {
                $table->char('issuing_province_code', 2)->nullable()->comment('2-char province code từ provinces.province_code — derived từ 3 chữ số đầu CCCD');
            }

            $table->string('method', 30)
                ->comment('email | phone_otp | cccd_ocr | cccd_chip | vne_id | passport')
                ->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('identity_verifications', 'issuing_province_id')) {
                $table->dropForeign(['issuing_province_id']);
                $table->dropColumn('issuing_province_id');
            }

            if (!Schema::hasColumn('identity_verifications', 'issuing_province_code')) {
                $table->char('issuing_province_code', 2)->nullable()->after('expires_at')
                    ->comment('2-char province code từ provinces.province_code — derived từ 3 chữ số đầu CCCD');
            }
        });
    }

    public function down(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('identity_verifications', 'issuing_province_code')) {
                $table->dropColumn('issuing_province_code');
            }

            if (!Schema::hasColumn('identity_verifications', 'issuing_province_id')) {
                $table->unsignedBigInteger('issuing_province_id')->nullable()->after('expires_at');
                $table->foreign('issuing_province_id')->references('id')->on('provinces')->nullOnDelete();
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'national_id_hash')) {
                $table->dropUnique(['national_id_hash']);
                $table->dropColumn('national_id_hash');
            }
            $table->unsignedTinyInteger('trust_level')->default(0)
                ->comment('0=unverified, 1=email, 2=phone')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'national_id_hash')) {
                $table->string('national_id_hash', 64)->nullable()->unique()->after('phone_verified_at')->comment('SHA-256(số_CCCD) — check uniqueness, không lưu số thật');
            }
            $table->unsignedTinyInteger('trust_level')->default(0)
                ->comment('0=unverified, 1=email, 2=phone, 3=cccd, 4=cccd_biometric')
                ->change();
        });
    }
};

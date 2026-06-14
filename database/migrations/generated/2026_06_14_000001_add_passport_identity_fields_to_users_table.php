<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type', 20)->notNull()->default('free')
                    ->after('id')
                    ->comment('free | org_member | suspended');
            }
            if (!Schema::hasColumn('users', 'current_org_id')) {
                $table->unsignedBigInteger('current_org_id')->nullable()
                    ->after('account_type')
                    ->comment('NULL nếu free');
            }
            if (!Schema::hasColumn('users', 'trust_level')) {
                $table->unsignedTinyInteger('trust_level')->notNull()->default(0)
                    ->after('current_org_id')
                    ->comment('0=unverified, 1=email, 2=phone, 3=cccd, 4=cccd_biometric');
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()
                    ->after('trust_level');
            }
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()
                    ->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'national_id_hash')) {
                $table->string('national_id_hash', 64)->nullable()->unique()
                    ->after('phone_verified_at')
                    ->comment('SHA-256(số_CCCD) — check uniqueness, không lưu số thật');
            }
        });

        // Indexes (check before adding to avoid duplicate key errors)
        if (!Schema::hasIndex('users', 'users_account_type_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('account_type', 'users_account_type_index');
            });
        }
        if (!Schema::hasIndex('users', 'users_trust_level_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('trust_level', 'users_trust_level_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['account_type', 'current_org_id', 'trust_level', 'phone_number', 'phone_verified_at', 'national_id_hash'],
                fn($c) => Schema::hasColumn('users', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};

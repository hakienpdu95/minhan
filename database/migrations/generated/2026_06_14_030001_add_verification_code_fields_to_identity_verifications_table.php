<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('identity_verifications', 'verification_code')) {
                $table->string('verification_code', 10)->nullable()
                    ->after('rejection_reason')
                    ->comment('6-digit code for phone verification (dev: shown on-screen)');
            }
            if (!Schema::hasColumn('identity_verifications', 'code_expires_at')) {
                $table->timestamp('code_expires_at')->nullable()
                    ->after('verification_code')
                    ->comment('TTL 5 minutes for phone code');
            }
            if (!Schema::hasColumn('identity_verifications', 'phone_candidate')) {
                $table->string('phone_candidate', 20)->nullable()
                    ->after('code_expires_at')
                    ->comment('Phone number submitted, confirmed on verify');
            }
        });
    }

    public function down(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'code_expires_at', 'phone_candidate']);
        });
    }
};

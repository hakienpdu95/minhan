<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('identity_verifications', 'email_candidate')) {
                $table->string('email_candidate', 255)->nullable()
                    ->after('phone_candidate')
                    ->comment('Email address captured at send time; compared on confirm to detect mid-flight email changes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('identity_verifications', 'email_candidate')) {
                $table->dropColumn('email_candidate');
            }
        });
    }
};

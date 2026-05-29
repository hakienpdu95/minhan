<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_feedback', function (Blueprint $table) {
            $table->unsignedBigInteger('submitted_by')->nullable()->after('is_processed');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('scoring_feedback', function (Blueprint $table) {
            $table->dropColumn(['submitted_by', 'submitted_at']);
        });
    }
};

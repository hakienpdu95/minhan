<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropUnique('uq_score_rule');
            $table->string('domain_code', 50)->nullable()->change();
            $table->unique(['assessment_code', 'field_key', 'domain_code'], 'uq_score_rule');
        });
    }

    public function down(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropUnique('uq_score_rule');
            $table->string('domain_code', 50)->nullable(false)->change();
            $table->unique(['assessment_code', 'field_key', 'domain_code'], 'uq_score_rule');
        });
    }
};

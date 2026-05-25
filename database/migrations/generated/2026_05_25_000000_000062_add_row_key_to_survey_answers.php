<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->string('row_key', 100)->nullable()->after('field_id');
            $table->index(['response_id', 'field_id', 'row_key'], 'sa_response_field_row_idx');
        });
    }

    public function down(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropIndex('sa_response_field_row_idx');
            $table->dropColumn('row_key');
        });
    }
};

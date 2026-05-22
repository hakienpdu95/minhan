<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration gốc đặt UNIQUE(survey_id, is_active) — sai spec (chỉ cần INDEX).
// Constraint sai giới hạn mỗi survey chỉ có 1 active + 1 inactive token.
// MySQL không cho drop unique index đang serve FK — phải tạo index thay thế trước.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            // Tạo index thay thế để MySQL không cần dùng unique index cho FK
            $table->index('survey_id', 'survey_tokens_survey_id_index');
        });

        Schema::table('survey_tokens', function (Blueprint $table) {
            // Giờ có thể drop unique (MySQL không cần nó cho FK nữa)
            $table->dropUnique('survey_tokens_survey_id_is_active_unique');
            // Thêm non-unique composite index theo spec
            $table->index(['survey_id', 'is_active'], 'survey_tokens_survey_id_is_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            $table->dropIndex('survey_tokens_survey_id_is_active_index');
            $table->unique(['survey_id', 'is_active'], 'survey_tokens_survey_id_is_active_unique');
            $table->dropIndex('survey_tokens_survey_id_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // maturity_levels: mỗi level map sang một nhiệt độ lead cố định
        Schema::table('maturity_levels', function (Blueprint $table) {
            $table->string('lead_temperature', 10)->default('cold')
                ->comment('hot | warm | cold — dùng để classify lead')
                ->after('sort_order');
        });

        // survey_results: lưu sẵn để query/filter nhanh, không cần JOIN
        Schema::table('survey_results', function (Blueprint $table) {
            $table->string('lead_temperature', 10)->default('cold')
                ->comment('hot | warm | cold — mirror từ maturity_levels')
                ->after('assessment_code');
            $table->index('lead_temperature');
        });

        // surveys: email nhận thông báo khi có hot lead
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('lead_notify_email', 255)->nullable()
                ->comment('Email nhận alert khi hot lead nộp bài — để trống = không gửi')
                ->after('assessment_code');
        });
    }

    public function down(): void
    {
        Schema::table('maturity_levels', fn ($t) => $t->dropColumn('lead_temperature'));
        Schema::table('survey_results',  fn ($t) => $t->dropColumn('lead_temperature'));
        Schema::table('surveys',         fn ($t) => $t->dropColumn('lead_notify_email'));
    }
};

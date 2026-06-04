<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('assessment_code', 50)->nullable()->unique()->comment('Mã định danh cho Scoring Engine — nullable nếu survey không có scoring');
            $table->string('lead_notify_email', 255)->nullable()->after('assessment_code')->comment('Email nhận alert khi hot lead nộp bài — để trống = không gửi');
            $table->boolean('allow_multiple_responses')->default(true)->after('lead_notify_email');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['assessment_code', 'lead_notify_email', 'allow_multiple_responses']);
        });
    }
};
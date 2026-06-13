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
            if (!Schema::hasColumn('surveys', 'assessment_code')) {
                $table->string('assessment_code', 50)->nullable()->unique()->comment('Mã định danh cho Scoring Engine — nullable nếu survey không có scoring');
            }
            if (!Schema::hasColumn('surveys', 'lead_notify_email')) {
                $table->string('lead_notify_email', 255)->nullable()->after('assessment_code')->comment('Email nhận alert khi hot lead nộp bài — để trống = không gửi');
            }
            if (!Schema::hasColumn('surveys', 'allow_multiple_responses')) {
                $table->boolean('allow_multiple_responses')->default(true)->after('lead_notify_email');
            }
            if (!Schema::hasColumn('surveys', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete()->after('allow_multiple_responses');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'organization_id')) $table->dropForeign(['organization_id']);
            $cols = array_filter(['assessment_code', 'lead_notify_email', 'allow_multiple_responses', 'organization_id'], fn($c) => Schema::hasColumn('surveys', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
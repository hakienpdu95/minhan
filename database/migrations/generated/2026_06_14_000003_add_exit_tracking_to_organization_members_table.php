<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            // --- Phase 0 §6.2: Exit tracking ---
            if (!Schema::hasColumn('organization_members', 'status')) {
                $table->string('status', 20)->notNull()->default('active')
                    ->comment('active | inactive | paused | suspended');
            }
            if (!Schema::hasColumn('organization_members', 'left_at')) {
                $table->timestamp('left_at')->nullable();
            }
            if (!Schema::hasColumn('organization_members', 'exit_reason')) {
                $table->string('exit_reason', 50)->nullable()
                    ->comment('resigned | terminated | retired | contract_end | internal_transfer');
            }
            if (!Schema::hasColumn('organization_members', 'exit_initiated_by')) {
                $table->string('exit_initiated_by', 20)->nullable()
                    ->comment('self | hr | system');
            }
            if (!Schema::hasColumn('organization_members', 'job_title_at_exit')) {
                $table->string('job_title_at_exit', 200)->nullable();
            }
            if (!Schema::hasColumn('organization_members', 'department_at_exit')) {
                $table->string('department_at_exit', 200)->nullable();
            }
            if (!Schema::hasColumn('organization_members', 'role_at_exit')) {
                $table->string('role_at_exit', 50)->nullable();
            }
            if (!Schema::hasColumn('organization_members', 'account_was_org_created')) {
                $table->tinyInteger('account_was_org_created')->notNull()->default(0)
                    ->comment('1 nếu org tạo tài khoản này (không phải user tự đăng ký)');
            }

            // --- Phase 0 §5.6: Late offboarding & Inactivity monitoring ---
            if (!Schema::hasColumn('organization_members', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()
                    ->comment('Ngày hết hợp đồng — hệ thống auto-suspend nếu HR không offboard trước');
            }
            if (!Schema::hasColumn('organization_members', 'auto_suspended_at')) {
                $table->timestamp('auto_suspended_at')->nullable()
                    ->comment('Thời điểm hệ thống tự suspend do hết contract_end_date');
            }
            if (!Schema::hasColumn('organization_members', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()
                    ->comment('Lần cuối user có activity trong org này (login + action)');
            }
            if (!Schema::hasColumn('organization_members', 'effective_left_at')) {
                $table->timestamp('effective_left_at')->nullable()
                    ->comment('Ngày thực tế nghỉ việc — do HR nhập, có thể khác left_at');
            }
            if (!Schema::hasColumn('organization_members', 'offboarded_at')) {
                $table->timestamp('offboarded_at')->nullable()
                    ->comment('Ngày HR click offboard trên hệ thống');
            }
            if (!Schema::hasColumn('organization_members', 'late_offboard_gap_days')) {
                $table->unsignedSmallInteger('late_offboard_gap_days')->nullable()
                    ->comment('Số ngày gap giữa effective_left_at và offboarded_at — 0 nếu offboard đúng hạn');
            }
        });

        if (!Schema::hasIndex('organization_members', 'om_status_index')) {
            Schema::table('organization_members', function (Blueprint $table) {
                $table->index('status', 'om_status_index');
                $table->index('left_at', 'om_left_at_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $cols = array_filter([
                'status', 'left_at', 'exit_reason', 'exit_initiated_by',
                'job_title_at_exit', 'department_at_exit', 'role_at_exit',
                'account_was_org_created', 'contract_end_date', 'auto_suspended_at',
                'last_active_at', 'effective_left_at', 'offboarded_at',
                'late_offboard_gap_days',
            ], fn($c) => Schema::hasColumn('organization_members', $c));

            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};

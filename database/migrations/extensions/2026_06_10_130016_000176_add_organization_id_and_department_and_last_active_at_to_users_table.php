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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete()->after('email')->comment('Thuộc tổ chức nào — null nếu super-admin');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 50)->nullable()->index()->after('organization_id')->comment('Phòng ban: hr, sales, ops, marketing');
            }
            if (!Schema::hasColumn('users', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('department')->comment('Lần hoạt động cuối cùng');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('last_active_at')->comment('Trạng thái tài khoản');
            }
            if (!Schema::hasIndex('users', 'users_organization_id_index')) {
                $table->index('organization_id');
            }
            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organization_id')) $table->dropForeign(['organization_id']);
            $cols = array_filter(['organization_id', 'department', 'last_active_at', 'is_active', 'branch_id', 'department_id'], fn($c) => Schema::hasColumn('users', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
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
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete()->after('email')->comment('Thuộc tổ chức nào');
            $table->string('department', 50)->nullable()->index()->after('organization_id')->comment('Phòng ban: hr, sales, ops, marketing');
            $table->timestamp('last_active_at')->nullable()->after('department')->comment('Lần hoạt động cuối');
            $table->boolean('is_active')->default(true)->index()->after('last_active_at')->comment('Trạng thái tài khoản');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'department', 'last_active_at', 'is_active']);
        });
    }
};
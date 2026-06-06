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
        Schema::table('mkt_listings', function (Blueprint $table) {
            $table->unsignedBigInteger('posted_by')->default(0);
            $table->string('slug', 320)->nullable()->unique()->after('posted_by');
            $table->text('benefits')->nullable()->after('slug');
            $table->string('visibility', 20)->default('public')->after('benefits');
            $table->boolean('salary_is_negotiable')->default(false)->after('visibility');
            $table->boolean('salary_is_visible')->default(true)->after('salary_is_negotiable');
            $table->decimal('budget_min', 15, 2)->nullable()->after('salary_is_visible');
            $table->decimal('budget_max', 15, 2)->nullable()->after('budget_min');
            $table->integer('duration_days')->nullable()->after('budget_max');
            $table->unsignedBigInteger('department_id')->nullable()->after('duration_days');
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
            $table->integer('bookmark_count')->default(0)->after('position_id');
            $table->string('jp_sync_status', 20)->nullable()->after('bookmark_count');
            $table->boolean('auto_close_on_jp')->default(true)->after('jp_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('mkt_listings', function (Blueprint $table) {
            $table->dropColumn(['posted_by', 'slug', 'benefits', 'visibility', 'salary_is_negotiable', 'salary_is_visible', 'budget_min', 'budget_max', 'duration_days', 'department_id', 'position_id', 'bookmark_count', 'jp_sync_status', 'auto_close_on_jp']);
        });
    }
};
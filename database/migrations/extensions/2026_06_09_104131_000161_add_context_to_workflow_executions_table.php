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
        Schema::table('workflow_executions', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_executions', 'context')) {
                $table->json('context')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflow_executions', function (Blueprint $table) {
            $cols = array_filter(['context'], fn($c) => Schema::hasColumn('workflow_executions', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_rule_options', function (Blueprint $table) {
            $table->string('option_label', 255)->default('')
                ->comment('Nhãn hiển thị của option')
                ->after('option_value');
        });
    }

    public function down(): void
    {
        Schema::table('score_rule_options', function (Blueprint $table) {
            $table->dropColumn('option_label');
        });
    }
};

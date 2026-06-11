<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_goals', function (Blueprint $table) {
            $table->string('ai_impact_category', 20)->nullable()->after('direction')
                ->comment('learning|productivity|quality|ai_adoption|business');
            $table->string('ai_impact_type', 50)->nullable()->after('ai_impact_category')
                ->comment('productivity_gain|error_rate_reduction|time_saving|cost_reduction|...');
            $table->decimal('baseline_value', 12, 4)->nullable()->after('ai_impact_type')
                ->comment('Giá trị trước khi áp dụng AI');
            $table->decimal('investment_cost', 15, 2)->nullable()->after('baseline_value')
                ->comment('Chi phí đầu tư để đạt KPI');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_goals', function (Blueprint $table) {
            $table->dropColumn([
                'ai_impact_category',
                'ai_impact_type',
                'baseline_value',
                'investment_cost',
            ]);
        });
    }
};

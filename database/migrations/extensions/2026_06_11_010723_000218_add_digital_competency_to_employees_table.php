<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('digital_competency_score', 5, 2)->nullable()->after('notes')
                ->comment('Điểm TDWCF tổng hợp — 0.00 đến 100.00');

            $table->string('digital_maturity_level', 64)->nullable()->after('digital_competency_score')
                ->comment('level_code: DIGITAL_BEGINNER ... DIGITAL_LEADER');

            $table->unsignedBigInteger('latest_assessment_result_id')->nullable()->after('digital_maturity_level')
                ->comment('FK tới assessment_results — kết quả TDWCF mới nhất');

            $table->timestamp('last_assessed_at')->nullable()->after('latest_assessment_result_id');

            $table->index('digital_maturity_level', 'idx_emp_digital_level');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_emp_digital_level');
            $table->dropColumn([
                'digital_competency_score',
                'digital_maturity_level',
                'latest_assessment_result_id',
                'last_assessed_at',
            ]);
        });
    }
};

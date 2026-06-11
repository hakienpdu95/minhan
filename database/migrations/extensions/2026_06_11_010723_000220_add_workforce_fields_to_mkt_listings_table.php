<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mkt_listings', function (Blueprint $table) {
            $table->decimal('required_workforce_score', 5, 2)->nullable()
                ->comment('Điểm TDWCF tối thiểu yêu cầu — 0–100');
            $table->string('required_cert_level', 30)->nullable()
                ->comment('FOUNDATION|PRACTITIONER|PROFESSIONAL|LEADER');
            $table->decimal('required_ai_readiness_score', 5, 2)->nullable()
                ->comment('AI Readiness Score tối thiểu');
            $table->string('required_cert_type_code', 30)->nullable()
                ->comment('AI_SALES|AI_HR|AI_FINANCE|... — loại chứng nhận yêu cầu');
        });
    }

    public function down(): void
    {
        Schema::table('mkt_listings', function (Blueprint $table) {
            $table->dropColumn([
                'required_workforce_score',
                'required_cert_level',
                'required_ai_readiness_score',
                'required_cert_type_code',
            ]);
        });
    }
};

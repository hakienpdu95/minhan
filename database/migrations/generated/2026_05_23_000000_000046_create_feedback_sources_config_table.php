<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_sources_config', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30)->unique()
                ->comment('admin_review | observed_outcome | user_self_report');
            $table->decimal('trust_weight', 4, 2)
                ->comment('Hệ số tin cậy khi tuning: admin_review=1.0, observed=0.7, self_report=0.4');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_sources_config');
    }
};

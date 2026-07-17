<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('success_reviews')) {
            return;
        }

        Schema::create('success_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_response_id')->nullable()->constrained('survey_responses')->nullOnDelete();
            $table->unsignedTinyInteger('csat_score')->nullable();
            $table->unsignedTinyInteger('nps_score')->nullable();
            $table->dateTime('follow_up_at')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->dateTime('followed_up_at')->nullable();
            $table->string('renewal_status', 20)->default('none');
            $table->text('renewal_note')->nullable();
            $table->foreignId('new_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index(['business_project_id', 'follow_up_at']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('success_reviews');
    }
};
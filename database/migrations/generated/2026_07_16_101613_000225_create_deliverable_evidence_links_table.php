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
        if (Schema::hasTable('deliverable_evidence_links')) {
            return;
        }

        Schema::create('deliverable_evidence_links', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('deliverable_id')->constrained('deliverables')->cascadeOnDelete();
            $table->foreignId('evidence_id')->constrained('deliverables')->cascadeOnDelete();
            $table->enum('evidence_type', ['interview', 'observation', 'document_review', 'data_review', 'task', 'metric'])->default('document_review');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();
            

            // Indexes
            $table->unique(['deliverable_id', 'evidence_id']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_evidence_links');
    }
};
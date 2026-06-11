<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sandbox_session_id')->unique();
            $table->text('submitted_content')->comment('Output thực tế của người dùng');
            $table->string('ai_tools_used', 300)->nullable()->comment('pipe-delimited');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('sandbox_session_id')->references('id')->on('sandbox_sessions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sandbox_submissions');
    }
};

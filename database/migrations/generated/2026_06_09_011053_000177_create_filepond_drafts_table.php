<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filepond_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('context_type')->nullable()->comment('Entity type that will receive the media after form save');
            $table->unsignedBigInteger('context_id')->nullable()->comment('Entity ID that will receive the media after form save');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filepond_drafts');
    }
};

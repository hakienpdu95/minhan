<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_environments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->unsignedBigInteger('organization_id')->nullable()->comment('null = global template');
            $table->string('env_code', 50)->unique();
            $table->string('name', 200);
            $table->string('type', 30)->comment('office|data|sales|hr|workflow|leadership');
            $table->unsignedTinyInteger('tier')->default(1)->comment('1=Foundation 2=Practitioner 3=Professional 4=Leader 5=Expert');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'tier'], 'idx_sandenv_type_tier');
            $table->index('organization_id', 'idx_sandenv_org');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sandbox_environments');
    }
};

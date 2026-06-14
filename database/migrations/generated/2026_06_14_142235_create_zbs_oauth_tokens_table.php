<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zbs_oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('app_id', 50)->unique()
                ->comment('Zalo App ID — one row per OA');
            $table->text('access_token');
            $table->timestamp('access_token_expires_at');
            $table->text('refresh_token');
            $table->timestamp('refresh_token_expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zbs_oauth_tokens');
    }
};

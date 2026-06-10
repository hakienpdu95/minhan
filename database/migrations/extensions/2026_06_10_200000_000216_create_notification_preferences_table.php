<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('event_type', 100);
            $table->boolean('channel_db')->default(true);
            $table->boolean('channel_mail')->default(false);
            $table->boolean('channel_push')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'organization_id', 'event_type'], 'notif_pref_user_org_type_unique');
            $table->foreign('user_id', 'notif_pref_user_id_fk')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};

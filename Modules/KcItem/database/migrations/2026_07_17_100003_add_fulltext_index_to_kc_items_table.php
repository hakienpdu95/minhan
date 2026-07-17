<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kc_items', function (Blueprint $table) {
            $table->fullText(['title', 'summary', 'content'], 'kc_items_title_summary_content_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('kc_items', function (Blueprint $table) {
            $table->dropFullText('kc_items_title_summary_content_fulltext');
        });
    }
};

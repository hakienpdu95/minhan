<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_phases', function (Blueprint $table) {
            $table->string('band_code', 50)->nullable()
                ->comment('Gắn phase theo score_band.band_code — mới theo spec. NULL = dùng maturity_level (cũ)')
                ->after('maturity_level');
        });

        // Sync band_code = maturity_level cho dữ liệu hiện có
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE roadmap_phases SET band_code = maturity_level WHERE band_code IS NULL AND maturity_level IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('roadmap_phases', function (Blueprint $table) {
            $table->dropColumn('band_code');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('result_job_positions');
        Schema::dropIfExists('job_positions');
    }

    public function down(): void
    {
        // Intentionally not restored — job positions feature has been removed
    }
};

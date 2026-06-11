<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_criteria', function (Blueprint $table) {
            $table->string('tdwcf_domain_code', 50)->nullable()->after('criteria_name')
                ->comment('D1_DIGITAL_LITERACY...D6_PERFORMANCE — ánh xạ tiêu chí về TDWCF domain');
        });
    }

    public function down(): void
    {
        Schema::table('review_criteria', function (Blueprint $table) {
            $table->dropColumn('tdwcf_domain_code');
        });
    }
};

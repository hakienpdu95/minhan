<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->string('gateway', 32)->nullable()->after('idempotent_key');
            $table->string('invoice_type', 32)->default('renewal')->after('gateway');
            $table->unsignedBigInteger('new_plan_id')->nullable()->after('invoice_type');

            $table->index('new_plan_id', 'idx_inv_new_plan');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->dropIndex('idx_inv_new_plan');
            $table->dropColumn(['gateway', 'invoice_type', 'new_plan_id']);
        });
    }
};

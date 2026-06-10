<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('subscription_invoices')->cascadeOnDelete();
            $table->string('gateway', 32);
            $table->string('direction', 16)->default('inbound'); // inbound=webhook, outbound=initiated by us
            $table->string('status', 32)->default('pending');    // pending, confirmed, failed, duplicate
            $table->string('gateway_ref', 191)->nullable();      // gateway's own transaction ID
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('raw_payload')->nullable();             // full JSON for audit
            $table->string('ip_addr', 45)->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'gateway', 'created_at'], 'idx_ptxn_org');
            $table->index('invoice_id', 'idx_ptxn_inv');
            $table->index('gateway_ref', 'idx_ptxn_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

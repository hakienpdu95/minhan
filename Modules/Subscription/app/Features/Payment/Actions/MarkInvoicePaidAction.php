<?php

namespace Modules\Subscription\Features\Payment\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Subscription\Enums\InvoiceStatus;
use Modules\Subscription\Features\Payment\Events\InvoicePaid;
use Modules\Subscription\Models\SubscriptionInvoice;

class MarkInvoicePaidAction
{
    use AsAction;

    public function handle(
        SubscriptionInvoice $invoice,
        string $paymentRef,
        string $paymentMethod,
    ): SubscriptionInvoice {
        // Idempotent — safe to call multiple times (IPN can arrive more than once)
        if ($invoice->isPaid()) {
            return $invoice;
        }

        DB::transaction(function () use ($invoice, $paymentRef, $paymentMethod): void {
            $invoice->update([
                'status'         => InvoiceStatus::Paid,
                'paid_at'        => now(),
                'payment_ref'    => $paymentRef,
                'payment_method' => $paymentMethod,
            ]);
        });

        // Dispatch outside the transaction so the listener reads the committed data
        InvoicePaid::dispatch($invoice->fresh());

        return $invoice->fresh();
    }
}

<?php

namespace Modules\Subscription\Features\Payment\Services;

use App\Shared\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;
use Modules\Subscription\Models\SubscriptionInvoice;

/**
 * Generates unique sequential invoice numbers per org.
 * Format: INV-YYYY-{org_id}-{seq:04d}
 *
 * The lockForUpdate() ensures atomicity under concurrent requests.
 * Must be called inside the same DB transaction that creates the invoice.
 */
final class InvoiceNumberService
{
    public function generate(Organization $org): string
    {
        return DB::transaction(function () use ($org): string {
            // Lock existing invoice rows for this org to serialize concurrent inserts
            $count = SubscriptionInvoice::withoutTenant()
                ->lockForUpdate()
                ->where('organization_id', $org->id)
                ->count();

            $seq  = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $year = now()->year;

            return "INV-{$year}-{$org->id}-{$seq}";
        });
    }
}

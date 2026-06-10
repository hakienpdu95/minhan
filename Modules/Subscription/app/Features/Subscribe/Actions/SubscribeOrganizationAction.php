<?php

namespace Modules\Subscription\Features\Subscribe\Actions;

use App\Shared\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Subscription\Enums\ChangeType;
use Modules\Subscription\Features\FeatureGate\Support\SubscriptionContext;
use Modules\Subscription\Features\Subscribe\Data\SubscribeData;
use Modules\Subscription\Features\Subscribe\Events\SubscriptionCreated;
use Modules\Subscription\Models\SubscriptionChange;

class SubscribeOrganizationAction
{
    use AsAction;

    public function handle(Organization $org, SubscribeData $data): Subscription
    {
        // Idempotency check
        if ($data->idempotentKey) {
            $existing = $org->planSubscriptions()
                ->where('slug', 'like', '%' . $data->idempotentKey . '%')
                ->first();
            if ($existing) return $existing;
        }

        $plan = Plan::findOrFail($data->planId);

        $subscription = DB::transaction(function () use ($org, $plan, $data): Subscription {
            $current = $org->planSubscription('main');
            if ($current && $current->active()) {
                $current->cancel();
            }

            $subscription = $org->newPlanSubscription(
                $data->slug ?? 'main',
                $plan,
                $data->startDate
            );

            SubscriptionChange::create([
                'organization_id' => $org->id,
                'subscription_id' => $subscription->id,
                'from_plan_id'    => $current?->plan_id,
                'to_plan_id'      => $plan->id,
                'change_type'     => ChangeType::Subscribe,
                'effective_at'    => now(),
            ]);

            return $subscription;
        });

        SubscriptionContext::flush($org->id);

        SubscriptionCreated::dispatch($org, $subscription, $plan);

        return $subscription;
    }
}

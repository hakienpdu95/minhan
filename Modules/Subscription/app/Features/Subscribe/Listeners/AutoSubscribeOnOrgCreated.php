<?php

namespace Modules\Subscription\Features\Subscribe\Listeners;

use Laravelcm\Subscriptions\Models\Plan;
use Modules\Organization\Events\OrganizationCreated;
use Modules\Subscription\Features\Subscribe\Actions\SubscribeOrganizationAction;
use Modules\Subscription\Features\Subscribe\Data\SubscribeData;

class AutoSubscribeOnOrgCreated
{
    public function handle(OrganizationCreated $event): void
    {
        $starterPlan = Plan::where('slug', config('subscription.default_plan', 'starter'))
            ->where('is_active', true)
            ->first();

        if (!$starterPlan) return;

        SubscribeOrganizationAction::run($event->organization, new SubscribeData(
            planId:        $starterPlan->id,
            idempotentKey: 'auto-' . $event->organization->id,
        ));
    }
}

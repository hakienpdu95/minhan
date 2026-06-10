<?php

namespace Modules\Subscription\Features\FeatureGate\Http\Middleware;

use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Modules\Subscription\Features\FeatureGate\Support\SubscriptionContext;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // Webhook routes are server-to-server calls — no tenant or subscription context
        if ($request->is('billing/webhook/*')) {
            return $next($request);
        }

        if (!TenantContext::isSet()) {
            return $next($request);
        }

        $org = TenantContext::resolve();
        $ctx = SubscriptionContext::boot($org);

        if (!$ctx->isActive() && !$ctx->isGracePeriod()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'subscription_expired',
                    'message' => 'Subscription đã hết hạn.',
                ], 402);
            }
            return redirect()->route('subscription.portal.billing')
                ->with('warning', 'Subscription đã hết hạn. Vui lòng gia hạn.');
        }

        return $next($request);
    }
}

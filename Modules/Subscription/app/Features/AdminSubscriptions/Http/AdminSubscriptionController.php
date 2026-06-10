<?php

namespace Modules\Subscription\Features\AdminSubscriptions\Http;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;
use Modules\Subscription\Features\FeatureGate\Support\SubscriptionContext;
use Modules\Subscription\Features\Subscribe\Actions\SubscribeOrganizationAction;
use Modules\Subscription\Features\Subscribe\Data\SubscribeData;
use Modules\Subscription\Models\OrganizationFeatureOverride;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $planId  = $request->input('plan_id');

        $orgs = Organization::query()
            ->with(['planSubscriptions' => function ($q) {
                $q->with('plan')->latest('starts_at')->limit(1);
            }])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($planId, function ($q) use ($planId) {
                $q->whereHas('planSubscriptions', fn ($s) => $s->where('plan_id', $planId));
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $plans = Plan::orderBy('sort_order')->get(['id', 'name', 'slug']);

        return view('subscription::admin.subscriptions.index', compact('orgs', 'plans', 'search', 'planId'));
    }

    public function assign(Request $request, Organization $organization, SubscribeOrganizationAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id'    => 'required|exists:plans,id',
            'start_date' => 'nullable|date',
            'reason'     => 'nullable|string|max:255',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $action->handle($organization, new SubscribeData(
            planId:    $plan->id,
            startDate: $validated['start_date'] ? new \Carbon\Carbon($validated['start_date']) : null,
        ));

        SubscriptionContext::flush($organization->id);

        return back()->with('success', "Đã gán plan \"{$plan->name}\" cho \"{$organization->name}\".");
    }

    public function override(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'feature_slug'   => 'required|string|max:128',
            'value'          => 'required|string|max:255',
            'override_reason'=> 'nullable|string|max:255',
            'expires_at'     => 'nullable|date',
        ]);

        OrganizationFeatureOverride::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'feature_slug'    => $validated['feature_slug'],
            ],
            [
                'value'           => $validated['value'],
                'override_reason' => $validated['override_reason'] ?? null,
                'expires_at'      => $validated['expires_at']      ?? null,
                'created_by'      => $request->user()->id,
            ]
        );

        SubscriptionContext::flush($organization->id);

        return back()->with('success', "Override feature \"{$validated['feature_slug']}\" đã được lưu.");
    }
}

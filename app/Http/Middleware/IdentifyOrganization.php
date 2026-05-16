<?php

namespace App\Http\Middleware;

use App\Shared\Tenancy\Models\Organization;
use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant (Organization) from the request and populates TenantContext.
 *
 * Resolution order:
 *   1. Subdomain:          acme.minhan.app → slug = 'acme'
 *   2. Request header:     X-Organization-ID: 42
 *   3. Auth user's org:    auth()->user()->organization_id
 *   4. Session:            organization_id stored from previous request
 *
 * Non-blocking: if no organization is resolved, the request continues as a guest/public.
 * Routes that require a tenant must use the 'tenant' middleware alias to assert context.
 */
class IdentifyOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $this->resolveFromSubdomain($request)
            ?? $this->resolveFromHeader($request)
            ?? $this->resolveFromAuthUser($request)
            ?? $this->resolveFromSession($request);

        if ($organization?->isActive()) {
            TenantContext::set($organization);
            $request->session()->put('organization_id', $organization->id);
        }

        return $next($request);
    }

    private function resolveFromSubdomain(Request $request): ?Organization
    {
        $host = $request->getHost();
        $appDomain = config('app.domain', parse_url(config('app.url'), PHP_URL_HOST));

        if (!$appDomain || !str_ends_with($host, '.' . $appDomain)) {
            return null;
        }

        $slug = str_replace('.' . $appDomain, '', $host);

        if (empty($slug) || $slug === $appDomain) {
            return null;
        }

        return $this->findBySlug($slug);
    }

    private function resolveFromHeader(Request $request): ?Organization
    {
        $orgId = $request->header('X-Organization-ID');

        if (!$orgId || !is_numeric($orgId)) {
            return null;
        }

        return $this->findById((int) $orgId);
    }

    private function resolveFromAuthUser(Request $request): ?Organization
    {
        $user = $request->user();

        if (!$user || !$user->organization_id) {
            return null;
        }

        return $this->findById($user->organization_id);
    }

    private function resolveFromSession(Request $request): ?Organization
    {
        if (!$request->hasSession()) {
            return null;
        }

        $orgId = $request->session()->get('organization_id');

        if (!$orgId) {
            return null;
        }

        return $this->findById((int) $orgId);
    }

    private function findBySlug(string $slug): ?Organization
    {
        return Cache::remember(
            "org.slug.{$slug}",
            now()->addMinutes(5),
            fn () => Organization::active()->bySlug($slug)->first()
        );
    }

    private function findById(int $id): ?Organization
    {
        return Cache::remember(
            "org.id.{$id}",
            now()->addMinutes(5),
            fn () => Organization::find($id)
        );
    }
}

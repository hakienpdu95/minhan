<?php

namespace App\Http\Middleware;

use App\Foundation\Vertical\OrganizationVertical;
use App\Foundation\VerticalRegistry;
use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVertical
{
    public function handle(Request $request, Closure $next, ?string $code = null): Response
    {
        // Fall back to route parameter when no explicit code is passed (e.g. ->middleware('vertical'))
        $code ??= $request->route('vertical');

        if (! $code) {
            abort(400, 'Vertical code không được xác định.');
        }

        $vertical = VerticalRegistry::resolve($code);

        if (! $vertical) {
            abort(404, "Vertical '{$code}' không tồn tại.");
        }

        $active = OrganizationVertical::withoutTenant()
            ->where('organization_id', TenantContext::getOrganizationId())
            ->where('vertical_code', $code)
            ->where('status', 'active')
            ->exists();

        if (! $active) {
            abort(403, "Vertical '{$code}' chưa được kích hoạt cho tổ chức này.");
        }

        $request->attributes->set('_vertical', $vertical);

        return $next($request);
    }
}

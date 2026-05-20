<?php

namespace Modules\User\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Http\Resources\UserListResource;
use Modules\User\Queries\ListUsersHandler;
use Modules\User\Queries\ListUsersQuery;

class UserApiController extends Controller
{
    public function index(Request $request, ListUsersHandler $handler): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $sort      = $request->input('sort', []);
        $sortField = $sort[0]['field'] ?? 'created_at';
        $sortDir   = $sort[0]['dir']   ?? 'desc';

        // Admin sees all (or filters by chosen org); non-admin is locked to their org
        $isAdmin = $request->user()->hasAnyRole(['super-admin', 'System_Admin']);

        if ($isAdmin) {
            $orgId = $request->filled('organization_id')
                ? (int) $request->input('organization_id')
                : null;
        } else {
            $orgId = TenantContext::getOrganizationId()
                  ?? $request->user()->organization_id;
        }

        $query = new ListUsersQuery(
            page:           max(1, $request->integer('page', 1)),
            perPage:        min(100, max(5, $request->integer('size', 25))),
            sortField:      $sortField,
            sortDir:        $sortDir,
            search:         $request->input('search'),
            organizationId: $orgId,
            role:           $request->input('role'),
            status:         $request->input('status'),
            dateFrom:       $request->input('date_from'),
            dateTo:         $request->input('date_to'),
        );

        $paginator = $handler->handle($query);

        return response()->json([
            'data'      => UserListResource::collection($paginator->items()),
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
        ]);
    }
}

<?php

namespace Modules\Organization\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Organization\Http\Resources\OrganizationListResource;
use Modules\Organization\Queries\ListOrganizationsHandler;
use Modules\Organization\Queries\ListOrganizationsQuery;

class OrganizationApiController extends Controller
{
    public function index(Request $request, ListOrganizationsHandler $handler): JsonResponse
    {
        $this->authorize('viewAny', \Modules\Organization\Models\Organization::class);

        $sort      = $request->input('sort', []);
        $sortField = $sort[0]['field'] ?? 'created_at';
        $sortDir   = $sort[0]['dir']   ?? 'desc';

        $query = new ListOrganizationsQuery(
            page:         max(1, $request->integer('page', 1)),
            perPage:      min(100, max(5, $request->integer('size', 25))),
            sortField:    $sortField,
            sortDir:      $sortDir,
            search:       $request->input('search'),
            provinceCode: $request->input('province_code'),
            wardCode:     $request->input('ward_code'),
            dateFrom:     $request->input('date_from'),
            dateTo:       $request->input('date_to'),
            status:       $request->input('status'),
        );

        $paginator = $handler->handle($query);

        return response()->json([
            'data'      => OrganizationListResource::collection($paginator->items()),
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
        ]);
    }
}

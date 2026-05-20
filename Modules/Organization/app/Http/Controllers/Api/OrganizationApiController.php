<?php

namespace Modules\Organization\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Shared\Tenancy\Enums\OrganizationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Organization\Queries\ListOrganizationsHandler;
use Modules\Organization\Queries\ListOrganizationsQuery;

class OrganizationApiController extends Controller
{
    public function index(Request $request, ListOrganizationsHandler $handler): JsonResponse
    {
        $this->authorize('viewAny', \Modules\Organization\Models\Organization::class);

        // Tabulator sends sort as sort[0][field] / sort[0][dir]
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
        );

        $paginator = $handler->handle($query);

        return response()->json([
            'data'      => collect($paginator->items())->map($this->formatRow(...)),
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
        ]);
    }

    private function formatRow(\Modules\Organization\Models\Organization $org): array
    {
        $status = $org->status;

        return [
            'id'            => $org->id,
            'name'          => $org->name,
            'slug'          => $org->slug,
            'tax_code'      => $org->tax_code,
            'industry'      => $org->industry,
            'email'         => $org->email,
            'phone'         => $org->phone,
            'province_name' => $org->province?->name,
            'ward_name'     => $org->ward?->name,
            'members_count' => $org->members_count,
            'status'        => $status instanceof OrganizationStatus ? $status->value : $status,
            'status_label'  => $status instanceof OrganizationStatus ? $status->label() : $status,
            'created_at'    => $org->created_at?->format('d/m/Y'),
            'show_url'      => route('backend.organizations.show', $org),
            'edit_url'      => route('backend.organizations.edit', $org),
            'delete_url'    => route('backend.organizations.destroy', $org),
        ];
    }
}

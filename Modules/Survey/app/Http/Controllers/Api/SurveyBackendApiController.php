<?php

namespace Modules\Survey\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Survey\Http\Resources\SurveyListResource;
use Modules\Survey\Queries\ListSurveysHandler;
use Modules\Survey\Queries\ListSurveysQuery;

class SurveyBackendApiController extends Controller
{
    public function index(Request $request, ListSurveysHandler $handler): JsonResponse
    {
        $this->authorize('survey.view');

        $validated = $request->validate([
            'page'      => ['nullable', 'integer', 'min:1'],
            'size'      => ['nullable', 'integer', 'min:5', 'max:100'],
            'search'    => ['nullable', 'string', 'max:200'],
            'status'    => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        // sort[0] is sent by Tabulator as an array element — guard against non-array payloads.
        $sortRaw   = $request->input('sort.0');
        $sortField = is_array($sortRaw) ? (string) ($sortRaw['field'] ?? 'created_at') : 'created_at';
        $sortDir   = is_array($sortRaw) ? (string) ($sortRaw['dir']   ?? 'desc')       : 'desc';

        $status = isset($validated['status']) ? (int) $validated['status'] : null;

        $query = new ListSurveysQuery(
            page:      max(1, (int) ($validated['page'] ?? 1)),
            perPage:   min(100, max(5, (int) ($validated['size'] ?? 25))),
            sortField: $sortField,
            sortDir:   $sortDir,
            search:    $validated['search'] ?? null,
            status:    $status,
            dateFrom:  $validated['date_from'] ?? null,
            dateTo:    $validated['date_to']   ?? null,
        );

        $paginator = $handler->handle($query);

        return response()->json([
            'data'      => SurveyListResource::collection($paginator->items()),
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
        ]);
    }
}

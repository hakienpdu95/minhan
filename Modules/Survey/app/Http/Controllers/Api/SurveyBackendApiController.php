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

        $sort      = $request->input('sort', []);
        $sortField = $sort[0]['field'] ?? 'created_at';
        $sortDir   = $sort[0]['dir']   ?? 'desc';

        $status = $request->input('status');

        $query = new ListSurveysQuery(
            page:      max(1, $request->integer('page', 1)),
            perPage:   min(100, max(5, $request->integer('size', 25))),
            sortField: $sortField,
            sortDir:   $sortDir,
            search:    $request->input('search'),
            status:    ($status !== null && $status !== '') ? (int) $status : null,
            dateFrom:  $request->input('date_from'),
            dateTo:    $request->input('date_to'),
        );

        $paginator = $handler->handle($query);

        return response()->json([
            'data'      => SurveyListResource::collection($paginator->items()),
            'last_page' => $paginator->lastPage(),
            'total'     => $paginator->total(),
        ]);
    }
}

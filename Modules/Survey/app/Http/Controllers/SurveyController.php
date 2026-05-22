<?php

namespace Modules\Survey\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Survey\Actions\BuildSurveySchemaAction;
use Modules\Survey\Actions\ExportSurveyResponsesAction;
use Modules\Survey\Actions\SubmitSurveyAction;
use Modules\Survey\Http\Requests\SubmitSurveyRequest;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Services\SurveyStatsService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyController extends Controller
{
    public function schema(string $slug, BuildSurveySchemaAction $action): JsonResponse
    {
        return response()->json($action->handle($slug));
    }

    public function submit(
        string             $slug,
        SubmitSurveyRequest $request,
        SubmitSurveyAction  $action,
    ): JsonResponse {
        // Load by slug only — action kiểm tra status (draft/closed → 403)
        $survey = Survey::bySlug($slug)->firstOrFail();

        $responseId = $action->handle($survey, $request->toResponseData());

        return response()->json(['response_id' => $responseId], 201);
    }

    public function stats(string $slug, SurveyStatsService $service): JsonResponse
    {
        $survey = Survey::active()->bySlug($slug)->firstOrFail();

        return response()->json($service->forSurvey($survey));
    }

    public function responses(
        string                      $slug,
        Request                     $request,
        ExportSurveyResponsesAction $exportAction,
    ): JsonResponse|StreamedResponse {
        $survey = Survey::active()->bySlug($slug)->firstOrFail();

        if ($request->boolean('export') || $request->query('export') === 'xlsx') {
            return $exportAction->handle(
                $survey,
                $request->query('respondent_ref'),
                $request->query('from'),
                $request->query('to'),
            );
        }

        $responses = SurveyResponse::forSurvey($survey->id)
            ->complete()
            ->when($request->query('respondent_ref'), fn ($q) => $q->where('respondent_ref', $request->query('respondent_ref')))
            ->when($request->query('from'),           fn ($q) => $q->where('submitted_at', '>=', $request->query('from')))
            ->when($request->query('to'),             fn ($q) => $q->where('submitted_at', '<=', $request->query('to') . ' 23:59:59'))
            ->orderBy('submitted_at', 'desc')
            ->paginate(50);

        return response()->json($responses);
    }
}

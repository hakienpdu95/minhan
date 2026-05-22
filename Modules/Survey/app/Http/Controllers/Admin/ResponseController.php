<?php

namespace Modules\Survey\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Survey\Actions\ExportSurveyResponsesAction;
use Modules\Survey\Enums\ResponseStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Services\ResponseViewerService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseController extends Controller
{
    // Task 4.1 — danh sách responses + filter
    public function index(Request $request, Survey $survey)
    {
        $this->authorize('survey.view_responses');

        $query = SurveyResponse::forSurvey($survey->id)
            ->when($request->filled('respondent_ref'), fn ($q) =>
                $q->where('respondent_ref', 'like', '%' . $request->respondent_ref . '%')
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', (int) $request->status)
            )
            ->when($request->filled('from'), fn ($q) =>
                $q->where('submitted_at', '>=', $request->from . ' 00:00:00')
            )
            ->when($request->filled('to'), fn ($q) =>
                $q->where('submitted_at', '<=', $request->to . ' 23:59:59')
            )
            ->orderByDesc('submitted_at');

        $responses = $query->paginate(30)->withQueryString();

        $totalAll      = SurveyResponse::forSurvey($survey->id)->count();
        $totalComplete = SurveyResponse::forSurvey($survey->id)->complete()->count();

        return view('survey::responses.index', [
            'survey'        => $survey,
            'responses'     => $responses,
            'totalAll'      => $totalAll,
            'totalComplete' => $totalComplete,
            'filters'       => $request->only('respondent_ref', 'status', 'from', 'to'),
            'statuses'      => ResponseStatus::cases(),
        ]);
    }

    // Task 4.2 — xem chi tiết 1 response
    public function show(Survey $survey, SurveyResponse $response, ResponseViewerService $viewer)
    {
        $this->authorize('survey.view_responses');

        $this->checkOwnership($response, $survey);

        $data = $viewer->build($response);

        return view('survey::responses.show', [
            'survey'   => $survey,
            'response' => $response,
            'sections' => $data['sections'],
        ]);
    }

    // Task 4.6 — export Excel (dùng lại ExportSurveyResponsesAction đã có)
    public function export(Request $request, Survey $survey, ExportSurveyResponsesAction $action): StreamedResponse
    {
        $this->authorize('survey.export');

        return $action->handle(
            $survey,
            $request->query('respondent_ref'),
            $request->query('from'),
            $request->query('to'),
        );
    }

    // Task 4.3 — soft delete
    public function destroy(Survey $survey, SurveyResponse $response): RedirectResponse
    {
        $this->authorize('survey.view_responses');

        $this->checkOwnership($response, $survey);

        $response->delete();

        return redirect()
            ->route('backend.surveys.responses.index', $survey)
            ->with('success', 'Response đã được xóa.');
    }

    private function checkOwnership(SurveyResponse $response, Survey $survey): void
    {
        if ($response->survey_id !== $survey->id) {
            abort(404);
        }
    }
}

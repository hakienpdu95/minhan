<?php

namespace Modules\Survey\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Survey\Actions\ExportSurveyResponsesAction;
use Modules\Survey\Enums\ResponseStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Services\QueryAuditService;
use Modules\Survey\Services\ResponseViewerService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseController extends Controller
{
    public function index(Request $request, Survey $survey)
    {
        $this->authorize('survey.view_responses');

        // Cursor pagination: select chỉ cột cần thiết, tránh select *
        // QueryAuditService::measure('ResponseList', fn() => ...) có thể dùng để audit khi debug
        $responses = SurveyResponse::forSurvey($survey->id)
            ->select(['id', 'respondent_ref', 'respondent_ip', 'status', 'submitted_at'])
            ->when($request->filled('respondent_ref'), fn ($q) =>
                $q->where('respondent_ref', 'like', '%' . $request->respondent_ref . '%')
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', (int) $request->status)
            )
            ->when($request->filled('completed_at'), fn ($q) =>
                $q->whereDate('submitted_at', $request->completed_at)
            )
            ->when($request->filled('from'), fn ($q) =>
                $q->where('submitted_at', '>=', $request->from . ' 00:00:00')
            )
            ->when($request->filled('to'), fn ($q) =>
                $q->where('submitted_at', '<=', $request->to . ' 23:59:59')
            )
            ->orderByDesc('submitted_at')
            ->orderByDesc('id') // tiebreaker để cursor pagination ổn định
            ->cursorPaginate(50)
            ->withQueryString();

        $totalAll      = SurveyResponse::forSurvey($survey->id)->count();
        $totalComplete = SurveyResponse::forSurvey($survey->id)->complete()->count();

        return view('survey::responses.index', [
            'survey'        => $survey,
            'responses'     => $responses,
            'totalAll'      => $totalAll,
            'totalComplete' => $totalComplete,
            'filters'       => $request->only('respondent_ref', 'status', 'completed_at', 'from', 'to'),
            'statuses'      => ResponseStatus::cases(),
        ]);
    }

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

    public function export(Request $request, Survey $survey, ExportSurveyResponsesAction $action): StreamedResponse|RedirectResponse
    {
        $this->authorize('survey.export');

        $result = $action->handle(
            $survey,
            $request->query('respondent_ref'),
            $request->query('from'),
            $request->query('to'),
        );

        if ($result === null) {
            // Job đã được dispatch (> 10.000 rows)
            $key = session('export_queued_key');
            return redirect()
                ->route('backend.surveys.responses.index', $survey)
                ->with('info', "Export đang được xử lý trong nền ({$survey->responses()->complete()->count()} responses). Tải về tại: "
                    . route('backend.surveys.responses.export.download', [$survey, $key]));
        }

        return $result;
    }

    public function downloadExport(Survey $survey, string $key): StreamedResponse|RedirectResponse
    {
        $this->authorize('survey.export');

        $info = Cache::store('redis')->get("survey:export:{$key}");

        if (!$info || !file_exists($info['path'])) {
            return redirect()
                ->route('backend.surveys.responses.index', $survey)
                ->with('error', 'File export chưa sẵn sàng hoặc đã hết hạn. Vui lòng export lại.');
        }

        return response()->download($info['path'], $info['filename'])->deleteFileAfterSend(true);
    }

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

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

        $filters = $request->validate([
            'respondent_ref' => ['nullable', 'string', 'max:190'],
            'status'         => ['nullable', 'integer', 'in:0,1'],
            'from'           => ['nullable', 'date_format:Y-m-d'],
            'to'             => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        // Single query for both totals — saves one DB roundtrip on every page load.
        // SoftDeletes global scope is applied by the Eloquent model automatically.
        $counts = SurveyResponse::forSurvey($survey->id)
            ->selectRaw(
                'COUNT(*) as total_all, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_complete',
                [ResponseStatus::Complete->value]
            )
            ->first();

        $totalAll      = (int) ($counts->total_all      ?? 0);
        $totalComplete = (int) ($counts->total_complete ?? 0);

        $responses = SurveyResponse::forSurvey($survey->id)
            ->select(['id', 'respondent_ref', 'respondent_ip', 'status', 'submitted_at'])
            ->when(isset($filters['respondent_ref']), fn ($q) =>
                // Prefix LIKE only — a leading wildcard disables the respondent_ref index at 500k rows.
                $q->where('respondent_ref', 'like', $filters['respondent_ref'] . '%')
            )
            ->when(isset($filters['status']), fn ($q) =>
                $q->where('status', $filters['status'])
            )
            ->when(isset($filters['from']), fn ($q) =>
                $q->where('submitted_at', '>=', $filters['from'] . ' 00:00:00')
            )
            ->when(isset($filters['to']), fn ($q) =>
                $q->where('submitted_at', '<=', $filters['to'] . ' 23:59:59')
            )
            ->orderByDesc('submitted_at')
            ->orderByDesc('id') // tiebreaker — InnoDB PK is implicit in all secondary indexes
            ->cursorPaginate(50)
            ->withQueryString();

        return view('survey::responses.index', [
            'survey'        => $survey,
            'responses'     => $responses,
            'totalAll'      => $totalAll,
            'totalComplete' => $totalComplete,
            'filters'       => array_filter($filters, fn ($v) => $v !== null),
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

        if (is_array($result)) {
            // Job đã được dispatch (> 10.000 rows)
            $key = $result['queued_key'];
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

        // Reject non-UUID keys before constructing the Redis lookup key.
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $key)) {
            abort(404);
        }

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

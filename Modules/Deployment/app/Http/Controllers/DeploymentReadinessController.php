<?php

namespace Modules\Deployment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Deployment\Actions\CloneSurveyAction;
use Modules\Deployment\Actions\SubmitReadinessAction;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Deployment\Services\GapAnalysisService;
use Modules\Deployment\Services\ReadinessScoreService;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveySection;

class DeploymentReadinessController extends Controller
{
    // ── Start assessment ──────────────────────────────────────────────────────

    public function start(Request $request, DeploymentTarget $target, CloneSurveyAction $action): RedirectResponse
    {
        $this->authorize('update', $target);

        $action->handle($target);

        return redirect()->route('deployment.readiness.fill', [
            'vertical' => $request->attributes->get('_vertical')->code(),
            'target'   => $target->id,
        ])->with('info', 'Đã khởi tạo bộ câu hỏi đánh giá sẵn sàng.');
    }

    // ── Fill form ─────────────────────────────────────────────────────────────

    public function fill(Request $request, DeploymentTarget $target): View
    {
        $this->authorize('update', $target);

        if (! $target->readiness_response_id) {
            (new CloneSurveyAction)->handle($target);
            $target->refresh();
        }

        $vertical = $request->attributes->get('_vertical');
        $response = $target->readinessResponse;
        $surveyId = $response->survey_id;

        // Load sections with their fields for the form
        $sections = SurveySection::where('survey_id', $surveyId)
            ->with(['fields' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        // Load previously answered values keyed by field_id
        $existingAnswers = $response->answers()
            ->whereNotNull('value_number')
            ->pluck('value_number', 'field_id');

        $orgName = $target->targetOrganization?->name ?? "Target #{$target->id}";

        return view('deployment::readiness.fill', compact(
            'vertical', 'target', 'sections', 'existingAnswers', 'orgName'
        ));
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit(Request $request, DeploymentTarget $target, SubmitReadinessAction $action): RedirectResponse
    {
        $this->authorize('update', $target);

        $vertical = $request->attributes->get('_vertical');
        $answers  = $request->input('answers', []);

        // answers[field_id] = rating
        $result = $action->handle($target, array_map('intval', $answers));

        return redirect()
            ->route('deployment.readiness.show', ['vertical' => $vertical->code(), 'target' => $target->id])
            ->with('success', "Đánh giá hoàn tất. Điểm sẵn sàng: {$result['score']}/100 — {$result['band']}");
    }

    // ── Show results ──────────────────────────────────────────────────────────

    public function show(Request $request, DeploymentTarget $target): View
    {
        $this->authorize('view', $target);

        $vertical = $request->attributes->get('_vertical');
        $target->load(['targetOrganization', 'readinessResponse.answers.field.section']);

        $result = null;
        $gaps   = [];

        if ($target->readiness_response_id) {
            $result = (new ReadinessScoreService)->compute($target->readinessResponse);
            $gaps   = (new GapAnalysisService)->analyze($result['domains']);
        }

        return view('deployment::readiness.show', compact(
            'vertical', 'target', 'result', 'gaps'
        ));
    }

    // ── Score API ─────────────────────────────────────────────────────────────

    public function score(DeploymentTarget $target): JsonResponse
    {
        $this->authorize('view', $target);

        if (! $target->readiness_response_id) {
            return response()->json(['score' => null, 'band' => 'Chưa đánh giá']);
        }

        $result = (new ReadinessScoreService)->compute($target->readinessResponse);

        return response()->json($result);
    }
}

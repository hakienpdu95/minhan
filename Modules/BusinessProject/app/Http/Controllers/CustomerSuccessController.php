<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\CustomerSuccess\AttachSuccessReviewSurveyAction;
use Modules\BusinessProject\Actions\CustomerSuccess\CreateLeadFromOpportunityAction;
use Modules\BusinessProject\Actions\CustomerSuccess\EnsureCsatNpsSurveyAction;
use Modules\BusinessProject\Actions\CustomerSuccess\MarkFollowUpDoneAction;
use Modules\BusinessProject\Actions\CustomerSuccess\StoreSuccessReviewNoteAction;
use Modules\BusinessProject\Data\Requests\AttachSuccessReviewSurveyData;
use Modules\BusinessProject\Data\Requests\CreateLeadFromOpportunityData;
use Modules\BusinessProject\Data\Requests\StoreSuccessReviewNoteData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\SuccessReview;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Modules\Survey\Enums\ResponseStatus;
use Modules\Survey\Models\SurveyResponse;

/**
 * Giai đoạn 8 — Customer Success Workspace ("vòng đời không kết thúc ở Closed"): CSAT/NPS (gắn
 * SurveyResponse có sẵn — KHÔNG xây form khảo sát mới), Follow-up định kỳ, Renewal, New
 * Opportunity -> Tạo Lead mới (khép vòng lặp toàn hệ thống).
 */
class CustomerSuccessController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $successReviews = $businessProject->successReviews()
            ->with(['surveyResponse', 'newLead', 'createdBy'])
            ->orderByDesc('id')
            ->get();

        $csatNpsSurvey = EnsureCsatNpsSurveyAction::run($businessProject->organization_id);

        $alreadyAttachedResponseIds = $businessProject->successReviews()
            ->whereNotNull('survey_response_id')
            ->pluck('survey_response_id');

        $attachableSurveyResponses = SurveyResponse::where('survey_id', $csatNpsSurvey->id)
            ->where('status', ResponseStatus::Complete->value)
            ->whereNotIn('id', $alreadyAttachedResponseIds)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'respondent_ref', 'submitted_at']);

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        return view('businessproject::business-projects.customer-success.show', [
            'businessProject' => $businessProject,
            'successReviews' => $successReviews,
            'csatNpsSurvey' => $csatNpsSurvey,
            'attachableSurveyResponses' => $attachableSurveyResponses,
            'gateResult' => $gateResult,
        ]);
    }

    public function attachSurvey(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageCustomerSuccess', $businessProject);

        $data = AttachSuccessReviewSurveyData::validateAndCreate($request->all());
        AttachSuccessReviewSurveyAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã gắn kết quả khảo sát CSAT/NPS vào Business Project.');
    }

    public function storeNote(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageCustomerSuccess', $businessProject);

        $data = StoreSuccessReviewNoteData::validateAndCreate($request->all());
        StoreSuccessReviewNoteAction::run($businessProject, $data);

        return $this->back($businessProject, 'Đã ghi nhận follow-up/renewal.');
    }

    public function markFollowUpDone(BusinessProject $businessProject, SuccessReview $successReview): RedirectResponse
    {
        $this->authorize('manageCustomerSuccess', $businessProject);

        MarkFollowUpDoneAction::run($successReview);

        return $this->back($businessProject, 'Đã đánh dấu hoàn thành follow-up.');
    }

    public function createLead(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageCustomerSuccess', $businessProject);

        $data = CreateLeadFromOpportunityData::validateAndCreate($request->all());
        $lead = CreateLeadFromOpportunityAction::run($businessProject, $data);

        return $this->back($businessProject, "Đã tạo Lead mới \"{$lead->title}\" — khép vòng lặp về module Lead.");
    }

    private function back(BusinessProject $businessProject, string $message): RedirectResponse
    {
        return redirect()
            ->route('backend.business-projects.customer-success.show', $businessProject)
            ->with('success', $message);
    }
}

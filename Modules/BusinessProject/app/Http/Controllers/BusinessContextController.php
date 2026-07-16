<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\BusinessProject\Actions\Context\CreateBusinessContextAction;
use Modules\BusinessProject\Actions\Context\UpdateBusinessContextAction;
use Modules\BusinessProject\Actions\Deliverable\ApproveDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\RejectDeliverableAction;
use Modules\BusinessProject\Actions\Deliverable\SubmitDeliverableForApprovalAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessContextData;
use Modules\BusinessProject\Models\BusinessProject;

class BusinessContextController extends Controller
{
    public function store(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('update', $businessProject);

        $data = StoreBusinessContextData::validateAndCreate($request->all());

        CreateBusinessContextAction::run($businessProject, $data);

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã tạo Business Context.');
    }

    public function update(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('update', $businessProject);

        $context = $businessProject->context ?? abort(404);

        $data = StoreBusinessContextData::validateAndCreate($request->all());

        UpdateBusinessContextAction::run($context, $data);

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã cập nhật Business Context.');
    }

    public function submit(BusinessProject $businessProject): RedirectResponse
    {
        $context = $businessProject->context ?? abort(404);
        $deliverable = $context->deliverable ?? abort(404);

        $this->authorize('manage', $deliverable);

        SubmitDeliverableForApprovalAction::run($deliverable);

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã gửi Business Context Report để phê duyệt.');
    }

    public function approve(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $businessProject->context?->deliverable ?? abort(404);

        ApproveDeliverableAction::run($deliverable, $request->input('comment'));

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã phê duyệt Business Context Report.');
    }

    public function reject(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $deliverable = $businessProject->context?->deliverable ?? abort(404);

        RejectDeliverableAction::run($deliverable, $request->input('comment'));

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã từ chối Business Context Report.');
    }
}

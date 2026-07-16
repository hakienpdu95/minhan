<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Discovery\AddDiscoveryRecordAction;
use Modules\BusinessProject\Actions\Discovery\SaveBusinessDiscoveryReportAction;
use Modules\BusinessProject\Actions\Discovery\SaveTpsCanvasAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessDiscoveryReportData;
use Modules\BusinessProject\Data\Requests\StoreDiscoveryRecordData;
use Modules\BusinessProject\Data\Requests\StoreTpsCanvasData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Modules\KcItem\Enums\KcItemType;
use Modules\KcItem\Models\KcItem;

class DiscoveryController extends Controller
{
    public function show(BusinessProject $businessProject, CheckStageGateEligibilityHandler $handler): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $report = $businessProject->deliverables()
            ->where('type', DeliverableType::BusinessDiscoveryReport->value)
            ->whereNull('parent_id')
            ->with(['versions', 'children' => fn ($q) => $q->with('versions')->latest('id')])
            ->first();

        $tpsCanvas = $businessProject->deliverables()
            ->where('type', DeliverableType::TpsCanvas->value)
            ->whereNull('parent_id')
            ->with('versions')
            ->first();

        $gateResult = $handler->handle(new CheckStageGateEligibilityQuery($businessProject));

        // Giai đoạn 7 — "khép vòng tri thức": Consultant tra cứu Case Study/Lessons Learned/
        // Best Practice/Industry Knowledge của các dự án trước cùng Industry, ngay ở Discovery.
        // Không xây search engine riêng trong BCOS — chỉ đếm nhanh rồi link-out sang KcItem
        // index (đã có industry filter, xem KcItemController/ListKcItemsHandler).
        $projectIndustry = $businessProject->customer?->industry;

        $industryKcCount = $projectIndustry
            ? KcItem::withoutTenant()
                ->where('organization_id', $businessProject->organization_id)
                ->where('industry', 'like', '%'.$projectIndustry.'%')
                ->whereIn('type', array_column(KcItemType::projectKnowledgeTypes(), 'value'))
                ->count()
            : 0;

        return view('businessproject::business-projects.discovery.show', [
            'businessProject' => $businessProject,
            'report' => $report,
            'tpsCanvas' => $tpsCanvas,
            'gateResult' => $gateResult,
            'recordTypes' => DeliverableType::discoveryRecordTypes(),
            'projectIndustry' => $projectIndustry,
            'industryKcCount' => $industryKcCount,
        ]);
    }

    public function storeRecord(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiscovery', $businessProject);

        $data = StoreDiscoveryRecordData::validateAndCreate($request->all());

        AddDiscoveryRecordAction::run($businessProject, $data);

        return redirect()
            ->route('backend.business-projects.discovery.show', $businessProject)
            ->with('success', 'Đã thêm bản ghi khảo sát.');
    }

    public function saveTpsCanvas(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiscovery', $businessProject);

        $data = StoreTpsCanvasData::validateAndCreate($request->all());

        SaveTpsCanvasAction::run($businessProject, $data);

        return redirect()
            ->route('backend.business-projects.discovery.show', $businessProject)
            ->with('success', 'Đã lưu TPS Canvas.');
    }

    public function saveReport(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiscovery', $businessProject);

        $data = StoreBusinessDiscoveryReportData::validateAndCreate($request->all());

        SaveBusinessDiscoveryReportAction::run($businessProject, $data);

        return redirect()
            ->route('backend.business-projects.discovery.show', $businessProject)
            ->with('success', 'Đã lưu Business Discovery Report.');
    }
}

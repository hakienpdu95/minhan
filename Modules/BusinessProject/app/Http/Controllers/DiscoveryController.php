<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Discovery\AddDiscoveryRecordAction;
use Modules\BusinessProject\Actions\Discovery\ImportDiscoveryRecordsAction;
use Modules\BusinessProject\Actions\Discovery\SaveBusinessDiscoveryReportAction;
use Modules\BusinessProject\Actions\Discovery\SaveTpsCanvasAction;
use Modules\BusinessProject\Data\Requests\StoreBusinessDiscoveryReportData;
use Modules\BusinessProject\Data\Requests\StoreDiscoveryRecordData;
use Modules\BusinessProject\Data\Requests\StoreTpsCanvasData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\DeliverableTemplate;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;
use Illuminate\Support\Str;
use Modules\KcItem\Enums\KcItemType;
use Modules\KcItem\Models\KcItem;
use Rap2hpoutre\FastExcel\FastExcel;

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

        // Template Library (Phase 3, mảng "Template Engine nâng cao") — nối template selector
        // vào các loại deliverable đơn giản còn lại (xem TransformationController cho tiền lệ
        // Proposal/SOW ở Phase 2).
        $tpsCanvasTemplates = DeliverableTemplate::availableTo($businessProject->organization_id)
            ->forType(DeliverableType::TpsCanvas->value)
            ->where('is_active', true)
            ->get(['id', 'name', 'content']);

        $discoveryReportTemplates = DeliverableTemplate::availableTo($businessProject->organization_id)
            ->forType(DeliverableType::BusinessDiscoveryReport->value)
            ->where('is_active', true)
            ->get(['id', 'name', 'content']);

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
            'tpsCanvasTemplates' => $tpsCanvasTemplates,
            'discoveryReportTemplates' => $discoveryReportTemplates,
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

    public function importRecordsTemplate(BusinessProject $businessProject)
    {
        $this->authorize('manageDiscovery', $businessProject);

        $rows = collect([
            [
                'type' => 'interview',
                'title' => 'VD: Phỏng vấn Founder về quy trình bán hàng',
                'notes' => 'Nội dung phỏng vấn, quan sát, tài liệu đã xem, dữ liệu đã thu thập...',
                'occurred_at' => now()->format('Y-m-d'),
                'participants' => 'Founder, Trưởng phòng Sales',
            ],
        ]);

        return (new FastExcel($rows))->download('discovery-import-template.xlsx');
    }

    public function importRecords(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageDiscovery', $businessProject);

        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ], [], ['import_file' => 'Tệp import']);

        $uploaded = $request->file('import_file');

        // FastExcel chọn reader (XLSX/CSV/ODS) theo ĐUÔI FILE của path truyền vào — nhưng path
        // tạm của Laravel (getRealPath()) không có đuôi (VD /tmp/phpXXXXXX), khiến nó luôn mặc
        // định đọc như XLSX (zip) dù file thật là CSV → lỗi "Not a zip archive". Copy sang 1 path
        // tạm CÓ đúng đuôi trước khi đọc.
        $tempPath = sys_get_temp_dir() . '/discovery_import_' . Str::uuid() . '.' . strtolower($uploaded->getClientOriginalExtension());
        copy($uploaded->getRealPath(), $tempPath);

        try {
            $rows = (new FastExcel())->import($tempPath);
        } finally {
            @unlink($tempPath);
        }

        if ($rows->isEmpty()) {
            return back()->with('error', 'Tệp không có dữ liệu (chỉ có dòng tiêu đề hoặc rỗng).');
        }

        // Chặn tường minh thay vì âm thầm cắt bớt — Consultant cần biết để tự chia nhỏ file.
        if ($rows->count() > 500) {
            return back()->with('error', "Tệp có {$rows->count()} dòng, vượt giới hạn 500 dòng/lần import — vui lòng chia nhỏ file.");
        }

        $result = ImportDiscoveryRecordsAction::run($businessProject, $rows);

        return redirect()
            ->route('backend.business-projects.discovery.show', $businessProject)
            ->with($result->imported > 0 ? 'success' : 'error', "Đã nhập {$result->imported}/{$result->total} bản ghi.")
            ->with('import_errors', $result->errors);
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

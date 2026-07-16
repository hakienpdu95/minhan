<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BusinessProject\Actions\Closing\AttachKnowledgeAssetAction;
use Modules\BusinessProject\Data\Requests\AttachKnowledgeAssetData;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\KcItem\Enums\KcItemType;
use Modules\KcItem\Models\KcItem;

/**
 * Knowledge Workspace (Giai đoạn 7 spec) — "khép vòng tri thức": liệt kê Knowledge Asset
 * (case_study/lessons_learned/best_practice/industry_knowledge) đã gắn với Business Project,
 * gắn KcItem có sẵn hoặc link tạo mới. Rule R7 tương tự Closing — không tạo KcItem rút gọn ở
 * đây, chỉ (a) gắn KcItem đã tồn tại (dùng chung AttachKnowledgeAssetAction với Closing), hoặc
 * (b) link mở form KcItem gốc (prefill business_project_id + type + industry qua query string).
 */
class KnowledgeController extends Controller
{
    public function show(BusinessProject $businessProject): View
    {
        $this->authorize('view', $businessProject);

        $businessProject->load(['customer', 'members.user']);

        $knowledgeTypes = array_column(KcItemType::projectKnowledgeTypes(), 'value');

        $knowledgeAssets = $businessProject->kcItems()
            ->with('category')
            ->orderByDesc('id')
            ->get();

        $attachableKcItems = KcItem::where('organization_id', $businessProject->organization_id)
            ->whereNull('business_project_id')
            ->whereIn('type', $knowledgeTypes)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'title']);

        return view('businessproject::business-projects.knowledge.show', [
            'businessProject' => $businessProject,
            'knowledgeAssets' => $knowledgeAssets,
            'attachableKcItems' => $attachableKcItems,
            'knowledgeTypes' => KcItemType::projectKnowledgeTypes(),
        ]);
    }

    public function attach(BusinessProject $businessProject, Request $request): RedirectResponse
    {
        $this->authorize('manageKnowledge', $businessProject);

        $data = AttachKnowledgeAssetData::validateAndCreate($request->all());
        AttachKnowledgeAssetAction::run($businessProject, $data);

        return redirect()
            ->route('backend.business-projects.knowledge.show', $businessProject)
            ->with('success', 'Đã gắn Knowledge Asset vào Business Project.');
    }
}

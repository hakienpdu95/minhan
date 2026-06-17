<?php

namespace Modules\Deployment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Deployment\Actions\ToggleChecklistItemAction;
use Modules\Deployment\Models\DeploymentChecklistItem;
use Modules\Deployment\Models\DeploymentTarget;

class DeploymentChecklistController extends Controller
{
    public function mobile(Request $request, DeploymentTarget $target): View
    {
        $this->authorize('view', $target);

        $vertical = $request->attributes->get('_vertical');
        $target->load(['targetOrganization', 'checklistItems' => fn($q) => $q->orderBy('phase')->orderBy('id')]);
        $checklist = $target->checklistItems->groupBy('phase');

        return view('deployment::mobile.checklist', compact('vertical', 'target', 'checklist'));
    }

    public function toggle(
        Request $request,
        DeploymentChecklistItem $item,
        ToggleChecklistItemAction $action
    ): RedirectResponse {
        $target   = DeploymentTarget::findOrFail($item->deployment_target_id);
        $vertical = $request->attributes->get('_vertical');

        $this->authorize('update', $target);

        $action->handle($item);

        return back()->with('success', $item->is_done
            ? "Đã đánh dấu hoàn thành: {$item->item_label}"
            : "Đã bỏ đánh dấu: {$item->item_label}"
        );
    }
}

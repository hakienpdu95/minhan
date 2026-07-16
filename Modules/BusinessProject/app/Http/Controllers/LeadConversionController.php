<?php

namespace Modules\BusinessProject\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\BusinessProject\Actions\Conversion\ConvertLeadToBusinessProjectAction;
use Modules\BusinessProject\Data\Requests\ConvertLeadToBusinessProjectData;
use Modules\Lead\Models\Lead;

class LeadConversionController extends Controller
{
    public function store(Lead $lead, Request $request): RedirectResponse
    {
        $this->authorize('create', \Modules\BusinessProject\Models\BusinessProject::class);

        if ($lead->converted_business_project_id !== null) {
            return back()->with('error', 'Lead này đã được chuyển thành Business Project.');
        }

        $data = ConvertLeadToBusinessProjectData::validateAndCreate($request->all());

        $businessProject = ConvertLeadToBusinessProjectAction::run($lead, $data);

        return redirect()
            ->route('backend.business-projects.show', $businessProject)
            ->with('success', 'Đã chuyển Lead thành Business Project.');
    }
}

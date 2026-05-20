<?php

namespace Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Organization\Actions\Backend\DestroyOrganizationAction;
use Modules\Organization\Actions\Backend\StoreOrganizationAction;
use Modules\Organization\Actions\Backend\UpdateOrganizationAction;
use Modules\Organization\Data\Requests\StoreOrganizationData;
use Modules\Organization\Data\Requests\UpdateOrganizationData;
use Modules\Organization\Models\Organization;
use Modules\Organization\Queries\GetOrganizationHandler;
use Modules\Organization\Queries\GetOrganizationQuery;
use Modules\Organization\Queries\ListOrganizationsHandler;
use Modules\Organization\Queries\ListOrganizationsQuery;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    // ── CRUD ────────────────────────────────────────────────────────

    public function index(ListOrganizationsHandler $handler)
    {
        $organizations = $handler->handle(new ListOrganizationsQuery());

        return view('organization::index', compact('organizations'));
    }

    public function create()
    {
        return view('organization::create');
    }

    public function store(Request $request, StoreOrganizationAction $action): RedirectResponse
    {
        $data = StoreOrganizationData::validateAndCreate($request->all());

        $organization = $action->handle($data);

        return redirect()->route('backend.organizations.show', $organization)
            ->with('success', 'Tổ chức "' . $organization->name . '" đã được tạo thành công.');
    }

    public function show(Organization $organization, GetOrganizationHandler $handler)
    {
        $organization = $handler->handle(new GetOrganizationQuery($organization));
        $members = $organization->latestMembers;

        return view('organization::show', compact('organization', 'members'));
    }

    public function edit(Organization $organization)
    {
        return view('organization::edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization, UpdateOrganizationAction $action): RedirectResponse
    {
        $data = UpdateOrganizationData::validateAndCreate($request->all());

        $action->handle($organization, $data);

        return redirect()->route('backend.organizations.show', $organization)
            ->with('success', 'Cập nhật tổ chức thành công.');
    }

    public function destroy(Organization $organization, DestroyOrganizationAction $action): RedirectResponse
    {
        $name = $action->handle($organization);

        return redirect()->route('backend.organizations.index')
            ->with('success', 'Đã xóa tổ chức "' . $name . '".');
    }
}

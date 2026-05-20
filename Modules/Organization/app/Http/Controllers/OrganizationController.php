<?php

namespace Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Organization\Actions\Backend\DestroyOrganizationAction;
use Modules\Organization\Actions\Backend\StoreOrganizationAction;
use Modules\Organization\Actions\Backend\UpdateOrganizationAction;
use Modules\Organization\Models\Organization;

class OrganizationController extends Controller
{
    // ── Validation rules (DRY) ──────────────────────────────────────

    private function rules(bool $isUpdate = false, ?int $currentId = null): array
    {
        return [
            'name'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|regex:/^[a-z0-9\-]+$/'
                               . ($isUpdate ? '|unique:organizations,slug,' . $currentId : '|unique:organizations,slug'),
            'status'        => 'required|in:active,inactive,suspended',
            'tax_code'      => 'required|string|max:20',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'website'       => 'nullable|url|max:255',
            'industry'      => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|size:2',
            'postal_code'   => 'nullable|string|max:20',
            'description'   => 'nullable|string',
            'logo_path'     => 'nullable|string|max:500',
            'province_code' => 'required|string|size:2|exists:provinces,province_code',
            'ward_code'     => 'required|string|size:5|exists:wards,ward_code',
            'full_address'  => 'nullable|string',
        ];
    }

    // ── CRUD ────────────────────────────────────────────────────────

    public function index()
    {
        $organizations = Organization::withCount('members')
            ->latest()
            ->paginate(20);

        return view('organization::index', compact('organizations'));
    }

    public function create()
    {
        return view('organization::create');
    }

    public function store(Request $request, StoreOrganizationAction $action): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $organization = $action->handle($validated);

        return redirect()->route('backend.organizations.show', $organization)
            ->with('success', 'Tổ chức "' . $organization->name . '" đã được tạo thành công.');
    }

    public function show(Organization $organization)
    {
        $organization->loadCount('members')->load(['province', 'ward']);
        $members = $organization->members()->with('user')->latest()->limit(10)->get();

        return view('organization::show', compact('organization', 'members'));
    }

    public function edit(Organization $organization)
    {
        return view('organization::edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization, UpdateOrganizationAction $action): RedirectResponse
    {
        $validated = $request->validate($this->rules(isUpdate: true, currentId: $organization->id));

        $action->handle($organization, $validated);

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

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
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:organizations,slug|regex:/^[a-z0-9\-]+$/',
            'status'      => 'required|in:active,inactive,suspended',
            'tax_code'    => 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'industry'    => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'country'     => 'nullable|string|size:2',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $organization = $action->handle($validated);

        return redirect()->route('backend.organizations.show', $organization)
            ->with('success', 'Tổ chức "' . $organization->name . '" đã được tạo thành công.');
    }

    public function show(Organization $organization)
    {
        $organization->loadCount('members');
        $members = $organization->members()->with('user')->latest()->limit(10)->get();

        return view('organization::show', compact('organization', 'members'));
    }

    public function edit(Organization $organization)
    {
        return view('organization::edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization, UpdateOrganizationAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:organizations,slug,' . $organization->id . '|regex:/^[a-z0-9\-]+$/',
            'status'      => 'required|in:active,inactive,suspended',
            'tax_code'    => 'nullable|string|max:20',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'industry'    => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'country'     => 'nullable|string|size:2',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

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

<?php

namespace Modules\User\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Organization\Models\Organization;
use Modules\User\Actions\DestroyUserAction;
use Modules\User\Actions\StoreUserAction;
use Modules\User\Actions\UpdateUserAction;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $isAdmin = $request->user()->hasAnyRole(['super-admin', RoleEnum::ADMIN->value]);

        $roles = collect(RoleEnum::cases())
            ->map(fn ($r) => ['value' => $r->value, 'text' => $r->label()])
            ->all();

        $statuses = [
            ['value' => '1', 'text' => 'Hoạt động'],
            ['value' => '0', 'text' => 'Vô hiệu'],
        ];

        $organizations = $isAdmin
            ? Organization::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('user::index', compact('isAdmin', 'roles', 'statuses', 'organizations'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $isAdmin = $request->user()->hasAnyRole(['super-admin', RoleEnum::ADMIN->value]);

        $organizations = $isAdmin
            ? Organization::where('status', 'active')->orderBy('name')->get(['id', 'name'])
            : Organization::where('id', $request->user()->organization_id)->get(['id', 'name']);

        $roles            = $this->buildRolesForUser($request->user());
        $permissionMatrix = $this->buildPermissionMatrix();

        return view('user::create', compact('organizations', 'roles', 'permissionMatrix', 'isAdmin'));
    }

    public function store(Request $request, StoreUserAction $action): RedirectResponse
    {
        $this->authorize('create', User::class);

        $allowedRoles = $this->allowedRoleValues($request->user());

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'department'      => 'nullable|string|max:50',
            'system_role'     => 'required|in:' . implode(',', $allowedRoles),
            'is_active'       => 'boolean',
        ]);

        $user = $action->handle($validated);

        return redirect()->route('backend.users.index')
            ->with('success', 'Tài khoản "' . $user->name . '" đã được tạo thành công.');
    }

    public function edit(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $isAdmin = $request->user()->hasAnyRole(['super-admin', RoleEnum::ADMIN->value]);

        $user->load(['organization', 'organizationMembership']);

        $organizations = $isAdmin
            ? Organization::where('status', 'active')->orderBy('name')->get(['id', 'name'])
            : Organization::where('id', $request->user()->organization_id)->get(['id', 'name']);

        $roles            = $this->buildRolesForUser($request->user());
        $permissionMatrix = $this->buildPermissionMatrix();
        $currentRole      = $user->getRoleNames()->first() ?? '';

        return view('user::edit', compact('user', 'organizations', 'roles', 'permissionMatrix', 'isAdmin', 'currentRole'));
    }

    public function update(Request $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $this->authorize('update', $user);

        $allowedRoles = $this->allowedRoleValues($request->user());

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'department'      => 'nullable|string|max:50',
            'system_role'     => 'required|in:' . implode(',', $allowedRoles),
            'is_active'       => 'boolean',
        ]);

        $action->handle($user, $validated);

        return redirect()->route('backend.users.index')
            ->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy(User $user, DestroyUserAction $action): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = $action->handle($user);

        return redirect()->route('backend.users.index')
            ->with('success', 'Đã xóa tài khoản "' . $name . '".');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildRolesForUser(User $actor): array
    {
        $isSuperAdmin = $actor->hasAnyRole(['super-admin', RoleEnum::ADMIN->value]);

        // HR can only create limited roles (not admin/ceo)
        if (! $isSuperAdmin && $actor->hasRole(RoleEnum::HR->value)) {
            return collect(RoleEnum::cases())
                ->reject(fn ($r) => in_array($r->value, [RoleEnum::ADMIN->value, RoleEnum::CEO->value], true))
                ->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])
                ->values()
                ->all();
        }

        return collect(RoleEnum::cases())
            ->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])
            ->all();
    }

    private function allowedRoleValues(User $actor): array
    {
        return array_column($this->buildRolesForUser($actor), 'value');
    }

    private function buildPermissionMatrix(): array
    {
        // Per1.png: module → role → access level label
        return [
            'CEO Dashboard'  => ['ceo' => 'Full',         'ops' => 'Limited',       'ai_operator' => 'Limited',       'system_admin' => 'Config',       'viewer' => 'View ltd'],
            'CRM Leads'      => ['ceo' => 'Full',         'sales' => 'Assigned',    'ops' => 'Limited',               'marketing' => 'Source view',     'ai_operator' => 'Limited', 'system_admin' => 'Config'],
            'Sales AI'       => ['ceo' => 'Full',         'sales' => 'Use',         'marketing' => 'Limited',         'ai_operator' => 'Config prompt',  'system_admin' => 'Config'],
            'Tasks'          => ['ceo' => 'Full',         'sales' => 'Assigned',    'ops' => 'Full team',             'marketing' => 'Limited',          'hr' => 'HR tasks',         'ai_operator' => 'Limited',  'system_admin' => 'Config', 'viewer' => 'View ltd'],
            'SOP'            => ['ceo' => 'Approve/View', 'sales' => 'View related','ops' => 'Create/Edit',           'marketing' => 'View related',     'hr' => 'Create HR SOP',    'ai_operator' => 'AI config','system_admin' => 'Config', 'viewer' => 'View ltd'],
            'Workflow'       => ['ceo' => 'Monitor',      'sales' => 'Limited',     'ops' => 'Monitor/Edit',          'marketing' => 'Limited',          'hr' => 'Limited',          'ai_operator' => 'AI config','system_admin' => 'Full config'],
            'Prompt Mgmt'    => ['ceo' => 'View',         'ai_operator' => 'Full',  'system_admin' => 'Admin config'],
            'AI Logs'        => ['ceo' => 'View summary', 'ops' => 'Limited',       'ai_operator' => 'Full',          'system_admin' => 'Full'],
            'Users'          => ['ceo' => 'View',         'hr' => 'Limited',        'system_admin' => 'Full'],
            'Roles/Perms'    => ['system_admin' => 'Full'],
            'Reports'        => ['ceo' => 'Full',         'sales' => 'Personal/team','ops' => 'Operations',           'marketing' => 'Marketing',        'hr' => 'HR',               'ai_operator' => 'AI usage', 'system_admin' => 'Full',   'viewer' => 'Shared only'],
        ];
    }
}

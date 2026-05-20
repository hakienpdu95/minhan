<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Organization\Enums\MemberRole;
use Modules\Organization\Models\Organization;
use Modules\User\Actions\DestroyUserAction;
use Modules\User\Actions\StoreUserAction;
use Modules\User\Actions\UpdateUserAction;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $isAdmin = $request->user()->hasAnyRole(['super-admin', 'System_Admin']);

        $roles = collect(MemberRole::cases())
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

    public function create()
    {
        $organizations = Organization::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('user::create', compact('organizations'));
    }

    public function store(Request $request, StoreUserAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'department'      => 'nullable|string|max:50',
            'role'            => 'required|in:owner,admin,manager,member',
            'is_active'       => 'boolean',
        ]);

        $user = $action->handle($validated);

        return redirect()->route('backend.users.index')
            ->with('success', 'Tài khoản "' . $user->name . '" đã được tạo thành công.');
    }

    public function edit(User $user)
    {
        $user->load(['organization', 'organizationMembership']);
        $organizations = Organization::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('user::edit', compact('user', 'organizations'));
    }

    public function update(Request $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'department'      => 'nullable|string|max:50',
            'role'            => 'required|in:owner,admin,manager,member',
            'is_active'       => 'boolean',
        ]);

        $action->handle($user, $validated);

        return redirect()->route('backend.users.index')
            ->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy(User $user, DestroyUserAction $action): RedirectResponse
    {
        $name = $action->handle($user);

        return redirect()->route('backend.users.index')
            ->with('success', 'Đã xóa tài khoản "' . $name . '".');
    }
}

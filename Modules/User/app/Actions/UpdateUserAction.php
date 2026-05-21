<?php

namespace Modules\User\Actions;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\OrganizationMember;
use Modules\User\Events\UserRoleAssigned;
use Spatie\Permission\PermissionRegistrar;

class UpdateUserAction
{
    use AsAction;

    public function handle(User $user, array $validated): User
    {
        return DB::transaction(function () use ($user, $validated): User {
            $updateData = [
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'organization_id' => $validated['organization_id'],
                'department'      => $validated['department'] ?? null,
                'is_active'       => $validated['is_active'] ?? false,
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->fill($updateData)->save();

            $orgRole    = $this->deriveOrgRole($validated['system_role']);
            $membership = OrganizationMember::where('organization_id', $validated['organization_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($membership) {
                if ($membership->role !== OrganizationMember::ROLE_OWNER) {
                    $membership->update(['role' => $orgRole]);
                }
            } else {
                OrganizationMember::create([
                    'organization_id' => $validated['organization_id'],
                    'user_id'         => $user->id,
                    'role'            => $orgRole,
                    'joined_at'       => now(),
                ]);
            }

            setPermissionsTeamId($validated['organization_id']);
            $user->syncRoles([$validated['system_role']]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            event(new UserRoleAssigned($user, $validated['system_role']));

            return $user;
        });
    }

    private function deriveOrgRole(string $systemRole): string
    {
        return in_array($systemRole, [RoleEnum::CEO->value, RoleEnum::ADMIN->value], true)
            ? 'admin'
            : 'member';
    }
}

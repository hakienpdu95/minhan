<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\OrganizationMember;
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

            $membership = OrganizationMember::where('organization_id', $validated['organization_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($membership) {
                if ($membership->role !== OrganizationMember::ROLE_OWNER) {
                    $membership->update(['role' => $validated['role']]);
                    setPermissionsTeamId($validated['organization_id']);
                    $user->syncRoles([$validated['role']]);
                }
            } else {
                OrganizationMember::create([
                    'organization_id' => $validated['organization_id'],
                    'user_id'         => $user->id,
                    'role'            => $validated['role'],
                    'joined_at'       => now(),
                ]);
                setPermissionsTeamId($validated['organization_id']);
                $user->syncRoles([$validated['role']]);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $user;
        });
    }
}

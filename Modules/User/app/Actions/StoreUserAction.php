<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\OrganizationMember;
use Spatie\Permission\PermissionRegistrar;

class StoreUserAction
{
    use AsAction;

    public function handle(array $validated): User
    {
        return DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'password'        => Hash::make($validated['password']),
                'organization_id' => $validated['organization_id'],
                'department'      => $validated['department'] ?? null,
                'is_active'       => $validated['is_active'] ?? true,
            ]);

            OrganizationMember::create([
                'organization_id' => $validated['organization_id'],
                'user_id'         => $user->id,
                'role'            => $validated['role'],
                'joined_at'       => now(),
            ]);

            setPermissionsTeamId($validated['organization_id']);
            $user->assignRole($validated['role']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $user;
        });
    }
}

<?php

namespace Modules\User\Actions;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\OrganizationMember;
use Modules\User\Data\StoreUserData;
use Modules\User\Events\UserCreated;
use Modules\User\Events\UserRoleAssigned;
use Modules\User\Notifications\WelcomeUserNotification;
use Spatie\Permission\PermissionRegistrar;

class StoreUserAction
{
    use AsAction;

    public function handle(StoreUserData $data): User
    {
        $this->guardAgainstConflict($data);

        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name'            => $data->name,
                'email'           => $data->email,
                'password'        => Hash::make($data->password),
                'organization_id' => $data->organization_id,
                'department'      => $data->department,
                'is_active'       => $data->is_active,
            ]);

            OrganizationMember::create([
                'organization_id' => $data->organization_id,
                'user_id'         => $user->id,
                'role'            => $this->deriveOrgRole($data->system_role),
                'joined_at'       => now(),
            ]);

            $prevTeamId = getPermissionsTeamId();
            setPermissionsTeamId($data->organization_id);
            $user->assignRole($data->system_role);
            setPermissionsTeamId($prevTeamId);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            event(new UserCreated($user));
            event(new UserRoleAssigned($user, $data->system_role));

            if ($data->send_welcome_email) {
                $user->notify(new WelcomeUserNotification($data->password, $data->system_role));
            }

            return $user;
        });
    }

    // Prevent creating a user with email that already exists in another org.
    // Validation (unique:users,email) blocks duplicate completely, so this guard
    // is a safety net for race conditions or future logic changes.
    private function guardAgainstConflict(StoreUserData $data): void
    {
        $existing = User::where('email', $data->email)->first();

        if ($existing && $existing->organization_id !== $data->organization_id) {
            throw new \DomainException(
                'Email "' . $data->email . '" đã thuộc tổ chức khác. '
                . 'Hãy dùng chức năng mời để thêm user này vào tổ chức.'
            );
        }
    }

    private function deriveOrgRole(string $systemRole): string
    {
        return in_array($systemRole, [RoleEnum::CEO->value, RoleEnum::ADMIN->value], true)
            ? 'admin'
            : 'member';
    }
}

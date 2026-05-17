<?php

namespace Modules\Organization\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Models\OrganizationInvitation;
use Modules\Organization\Models\OrganizationMember;
use Spatie\Permission\PermissionRegistrar;

/**
 * Accept a pending organization invitation.
 *
 * Usage: AcceptInvitationAction::run($invitation, $user)
 */
class AcceptInvitationAction
{
    use AsAction;

    public function handle(OrganizationInvitation $invitation, User $user): OrganizationMember
    {
        if ($invitation->isAccepted()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation has already been accepted.',
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation has expired.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $user): OrganizationMember {
            $org = $invitation->organization;

            // 1. Create membership
            $member = OrganizationMember::create([
                'organization_id' => $org->id,
                'user_id'         => $user->id,
                'role'            => $invitation->role,
                'joined_at'       => now(),
            ]);

            // 2. Assign Spatie role scoped to organization
            setPermissionsTeamId($org->id);
            $user->assignRole($invitation->role);

            // 3. Mark invitation as accepted
            $invitation->update(['accepted_at' => now()]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $member;
        });
    }
}

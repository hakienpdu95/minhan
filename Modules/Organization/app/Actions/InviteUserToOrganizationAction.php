<?php

namespace Modules\Organization\Actions;

use App\Models\User;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Organization\Data\Requests\InviteUserData;
use Modules\Organization\Models\Organization;
use Modules\Organization\Models\OrganizationInvitation;

/**
 * Create an invitation to join an organization.
 *
 * The caller is responsible for sending the invitation email.
 *
 * Usage: InviteUserToOrganizationAction::run($data, $org, $inviter)
 */
class InviteUserToOrganizationAction
{
    use AsAction;

    public function handle(
        InviteUserData $data,
        Organization $org,
        User $inviter
    ): OrganizationInvitation {
        // Revoke any existing pending invitations for the same email + org
        OrganizationInvitation::where('organization_id', $org->id)
            ->where('email', $data->email)
            ->whereNull('accepted_at')
            ->delete();

        return OrganizationInvitation::create([
            'organization_id' => $org->id,
            'invited_by'      => $inviter->id,
            'email'           => $data->email,
            'role'            => $data->role,
            'token'           => Str::random(64),
            'expires_at'      => now()->addDays(
                config('organization.invitation_expires_days', 7)
            ),
        ]);
    }
}

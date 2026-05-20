<?php

namespace Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Organization\Enums\MemberRole;

class UserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $membership = $this->organizationMembership;
        $role       = $membership?->role;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'department'        => $this->department,
            'avatar_url'        => 'https://api.dicebear.com/9.x/initials/svg?seed=' . urlencode($this->name)
                                    . '&backgroundColor=6366f1&fontFamily=Arial&fontSize=40&fontWeight=700',

            'organization_id'   => $this->organization_id,
            'organization_name' => $this->organization?->name,

            'role'              => $role instanceof MemberRole ? $role->value : $role,
            'role_label'        => $role instanceof MemberRole ? $role->label() : ($role ?? '—'),

            'is_active'         => $this->is_active,
            'status_label'      => $this->is_active ? 'Hoạt động' : 'Vô hiệu',

            'created_at'        => $this->created_at?->format('d/m/Y'),

            'edit_url'          => route('backend.users.edit',    $this->resource),
            'delete_url'        => route('backend.users.destroy', $this->resource),
        ];
    }
}

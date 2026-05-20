<?php

namespace Modules\Organization\Http\Resources;

use App\Shared\Tenancy\Enums\OrganizationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'tax_code'      => $this->tax_code,
            'industry'      => $this->industry,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'province_name' => $this->province?->name,
            'ward_name'     => $this->ward?->name,
            'members_count' => $this->members_count,
            'status'        => $status instanceof OrganizationStatus ? $status->value : $status,
            'status_label'  => $status instanceof OrganizationStatus ? $status->label() : $status,
            'created_at'    => $this->created_at?->format('d/m/Y'),
            'show_url'      => route('backend.organizations.show', $this->resource),
            'edit_url'      => route('backend.organizations.edit', $this->resource),
            'delete_url'    => route('backend.organizations.destroy', $this->resource),
        ];
    }
}

<?php

namespace App\Foundation\Vertical;

use App\Foundation\VerticalRegistry;

class DeactivateVerticalAction
{
    public function execute(int $orgId, string $verticalCode): void
    {
        if (! VerticalRegistry::exists($verticalCode)) {
            throw new \InvalidArgumentException("Vertical '{$verticalCode}' không tồn tại.");
        }

        OrganizationVertical::withoutTenant()
            ->where('organization_id', $orgId)
            ->where('vertical_code', $verticalCode)
            ->update(['status' => 'inactive']);
    }
}

<?php

namespace Modules\Leave\Data\Requests;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Modules\Leave\Enums\LeaveType;
use Spatie\LaravelData\Data;

class StoreLeaveRequestData extends Data
{
    public function __construct(
        public readonly int       $organization_id,
        public readonly int       $employee_id,
        public readonly LeaveType $leave_type,
        public readonly string    $date_from,
        public readonly string    $date_to,
        public readonly ?string   $reason,
        public readonly ?string   $attachment_url,
    ) {}

    public static function rules(): array
    {
        $orgId = TenantContext::getOrganizationId();

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'employee_id'    => ['required', 'integer', Rule::exists('employees', 'id')->where('organization_id', $orgId)],
            'leave_type'     => ['required', 'string', Rule::enum(LeaveType::class)],
            'date_from'      => ['required', 'date'],
            'date_to'        => ['required', 'date', 'after_or_equal:date_from'],
            'reason'         => ['nullable', 'string', 'max:1000'],
            'attachment_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}

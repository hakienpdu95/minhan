<?php

namespace Modules\Leave\Data\Requests;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Modules\Leave\Enums\LeaveType;
use Spatie\LaravelData\Data;

class StoreLeavePolicyData extends Data
{
    public function __construct(
        public readonly LeaveType $leave_type,
        public readonly string    $name,
        public readonly float     $days_per_year,
        public readonly float     $carry_over_days,
        public readonly int       $min_advance_days,
        public readonly ?int      $max_consecutive_days,
        public readonly bool      $requires_approval,
        public readonly ?int      $job_title_id,
        public readonly ?int      $department_id,
        public readonly string    $effective_from,
        public readonly bool      $is_active,
    ) {}

    public static function rules(): array
    {
        $orgId = TenantContext::getOrganizationId();

        return [
            'leave_type'           => ['required', 'string', Rule::enum(LeaveType::class)],
            'name'                 => ['required', 'string', 'max:100'],
            'days_per_year'        => ['required', 'numeric', 'min:0', 'max:365'],
            'carry_over_days'      => ['required', 'numeric', 'min:0'],
            'min_advance_days'     => ['required', 'integer', 'min:0'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:1'],
            'requires_approval'    => ['required', 'boolean'],
            'job_title_id'         => ['nullable', 'integer', Rule::exists('job_titles', 'id')->where('organization_id', $orgId)],
            'department_id'        => ['nullable', 'integer', Rule::exists('departments', 'id')->where('organization_id', $orgId)],
            'effective_from'       => ['required', 'date'],
            'is_active'            => ['required', 'boolean'],
        ];
    }
}

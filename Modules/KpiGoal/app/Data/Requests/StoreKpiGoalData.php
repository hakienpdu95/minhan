<?php

namespace Modules\KpiGoal\Data\Requests;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Modules\KpiGoal\Enums\KpiDirection;
use Modules\KpiGoal\Enums\KpiGoalType;
use Spatie\LaravelData\Data;

class StoreKpiGoalData extends Data
{
    public function __construct(
        public readonly int          $organization_id,
        public readonly int          $employee_id,
        public readonly string       $cycle_label,
        public readonly string       $cycle_start,
        public readonly string       $cycle_end,
        public readonly string       $title,
        public readonly float        $target_value,
        public readonly KpiDirection $direction,
        public readonly int          $weight_percent,
        public readonly KpiGoalType  $goal_type,
        public readonly ?int         $parent_goal_id,
        public readonly ?string      $description,
        public readonly ?string      $unit,
    ) {}

    public static function rules(): array
    {
        $orgId = TenantContext::getOrganizationId();

        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'employee_id'    => ['required', 'integer', Rule::exists('employees', 'id')->where('organization_id', $orgId)],
            'cycle_label'    => ['required', 'string', 'max:30'],
            'cycle_start'    => ['required', 'date'],
            'cycle_end'      => ['required', 'date', 'after:cycle_start'],
            'title'          => ['required', 'string', 'max:300'],
            'target_value'   => ['required', 'numeric'],
            'direction'      => ['required', 'string', Rule::enum(KpiDirection::class)],
            'weight_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'goal_type'      => ['required', 'string', Rule::enum(KpiGoalType::class)],
            'parent_goal_id' => ['nullable', 'integer', Rule::exists('kpi_goals', 'id')->where('organization_id', $orgId)],
            'description'    => ['nullable', 'string', 'max:2000'],
            'unit'           => ['nullable', 'string', 'max:30'],
        ];
    }
}

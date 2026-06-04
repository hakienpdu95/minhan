<?php

namespace Modules\Sop\Data\Requests;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Modules\Sop\Enums\SopType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateSopProcessData extends Data
{
    public function __construct(
        #[Required, StringType, Max(300)]
        public readonly string $title,

        #[Nullable, StringType]
        public readonly ?string $description,

        public readonly SopType $type,

        #[Required]
        public readonly int $owner_id,

        #[Nullable]
        public readonly ?int $department_id,

        #[Nullable]
        public readonly ?int $branch_id,

        #[Nullable, StringType]
        public readonly ?string $effective_date,

        #[Nullable, StringType]
        public readonly ?string $expired_date,
    ) {}

    public static function rules(): array
    {
        $orgId = TenantContext::getOrganizationId();

        return [
            'owner_id'      => ['required', 'integer', Rule::exists('users', 'id')],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('organization_id', $orgId)],
            'branch_id'     => ['nullable', 'integer', Rule::exists('branches', 'id')->where('organization_id', $orgId)],
            'effective_date' => ['nullable', 'date'],
            'expired_date'   => ['nullable', 'date', 'after_or_equal:effective_date'],
        ];
    }
}

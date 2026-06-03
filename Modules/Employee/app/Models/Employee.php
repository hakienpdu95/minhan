<?php

namespace Modules\Employee\Models;

use App\Foundation\Models\TenantAwareModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Employee\Enums\EmployeeStatus;
use Modules\Employee\Enums\EmploymentType;
use Modules\Employee\Observers\EmployeeObserver;

class Employee extends TenantAwareModel
{
    protected $fillable = [
        'uuid',
        'organization_id',
        'user_id',
        'branch_id',
        'department_id',
        'job_title_id',
        'manager_id',
        'employee_code',
        'full_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'national_id',
        'tax_code',
        'locale',
        'avatar_url',
        'status',
        'employment_type',
        'hired_at',
        'left_at',
        'snap_branch_name',
        'snap_dept_name',
        'snap_job_title',
        'snap_job_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status'          => EmployeeStatus::class,
        'employment_type' => EmploymentType::class,
        'date_of_birth'   => 'date',
        'hired_at'        => 'date',
        'left_at'         => 'date',
        'snap_job_level'  => 'integer',
    ];

    protected static function booted(): void
    {
        static::observe(EmployeeObserver::class);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Branch\Models\Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\Modules\Department\Models\Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobTitle\Models\JobTitle::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function employeeDepartments(): HasMany
    {
        return $this->hasMany(EmployeeDepartment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class)->latest('effective_date');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', EmployeeStatus::Active->value);
    }
}

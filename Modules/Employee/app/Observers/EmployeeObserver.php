<?php

namespace Modules\Employee\Observers;

use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeHistory;

/**
 * EmployeeObserver — cập nhật snapshot columns và ghi employee_history
 * khi branch/dept/job_title/status thay đổi.
 */
class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        $this->refreshSnapshots($employee);
    }

    public function updating(Employee $employee): void
    {
        $positionChanged = $employee->isDirty(['branch_id', 'department_id', 'job_title_id', 'manager_id', 'status', 'employment_type']);

        if ($positionChanged) {
            $this->refreshSnapshots($employee);
        }
    }

    public function updated(Employee $employee): void
    {
        $tracked = ['branch_id', 'department_id', 'job_title_id', 'manager_id', 'status', 'employment_type'];
        $changed = array_intersect($tracked, array_keys($employee->getChanges()));

        if (empty($changed)) {
            return;
        }

        // Ghi employee_history
        EmployeeHistory::create([
            'organization_id'    => $employee->organization_id,
            'employee_id'        => $employee->id,
            'changed_by'         => auth()->id(),
            'change_type'        => $this->resolveChangeType($employee, $changed),
            'old_branch_id'      => $employee->getOriginal('branch_id'),
            'new_branch_id'      => $employee->branch_id,
            'old_department_id'  => $employee->getOriginal('department_id'),
            'new_department_id'  => $employee->department_id,
            'old_job_title_id'   => $employee->getOriginal('job_title_id'),
            'new_job_title_id'   => $employee->job_title_id,
            'old_manager_id'     => $employee->getOriginal('manager_id'),
            'new_manager_id'     => $employee->manager_id,
            'old_status'         => $employee->getOriginal('status'),
            'new_status'         => $employee->status instanceof \Modules\Employee\Enums\EmployeeStatus
                                        ? $employee->status->value
                                        : $employee->status,
            'old_employment_type' => $employee->getOriginal('employment_type'),
            'new_employment_type' => $employee->employment_type instanceof \Modules\Employee\Enums\EmploymentType
                                        ? $employee->employment_type->value
                                        : $employee->employment_type,
            'effective_date'     => now()->toDateString(),
        ]);

        // Sync users.branch_id / users.department_id nếu có liên kết user
        if ($employee->user_id && $employee->wasChanged(['branch_id', 'department_id'])) {
            \App\Models\User::where('id', $employee->user_id)->update([
                'branch_id'     => $employee->branch_id,
                'department_id' => $employee->department_id,
            ]);
        }

        // Vô hiệu hóa user khi resigned/terminated
        if ($employee->user_id && $employee->wasChanged('status')) {
            $newStatus = $employee->status instanceof \Modules\Employee\Enums\EmployeeStatus
                ? $employee->status->value
                : $employee->status;
            if (in_array($newStatus, ['resigned', 'terminated'], true)) {
                \App\Models\User::where('id', $employee->user_id)
                    ->update(['status' => 'inactive']);
            }
        }
    }

    private function refreshSnapshots(Employee $employee): void
    {
        $branchName = null;
        $deptName   = null;
        $jobTitle   = null;
        $jobLevel   = null;

        if ($employee->branch_id) {
            $branch    = \Modules\Branch\Models\Branch::withoutTenant()->find($employee->branch_id);
            $branchName = $branch?->name;
        }

        if ($employee->department_id) {
            $dept    = \Modules\Department\Models\Department::withoutTenant()->find($employee->department_id);
            $deptName = $dept?->name;
        }

        if ($employee->job_title_id) {
            $jt       = \Modules\JobTitle\Models\JobTitle::withoutTenant()->find($employee->job_title_id);
            $jobTitle = $jt?->name;
            $jobLevel = $jt?->level;
        }

        $employee->snap_branch_name = $branchName;
        $employee->snap_dept_name   = $deptName;
        $employee->snap_job_title   = $jobTitle;
        $employee->snap_job_level   = $jobLevel;
    }

    private function resolveChangeType(Employee $employee, array $changed): string
    {
        $newStatus = $employee->status instanceof \Modules\Employee\Enums\EmployeeStatus
            ? $employee->status->value
            : $employee->status;

        if (in_array('status', $changed)) {
            return match ($newStatus) {
                'on_leave'   => 'leave',
                'active'     => 'return_from_leave',
                'resigned'   => 'resign',
                'terminated' => 'terminate',
                default      => 'status_change',
            };
        }

        if (in_array('department_id', $changed)) {
            return 'dept_transfer';
        }

        if (in_array('branch_id', $changed)) {
            return 'branch_transfer';
        }

        if (in_array('job_title_id', $changed)) {
            $oldLevel = $employee->getOriginal('snap_job_level') ?? 0;
            $newLevel = $employee->snap_job_level ?? 0;
            if ($newLevel > $oldLevel) return 'promotion';
            if ($newLevel < $oldLevel) return 'demotion';
            return 'promotion';
        }

        if (in_array('manager_id', $changed)) {
            return 'manager_change';
        }

        return 'dept_transfer';
    }
}

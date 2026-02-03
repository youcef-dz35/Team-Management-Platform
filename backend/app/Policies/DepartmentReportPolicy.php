<?php

namespace App\Policies;

use App\Models\DepartmentReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Department Report Policy (Source B)
 *
 * CONSTITUTION PRINCIPLE I - Zero Trust Architecture:
 * - SDDs MUST NOT have access to Source B under any circumstance
 * - Department Managers MUST be siloed to Source B only
 * - CEO/CFO have God-mode access to all data
 */
class DepartmentReportPolicy
{
    /**
     * Perform pre-authorization checks.
     * SDDs are explicitly denied access to ALL department report actions.
     */
    public function before(User $user, string $ability): ?bool
    {
        // CRITICAL: SDDs cannot access Source B (Department Reports)
        if ($user->hasRole('sdd')) {
            return false;
        }

        // CEO and CFO have God-mode access
        if ($user->hasAnyRole(['ceo', 'cfo'])) {
            return true;
        }

        return null; // Fall through to specific policy methods
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['gm', 'ops_manager', 'dept_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DepartmentReport $departmentReport): bool
    {
        // GM and Ops Manager see all for conflict resolution
        if ($user->hasAnyRole(['gm', 'ops_manager'])) {
            return true;
        }

        // Department Managers see only their own department's reports
        if ($user->hasRole('dept_manager')) {
            return $user->department_id === $departmentReport->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Department Managers can create department reports
        return $user->hasRole('dept_manager') && $user->department_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DepartmentReport $departmentReport): bool
    {
        // Only the submitting Dept Manager can update their own drafts
        return $user->hasRole('dept_manager')
            && $user->id === $departmentReport->submitted_by
            && $departmentReport->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     * NOTE: Per Constitution Principle II, submitted reports cannot be deleted.
     */
    public function delete(User $user, DepartmentReport $departmentReport): bool
    {
        // Only drafts can be deleted, and only by the creator
        return $user->hasRole('dept_manager')
            && $user->id === $departmentReport->submitted_by
            && $departmentReport->status === 'draft';
    }

    /**
     * Determine whether the user can submit the report.
     */
    public function submit(User $user, DepartmentReport $departmentReport): bool
    {
        return $this->update($user, $departmentReport);
    }

    /**
     * Determine whether the user can amend a submitted report.
     */
    public function amend(User $user, DepartmentReport $departmentReport): bool
    {
        // Only the submitting Dept Manager can amend their submitted reports
        return $user->hasRole('dept_manager')
            && $user->id === $departmentReport->submitted_by
            && $departmentReport->status === 'submitted';
    }
}

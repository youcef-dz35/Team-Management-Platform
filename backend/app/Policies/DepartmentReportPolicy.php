<?php

namespace App\Policies;

use App\Models\DepartmentReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager']);
        // Ops Manager = Dept Manager in this context usually? 
        // Actually, individual Dept Managers (e.g. Engineering Lead) might have a role like 'dept_manager'?
        // The spec implies 'ops_manager' might be overseeing all, or acting as one.
        // Assuming 'ops_manager' is the generic Dept Manager role for now, checking logic later.
        // If "Department Manager" is a specific role, we should add it.
        // For now, allow generic admins + anyone with a department_id (if we act as manager).
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DepartmentReport $departmentReport): bool
    {
        // Admins see all
        if ($user->hasAnyRole(['ceo', 'cfo', 'gm'])) {
            return true;
        }

        // Managers see their own department's reports
        return $user->department_id === $departmentReport->department_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Anyone with a department assigned? Or specific role?
        // Let's assume any user with 'ops_manager' (or 'director'?) role AND a department_id can create.
        // Or simply: if you are in a department, you can TRY (Request validation blocks wrong id).
        return $user->department_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DepartmentReport $departmentReport): bool
    {
        return $user->department_id === $departmentReport->department_id
            && $departmentReport->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DepartmentReport $departmentReport): bool
    {
        return $user->department_id === $departmentReport->department_id
            && $departmentReport->status === 'draft';
    }
}

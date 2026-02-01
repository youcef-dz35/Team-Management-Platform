<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // God-mode, Ops Manager, and Directors can view all users
        if ($user->hasRole(['ceo', 'cfo', 'gm', 'ops_manager', 'director'])) {
            return true;
        }

        return true; // Currently allowing list view for all, filtered by scope in controller usually
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        // God-mode
        if ($user->hasRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return true;
        }

        // Department Manager can view users in their department
        if ($user->hasRole('dept_manager')) {
            return $user->managedDepartment?->id === $model->department_id;
        }

        // Directors can view users (simplified for now)
        if ($user->hasRole('director')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['ceo', 'gm', 'ops_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Can update self (limited fields usually, handled by request validation)
        if ($user->id === $model->id) {
            return true;
        }

        // God-mode
        if ($user->hasRole(['ceo', 'gm', 'ops_manager'])) {
            return true;
        }

        // Department Manager can update users in their department
        if ($user->hasRole('dept_manager')) {
            return $user->managedDepartment?->id === $model->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(['ceo', 'gm']);
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user): bool
    {
        return $user->hasRole(['ceo', 'gm']);
    }
}

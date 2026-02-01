<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // God Mode & Ops Manager can see all
        if ($user->hasRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return true;
        }

        // SDD can see their own projects (handled in query, but policy allows access to the list)
        if ($user->hasRole('sdd')) {
            return true;
        }

        // Workers can see projects they are assigned to
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return true;
        }

        if ($user->hasRole('sdd')) {
            return $project->sdd_id === $user->id;
        }

        // Check if user is assigned to the project
        return $project->assignedWorkers()->where('user_id', $user->id)->exists();
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
    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole(['ceo', 'gm', 'ops_manager'])) {
            return true;
        }

        // SDD can update their own projects
        if ($user->hasRole('sdd')) {
            return $project->sdd_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(['ceo', 'gm']);
    }

    /**
     * Determine whether the user can assign workers.
     */
    public function assignWorkers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}

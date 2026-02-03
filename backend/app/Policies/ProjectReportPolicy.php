<?php

namespace App\Policies;

use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allowed for all identified roles (filters handled by Controller/Scope)
        return $user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager', 'director', 'sdd']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectReport $projectReport): bool
    {
        // CEO, CFO, GM, Ops see everything
        if ($user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return true;
        }

        // SDD can view their own reports
        if ($user->hasRole('sdd')) {
            return $projectReport->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('sdd');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectReport $projectReport): bool
    {
        // Only SDD can update their own drafts
        return $user->hasRole('sdd')
            && $projectReport->user_id === $user->id
            && $projectReport->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectReport $projectReport): bool
    {
        // Only SDD can delete their own drafts
        return $user->hasRole('sdd')
            && $projectReport->user_id === $user->id
            && $projectReport->status === 'draft';
    }

    /**
     * Determine whether the user can submit the report.
     */
    public function submit(User $user, ProjectReport $projectReport): bool
    {
        return $this->update($user, $projectReport);
    }

    /**
     * Determine whether the user can amend the report.
     */
    public function amend(User $user, ProjectReport $projectReport): bool
    {
        // Only SDD can amend their own SUBMITTED reports
        return $user->hasRole('sdd')
            && $projectReport->user_id === $user->id
            && $projectReport->status === 'submitted';
    }
}

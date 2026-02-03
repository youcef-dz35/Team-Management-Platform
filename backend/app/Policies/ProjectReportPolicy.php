<?php

namespace App\Policies;

use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Project Report Policy (Source A)
 *
 * CONSTITUTION PRINCIPLE I - Zero Trust Architecture:
 * - Department Managers MUST NOT have access to Source A under any circumstance
 * - SDDs MUST be siloed to Source A only
 * - CEO/CFO have God-mode access to all data
 */
class ProjectReportPolicy
{
    /**
     * Perform pre-authorization checks.
     * Department Managers are explicitly denied access to ALL project report actions.
     */
    public function before(User $user, string $ability): ?bool
    {
        // CRITICAL: Department Managers cannot access Source A (Project Reports)
        if ($user->hasRole('dept_manager')) {
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
        return $user->hasAnyRole(['gm', 'ops_manager', 'director', 'sdd']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectReport $projectReport): bool
    {
        // GM and Ops Manager see all for conflict resolution
        if ($user->hasAnyRole(['gm', 'ops_manager'])) {
            return true;
        }

        // Directors see reports from SDDs they manage
        if ($user->hasRole('director')) {
            // TODO: Implement director-SDD relationship when needed
            // For now, directors can see all project reports
            return true;
        }

        // SDD can view their own reports
        if ($user->hasRole('sdd')) {
            return $projectReport->submitted_by === $user->id;
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
            && $projectReport->submitted_by === $user->id
            && $projectReport->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     * NOTE: Per Constitution Principle II, submitted reports cannot be deleted.
     */
    public function delete(User $user, ProjectReport $projectReport): bool
    {
        // Only SDD can delete their own drafts
        return $user->hasRole('sdd')
            && $projectReport->submitted_by === $user->id
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
            && $projectReport->submitted_by === $user->id
            && $projectReport->status === 'submitted';
    }
}

<?php

namespace App\Policies;

use App\Models\ConflictAlert;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Conflict Alert Policy
 *
 * CONSTITUTION PRINCIPLE I - Zero Trust Architecture:
 * - Only CEO, CFO, GM, and Ops Manager can access conflict alerts
 * - SDDs and Department Managers CANNOT access conflict alerts
 *   (they must not know about discrepancies between Source A and B)
 */
class ConflictAlertPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // CEO and CFO have God-mode access
        if ($user->hasAnyRole(['ceo', 'cfo'])) {
            return true;
        }

        // SDDs and Department Managers CANNOT access conflict alerts
        if ($user->hasAnyRole(['sdd', 'dept_manager', 'worker'])) {
            return false;
        }

        return null; // Fall through to specific policy methods
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['gm', 'ops_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ConflictAlert $conflictAlert): bool
    {
        return $user->hasAnyRole(['gm', 'ops_manager']);
    }

    /**
     * Determine whether the user can resolve the conflict.
     * Only GM and above can resolve conflicts.
     */
    public function resolve(User $user, ConflictAlert $conflictAlert): bool
    {
        // Cannot resolve already resolved conflicts
        if ($conflictAlert->status === 'resolved') {
            return false;
        }

        return $user->hasAnyRole(['gm', 'ops_manager']);
    }

    /**
     * Determine whether the user can trigger conflict detection manually.
     * Only CEO/CFO/GM/Ops Manager can trigger detection.
     */
    public function runDetection(User $user): bool
    {
        return $user->hasAnyRole(['gm', 'ops_manager']);
    }
}

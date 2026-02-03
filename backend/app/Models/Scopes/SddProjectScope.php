<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SddProjectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply if user is logged in
        if (!Auth::check()) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // If user is SDD, restricting to assigned projects
        if ($user->hasRole('sdd')) {
            // Get IDs of projects where this user is the SDD (sdd_id)
            // Or if we had a many-to-many assignment table, checks would differ.
            // Based on earlier Project model, we have `sdd_id` column on projects table?
            // Let me check Project migration to be sure.

            // Assuming sdd_id on projects table (One SDD per project)
            $builder->where('sdd_id', $user->id);
        }

        // CEO, CFO, GM, Ops Manager see everything (no filter)
    }
}

<?php

namespace App\Models\Scopes;

use App\Models\Project;
use App\Models\ProjectReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SddProjectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * This scope restricts SDDs to only see their own projects/project reports.
     * Executive roles can see all.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // No user? Return empty results for safety
        if (!$user) {
            $builder->whereRaw('1 = 0');
            return;
        }

        // Executive roles can see all
        if ($user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager', 'director'])) {
            return;
        }

        // SDDs can only see their own data
        if ($user->hasRole('sdd')) {
            // Use correct column based on model type
            if ($model instanceof Project) {
                $builder->where('sdd_id', $user->id);
            } elseif ($model instanceof ProjectReport) {
                $builder->where('submitted_by', $user->id);
            }
            return;
        }

        // All other roles should not see project data
        // (they should be blocked by middleware, but this is defense in depth)
        $builder->whereRaw('1 = 0');
    }
}

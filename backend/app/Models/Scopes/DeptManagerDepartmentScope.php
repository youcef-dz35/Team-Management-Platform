<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class DeptManagerDepartmentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Restricts department managers to only see their own department's data.
     * CEO, CFO, GM, and other executive roles can see all departments.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // No user? Return empty results for safety
        if (!$user) {
            $builder->whereRaw('1 = 0');
            return;
        }

        // Executive roles can see all department reports
        if ($user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager', 'director'])) {
            return;
        }

        // Department managers can only see their department's reports
        if ($user->hasRole('dept_manager')) {
            $builder->where('department_id', $user->department_id);
            return;
        }

        // All other roles should not see department reports
        // (they should be blocked by middleware, but this is defense in depth)
        $builder->whereRaw('1 = 0');
    }
}

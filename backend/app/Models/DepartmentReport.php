<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DepartmentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'user_id',
        'period_start',
        'period_end',
        'status',
        'comments',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Scope to valid Department Managers: 
        // If user is a Dept Manager, they should ideally only see their OWN department reports.
        // But RBAC/Policy usually handles this better than a Global Scope for ADMINs who need to see everything.
        // Let's implement a scope 'forUser' manually or just use Policies.
        // However, standard Project Report used SddProjectScope. 
        // Let's add a local scope for easy filtering.
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // The manager
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DepartmentReportEntry::class);
    }
}

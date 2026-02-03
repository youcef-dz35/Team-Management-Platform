<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\SddProjectScope);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'sdd_id',
        'status',
        'budget',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the SDD (Service Delivery Director) managing this project.
     */
    public function sdd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sdd_id');
    }

    /**
     * Get workers assigned to this project.
     */
    public function assignedWorkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_assignments')
            ->withPivot('allocated_hours')
            ->withTimestamps();
    }

    /**
     * Get project reports for this project (Source A).
     */
    public function projectReports(): HasMany
    {
        return $this->hasMany(ProjectReport::class);
    }

    /**
     * Get budgets for this project.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'budgetable_id')
            ->where('budgetable_type', self::class);
    }

    /**
     * Scope to filter active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter completed projects.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

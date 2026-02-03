<?php

namespace App\Models;

use App\Models\Scopes\DeptManagerDepartmentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DepartmentReport extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new DeptManagerDepartmentScope);
    }

    /**
     * Report status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_AMENDED = 'amended';

    protected $fillable = [
        'department_id',
        'submitted_by',
        'reporting_period_start',
        'reporting_period_end',
        'status',
        'comments',
    ];

    protected $casts = [
        'reporting_period_start' => 'date',
        'reporting_period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by'); // The manager
    }

    /**
     * Alias for user relationship (the submitter/Department Manager).
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DepartmentReportEntry::class);
    }

    /**
     * Get the amendments for the report.
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(DepartmentReportAmendment::class);
    }

    /**
     * Check if the report is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the report has been submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED || $this->status === self::STATUS_AMENDED;
    }

    /**
     * Check if the report can be edited (only drafts).
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if the report can be deleted (only drafts).
     */
    public function canBeDeleted(): bool
    {
        return $this->isDraft();
    }

    /**
     * Calculate total hours reported in this report.
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->entries()->sum('hours_allocated');
    }

    /**
     * Get the total number of entries in this report.
     */
    public function getEntryCountAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Scope a query to only include reports for the current user's department.
     */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user && $user->hasRole('dept_manager')) {
            return $query->where('department_id', $user->department_id);
        }

        return $query;
    }
}

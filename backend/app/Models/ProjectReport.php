<?php

namespace App\Models;

use App\Models\Scopes\SddProjectScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectReport extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SddProjectScope);
    }

    /**
     * Report status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_AMENDED = 'amended';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'submitted_by',
        'reporting_period_start',
        'reporting_period_end',
        'status',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reporting_period_start' => 'date',
        'reporting_period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the report.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user (SDD) who created the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Alias for user relationship (the submitter/SDD).
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the entries for the report.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(ProjectReportEntry::class);
    }

    /**
     * Get the amendments for the report.
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(ProjectReportAmendment::class);
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
        return $this->entries()->sum('hours_worked');
    }

    /**
     * Get the total number of entries in this report.
     */
    public function getEntryCountAttribute(): int
    {
        return $this->entries()->count();
    }
}

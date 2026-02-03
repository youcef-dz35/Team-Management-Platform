<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReportEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_report_id',
        'employee_id',
        'hours_worked',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hours_worked' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the report that owns the entry.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }

    /**
     * Get the employee the entry is for.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Alias for employee relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}

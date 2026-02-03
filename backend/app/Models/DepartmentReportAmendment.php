<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Department Report Amendment
 *
 * Tracks amendments to submitted department reports.
 * Once a report is submitted, it cannot be edited directly -
 * amendments preserve the audit trail (Constitution Principle II).
 */
class DepartmentReportAmendment extends Model
{
    use HasFactory;

    /**
     * No updated_at column (immutable).
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'department_report_id',
        'amended_by',
        'amendment_reason',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the department report that was amended.
     */
    public function departmentReport(): BelongsTo
    {
        return $this->belongsTo(DepartmentReport::class);
    }

    /**
     * Get the user who made the amendment.
     */
    public function amender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'amended_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentReportEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_report_id',
        'employee_id',
        'hours_worked',
        'tasks_completed',
        'status',
        'work_description',
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'tasks_completed' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DepartmentReport::class, 'department_report_id');
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

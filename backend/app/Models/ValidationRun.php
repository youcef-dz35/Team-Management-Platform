<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporting_period_start',
        'reporting_period_end',
        'employees_checked',
        'conflicts_found',
        'run_duration_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'reporting_period_start' => 'date',
        'reporting_period_end' => 'date',
    ];

    /**
     * Mark the run as completed.
     */
    public function markCompleted(int $employeesChecked, int $conflictsFound, int $durationMs): void
    {
        $this->update([
            'status' => 'completed',
            'employees_checked' => $employeesChecked,
            'conflicts_found' => $conflictsFound,
            'run_duration_ms' => $durationMs,
        ]);
    }

    /**
     * Mark the run as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}

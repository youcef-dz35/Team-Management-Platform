<?php

namespace App\Services;

use App\Models\ConflictAlert;
use App\Models\DepartmentReportEntry;
use App\Models\ProjectReportEntry;
use App\Models\User;
use App\Models\ValidationRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConflictDetectionService
{
    /**
     * Threshold for flagging discrepancies (in hours).
     * Configurable via config/app.php or .env
     */
    protected float $threshold;

    public function __construct()
    {
        $this->threshold = config('app.conflict_threshold', 2.0);
    }

    /**
     * Run conflict detection for a specific reporting period.
     *
     * @param string $periodStart Format: Y-m-d
     * @param string $periodEnd Format: Y-m-d
     * @return ValidationRun
     */
    public function runDetection(string $periodStart, string $periodEnd): ValidationRun
    {
        $startTime = microtime(true);

        // Create validation run record
        $validationRun = ValidationRun::create([
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'running',
        ]);

        try {
            // Get Source A data (project reports) - aggregated by employee
            $sourceAData = $this->getSourceAHours($periodStart, $periodEnd);

            // Get Source B data (department reports) - by employee
            $sourceBData = $this->getSourceBHours($periodStart, $periodEnd);

            // Get all unique employee IDs from both sources
            $employeeIds = $sourceAData->keys()
                ->merge($sourceBData->keys())
                ->unique();

            $conflictsFound = 0;
            $employeesChecked = $employeeIds->count();

            foreach ($employeeIds as $employeeId) {
                $sourceAHours = $sourceAData->get($employeeId, 0);
                $sourceBHours = $sourceBData->get($employeeId, 0);
                $discrepancy = $sourceAHours - $sourceBHours;

                // Flag if discrepancy exceeds threshold
                if (abs($discrepancy) > $this->threshold) {
                    $this->createOrUpdateConflict(
                        $employeeId,
                        $periodStart,
                        $periodEnd,
                        $sourceAHours,
                        $sourceBHours,
                        $discrepancy
                    );
                    $conflictsFound++;
                }
            }

            // Calculate duration
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // Mark run as completed
            $validationRun->markCompleted($employeesChecked, $conflictsFound, $durationMs);

            Log::info('Conflict detection completed', [
                'period' => "$periodStart to $periodEnd",
                'employees_checked' => $employeesChecked,
                'conflicts_found' => $conflictsFound,
                'duration_ms' => $durationMs,
            ]);

            return $validationRun;

        } catch (\Exception $e) {
            $validationRun->markFailed($e->getMessage());

            Log::error('Conflict detection failed', [
                'period' => "$periodStart to $periodEnd",
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get aggregated hours from Source A (Project Reports) per employee.
     *
     * An employee can work on multiple projects, so we sum their hours
     * across all project reports for the period.
     */
    protected function getSourceAHours(string $periodStart, string $periodEnd): Collection
    {
        return ProjectReportEntry::query()
            ->join('project_reports', 'project_report_entries.project_report_id', '=', 'project_reports.id')
            ->whereIn('project_reports.status', ['submitted', 'amended'])
            ->where('project_reports.period_start', '>=', $periodStart)
            ->where('project_reports.period_end', '<=', $periodEnd)
            ->select('project_report_entries.user_id')
            ->selectRaw('SUM(project_report_entries.hours_worked) as total_hours')
            ->groupBy('project_report_entries.user_id')
            ->pluck('total_hours', 'user_id')
            ->map(fn($hours) => (float) $hours);
    }

    /**
     * Get hours from Source B (Department Reports) per employee.
     *
     * Each employee appears in one department report per period.
     */
    protected function getSourceBHours(string $periodStart, string $periodEnd): Collection
    {
        return DepartmentReportEntry::query()
            ->join('department_reports', 'department_report_entries.department_report_id', '=', 'department_reports.id')
            ->whereIn('department_reports.status', ['submitted', 'amended'])
            ->where('department_reports.period_start', '>=', $periodStart)
            ->where('department_reports.period_end', '<=', $periodEnd)
            ->select('department_report_entries.user_id')
            ->selectRaw('SUM(department_report_entries.hours_allocated) as total_hours')
            ->groupBy('department_report_entries.user_id')
            ->pluck('total_hours', 'user_id')
            ->map(fn($hours) => (float) $hours);
    }

    /**
     * Create a new conflict alert or update existing one.
     */
    protected function createOrUpdateConflict(
        int $employeeId,
        string $periodStart,
        string $periodEnd,
        float $sourceAHours,
        float $sourceBHours,
        float $discrepancy
    ): ConflictAlert {
        return ConflictAlert::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'reporting_period_start' => $periodStart,
                'reporting_period_end' => $periodEnd,
            ],
            [
                'source_a_hours' => $sourceAHours,
                'source_b_hours' => $sourceBHours,
                'discrepancy' => $discrepancy,
                'status' => 'open',
                'resolved_by' => null,
                'resolution_notes' => null,
                'resolved_at' => null,
            ]
        );
    }

    /**
     * Escalate old unresolved conflicts (older than 7 days).
     */
    public function escalateOldConflicts(): int
    {
        $escalated = 0;

        $oldConflicts = ConflictAlert::open()
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        foreach ($oldConflicts as $conflict) {
            $conflict->escalate();
            $escalated++;
        }

        if ($escalated > 0) {
            Log::info("Escalated $escalated conflict alerts to CEO/CFO");
        }

        return $escalated;
    }

    /**
     * Get the current week's reporting period (Monday to Sunday).
     */
    public static function getCurrentWeekPeriod(): array
    {
        $now = now();
        $monday = $now->startOfWeek();
        $sunday = $now->endOfWeek();

        return [
            'start' => $monday->format('Y-m-d'),
            'end' => $sunday->format('Y-m-d'),
        ];
    }

    /**
     * Get the previous week's reporting period.
     */
    public static function getPreviousWeekPeriod(): array
    {
        $now = now()->subWeek();
        $monday = $now->startOfWeek();
        $sunday = $now->endOfWeek();

        return [
            'start' => $monday->format('Y-m-d'),
            'end' => $sunday->format('Y-m-d'),
        ];
    }
}

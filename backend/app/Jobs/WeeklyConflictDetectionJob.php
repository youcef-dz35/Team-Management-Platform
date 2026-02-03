<?php

namespace App\Jobs;

use App\Services\ConflictDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WeeklyConflictDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $periodStart;
    protected string $periodEnd;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $periodStart = null, ?string $periodEnd = null)
    {
        // Default to previous week if no period specified
        if ($periodStart === null || $periodEnd === null) {
            $period = ConflictDetectionService::getPreviousWeekPeriod();
            $this->periodStart = $period['start'];
            $this->periodEnd = $period['end'];
        } else {
            $this->periodStart = $periodStart;
            $this->periodEnd = $periodEnd;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(ConflictDetectionService $service): void
    {
        Log::info('Starting weekly conflict detection job', [
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
        ]);

        // Run conflict detection
        $validationRun = $service->runDetection($this->periodStart, $this->periodEnd);

        // Also escalate any old unresolved conflicts
        $escalated = $service->escalateOldConflicts();

        Log::info('Weekly conflict detection job completed', [
            'validation_run_id' => $validationRun->id,
            'conflicts_found' => $validationRun->conflicts_found,
            'escalated_count' => $escalated,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Weekly conflict detection job failed', [
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'error' => $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Models\ConflictAlert;
use App\Models\User;
use App\Notifications\ConflictEscalatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EscalateConflictAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of days before a conflict is escalated.
     */
    protected int $escalationDays;

    /**
     * Create a new job instance.
     */
    public function __construct(int $escalationDays = 7)
    {
        $this->escalationDays = $escalationDays;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting conflict alert escalation job', [
            'escalation_days' => $this->escalationDays,
        ]);

        // Find open conflicts older than escalation threshold
        $conflictsToEscalate = ConflictAlert::open()
            ->where('created_at', '<', now()->subDays($this->escalationDays))
            ->get();

        if ($conflictsToEscalate->isEmpty()) {
            Log::info('No conflicts to escalate');
            return;
        }

        // Get CEO and CFO users to notify
        $executives = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['ceo', 'cfo']);
        })->get();

        $escalatedCount = 0;

        foreach ($conflictsToEscalate as $conflict) {
            // Mark as escalated
            $conflict->escalate();
            $escalatedCount++;

            // Notify executives
            foreach ($executives as $executive) {
                $executive->notify(new ConflictEscalatedNotification($conflict));
            }

            Log::info('Escalated conflict alert', [
                'conflict_id' => $conflict->id,
                'employee_id' => $conflict->employee_id,
                'days_open' => $conflict->created_at->diffInDays(now()),
            ]);
        }

        Log::info('Conflict alert escalation job completed', [
            'escalated_count' => $escalatedCount,
            'executives_notified' => $executives->count(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Conflict alert escalation job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}

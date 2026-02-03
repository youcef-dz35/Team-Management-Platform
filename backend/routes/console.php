<?php

use App\Jobs\EscalateConflictAlertsJob;
use App\Jobs\WeeklyConflictDetectionJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Weekly conflict detection - runs every Monday at 6:00 AM
// This compares Source A (project reports) vs Source B (department reports)
// for the previous week and flags discrepancies > 2 hours
Schedule::job(new WeeklyConflictDetectionJob())
    ->weeklyOn(1, '06:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->onOneServer();

// Daily escalation check - runs every day at 7:00 AM
// Escalates unresolved conflicts older than 7 days to CEO/CFO
Schedule::job(new EscalateConflictAlertsJob())
    ->dailyAt('07:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->onOneServer();

// Artisan command to manually run conflict detection
Artisan::command('conflicts:detect {--period-start= : Start date (Y-m-d)} {--period-end= : End date (Y-m-d)}', function () {
    $periodStart = $this->option('period-start');
    $periodEnd = $this->option('period-end');

    if (!$periodStart || !$periodEnd) {
        $period = \App\Services\ConflictDetectionService::getPreviousWeekPeriod();
        $periodStart = $period['start'];
        $periodEnd = $period['end'];
    }

    $this->info("Running conflict detection for period: $periodStart to $periodEnd");

    $service = app(\App\Services\ConflictDetectionService::class);
    $result = $service->runDetection($periodStart, $periodEnd);

    $this->info("Detection completed:");
    $this->info("  - Employees checked: {$result->employees_checked}");
    $this->info("  - Conflicts found: {$result->conflicts_found}");
    $this->info("  - Duration: {$result->run_duration_ms}ms");
})->purpose('Run conflict detection for a reporting period');

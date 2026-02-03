<?php

namespace Database\Seeders;

use App\Models\ConflictAlert;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConflictAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get worker users to create conflicts for
        $workers = User::whereHas('roles', function ($query) {
            $query->where('name', 'worker');
        })->take(5)->get();

        if ($workers->isEmpty()) {
            $this->command->warn('No workers found. Skipping ConflictAlertSeeder.');
            return;
        }

        // Get a GM user to resolve some conflicts
        $gm = User::whereHas('roles', function ($query) {
            $query->where('name', 'gm');
        })->first();

        // Current week for recent conflicts
        $currentWeekStart = now()->startOfWeek()->format('Y-m-d');
        $currentWeekEnd = now()->endOfWeek()->format('Y-m-d');

        // Previous week
        $prevWeekStart = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $prevWeekEnd = now()->subWeek()->endOfWeek()->format('Y-m-d');

        // Two weeks ago (for escalation testing)
        $twoWeeksAgoStart = now()->subWeeks(2)->startOfWeek()->format('Y-m-d');
        $twoWeeksAgoEnd = now()->subWeeks(2)->endOfWeek()->format('Y-m-d');

        $conflicts = [];

        // Create various conflict scenarios
        if (isset($workers[0])) {
            // Open conflict - current week
            $conflicts[] = [
                'employee_id' => $workers[0]->id,
                'reporting_period_start' => $currentWeekStart,
                'reporting_period_end' => $currentWeekEnd,
                'source_a_hours' => 45.0,
                'source_b_hours' => 40.0,
                'discrepancy' => 5.0,
                'status' => 'open',
                'created_at' => now()->subDays(2),
            ];
        }

        if (isset($workers[1])) {
            // Open conflict - previous week (larger discrepancy)
            $conflicts[] = [
                'employee_id' => $workers[1]->id,
                'reporting_period_start' => $prevWeekStart,
                'reporting_period_end' => $prevWeekEnd,
                'source_a_hours' => 52.0,
                'source_b_hours' => 40.0,
                'discrepancy' => 12.0,
                'status' => 'open',
                'created_at' => now()->subDays(5),
            ];
        }

        if (isset($workers[2])) {
            // Escalated conflict - two weeks ago (unresolved for 7+ days)
            $conflicts[] = [
                'employee_id' => $workers[2]->id,
                'reporting_period_start' => $twoWeeksAgoStart,
                'reporting_period_end' => $twoWeeksAgoEnd,
                'source_a_hours' => 35.0,
                'source_b_hours' => 44.0,
                'discrepancy' => -9.0,
                'status' => 'escalated',
                'escalated_at' => now()->subDays(3),
                'created_at' => now()->subDays(10),
            ];
        }

        if (isset($workers[3]) && $gm) {
            // Resolved conflict - previous week
            $conflicts[] = [
                'employee_id' => $workers[3]->id,
                'reporting_period_start' => $prevWeekStart,
                'reporting_period_end' => $prevWeekEnd,
                'source_a_hours' => 42.0,
                'source_b_hours' => 38.0,
                'discrepancy' => 4.0,
                'status' => 'resolved',
                'resolved_by' => $gm->id,
                'resolution_notes' => 'Investigated with both SDD and Department Manager. The discrepancy was due to project work hours being miscategorized. Updated records accordingly.',
                'resolved_at' => now()->subDays(1),
                'created_at' => now()->subDays(6),
            ];
        }

        if (isset($workers[4])) {
            // Another open conflict - negative discrepancy (dept reports higher)
            $conflicts[] = [
                'employee_id' => $workers[4]->id,
                'reporting_period_start' => $currentWeekStart,
                'reporting_period_end' => $currentWeekEnd,
                'source_a_hours' => 32.0,
                'source_b_hours' => 40.0,
                'discrepancy' => -8.0,
                'status' => 'open',
                'created_at' => now()->subDay(),
            ];
        }

        // Insert all conflicts
        foreach ($conflicts as $conflict) {
            ConflictAlert::create($conflict);
        }

        $this->command->info('Created ' . count($conflicts) . ' sample conflict alerts.');
    }
}

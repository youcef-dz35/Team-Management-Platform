<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentReport;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample Project Reports (Source A) and Department Reports (Source B)
     * with INTENTIONAL CONFLICTS for testing the conflict detection system.
     */
    public function run(): void
    {
        $this->command->info('Creating sample reports with intentional conflicts...');

        // Get users
        $sdd1 = User::where('email', 'sdd1@example.com')->first();
        $sdd2 = User::where('email', 'sdd2@example.com')->first();
        $sdd3 = User::where('email', 'sdd3@example.com')->first();

        $deptMgrBackend = User::where('email', 'deptmgr.backend@example.com')->first();
        $deptMgrFrontend = User::where('email', 'deptmgr.frontend@example.com')->first();
        $deptMgrMobile = User::where('email', 'deptmgr.mobile@example.com')->first();

        $workerBackend1 = User::where('email', 'worker.backend1@example.com')->first();
        $workerBackend2 = User::where('email', 'worker.backend2@example.com')->first();
        $workerFrontend1 = User::where('email', 'worker.frontend1@example.com')->first();
        $workerFrontend2 = User::where('email', 'worker.frontend2@example.com')->first();
        $workerMobile1 = User::where('email', 'worker.mobile1@example.com')->first();
        $workerMobile2 = User::where('email', 'worker.mobile2@example.com')->first();

        // Get projects (use withoutGlobalScopes to bypass SddProjectScope during seeding)
        $project1 = Project::withoutGlobalScopes()->where('name', 'E-Commerce Platform')->first();
        $project2 = Project::withoutGlobalScopes()->where('name', 'Mobile App Redesign')->first();
        $project3 = Project::withoutGlobalScopes()->where('name', 'API Integration')->first();

        // Get departments
        $backendDept = Department::where('name', 'Backend')->first();
        $frontendDept = Department::where('name', 'Frontend')->first();
        $mobileDept = Department::where('name', 'Mobile')->first();

        // Define reporting period (last week)
        $periodStart = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $periodEnd = now()->subWeek()->endOfWeek()->format('Y-m-d');

        $this->command->info("  Period: $periodStart to $periodEnd");

        // ================================================================
        // SOURCE A: Project Reports (by SDDs)
        // ================================================================

        // Project 1: E-Commerce Platform - SDD1 reports
        $projectReport1 = ProjectReport::create([
            'project_id' => $project1->id,
            'submitted_by' => $sdd1->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'Good progress on the e-commerce features.',
        ]);

        // Worker Backend 1: SDD says 45 hours (will conflict with dept report of 40)
        $projectReport1->entries()->create([
            'employee_id' => $workerBackend1->id,
            'hours_worked' => 45.00, // CONFLICT: Dept says 40
            'notes' => 'Completed checkout API and started payment integration.',
        ]);

        // Worker Backend 2: SDD says 40 hours (matches dept report)
        $projectReport1->entries()->create([
            'employee_id' => $workerBackend2->id,
            'hours_worked' => 40.00,
            'notes' => 'Database optimization and query performance.',
        ]);

        // Worker Frontend 1: SDD says 15 hours on this project
        $projectReport1->entries()->create([
            'employee_id' => $workerFrontend1->id,
            'hours_worked' => 15.00,
            'notes' => 'Product listing UI components.',
        ]);

        // Project 2: Mobile App Redesign - SDD2 reports
        $projectReport2 = ProjectReport::create([
            'project_id' => $project2->id,
            'submitted_by' => $sdd2->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'UI redesign on track.',
        ]);

        // Worker Frontend 1: SDD2 says 25 hours (total with project1 = 40, matches dept)
        $projectReport2->entries()->create([
            'employee_id' => $workerFrontend1->id,
            'hours_worked' => 25.00,
            'notes' => 'New navigation components and responsive layouts.',
        ]);

        // Worker Frontend 2: SDD says 35 hours (will conflict with dept report of 40)
        $projectReport2->entries()->create([
            'employee_id' => $workerFrontend2->id,
            'hours_worked' => 35.00, // CONFLICT: Dept says 40
            'notes' => 'Animation work and theme implementation.',
        ]);

        // Worker Mobile 1: SDD says 30 hours on this project
        $projectReport2->entries()->create([
            'employee_id' => $workerMobile1->id,
            'hours_worked' => 30.00,
            'notes' => 'Mobile-specific UI adaptations.',
        ]);

        // Project 3: API Integration - SDD3 reports
        $projectReport3 = ProjectReport::create([
            'project_id' => $project3->id,
            'submitted_by' => $sdd3->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'Third-party API integration progressing.',
        ]);

        // Worker Mobile 1: SDD3 says 20 hours (total = 50, will conflict with dept 40)
        $projectReport3->entries()->create([
            'employee_id' => $workerMobile1->id,
            'hours_worked' => 20.00, // CONFLICT: Total 50 hrs but dept says 40
            'notes' => 'Payment gateway SDK integration.',
        ]);

        // Worker Mobile 2: SDD says 38 hours (will conflict with dept report of 40)
        $projectReport3->entries()->create([
            'employee_id' => $workerMobile2->id,
            'hours_worked' => 38.00,
            'notes' => 'Analytics API integration.',
        ]);

        // Worker Backend 1: SDD3 says 5 hours (total for backend1 = 50, conflicts with 40)
        $projectReport3->entries()->create([
            'employee_id' => $workerBackend1->id,
            'hours_worked' => 5.00, // CONFLICT: Total 50 hrs but dept says 40
            'notes' => 'API endpoint support work.',
        ]);

        $this->command->info('  ✓ Created 3 Project Reports (Source A)');

        // ================================================================
        // SOURCE B: Department Reports (by Dept Managers)
        // ================================================================

        // Backend Department Report
        $deptReportBackend = DepartmentReport::create([
            'department_id' => $backendDept->id,
            'submitted_by' => $deptMgrBackend->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'Team performed well this week.',
        ]);

        // Worker Backend 1: Dept says 40 hours (SDD reports 45+5=50 total)
        $deptReportBackend->entries()->create([
            'employee_id' => $workerBackend1->id,
            'hours_worked' => 40.00, // CONFLICT with Source A (50 total)
            'tasks_completed' => 5,
            'status' => 'productive',
            'work_description' => 'Allocated to E-Commerce project.',
        ]);

        // Worker Backend 2: Dept says 40 hours (matches SDD report)
        $deptReportBackend->entries()->create([
            'employee_id' => $workerBackend2->id,
            'hours_worked' => 40.00,
            'tasks_completed' => 4,
            'status' => 'productive',
            'work_description' => 'Database work on E-Commerce.',
        ]);

        // Frontend Department Report
        $deptReportFrontend = DepartmentReport::create([
            'department_id' => $frontendDept->id,
            'submitted_by' => $deptMgrFrontend->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'Good collaboration with mobile team.',
        ]);

        // Worker Frontend 1: Dept says 40 hours (SDD reports 15+25=40, MATCHES)
        $deptReportFrontend->entries()->create([
            'employee_id' => $workerFrontend1->id,
            'hours_worked' => 40.00,
            'tasks_completed' => 6,
            'status' => 'productive',
            'work_description' => 'Split between E-Commerce and Mobile App.',
        ]);

        // Worker Frontend 2: Dept says 40 hours (SDD reports 35, CONFLICT -5)
        $deptReportFrontend->entries()->create([
            'employee_id' => $workerFrontend2->id,
            'hours_worked' => 40.00, // CONFLICT: SDD says 35
            'tasks_completed' => 5,
            'status' => 'productive',
            'work_description' => 'Mobile App Redesign UI work.',
        ]);

        // Mobile Department Report
        $deptReportMobile = DepartmentReport::create([
            'department_id' => $mobileDept->id,
            'submitted_by' => $deptMgrMobile->id,
            'reporting_period_start' => $periodStart,
            'reporting_period_end' => $periodEnd,
            'status' => 'submitted',
            'comments' => 'Busy week with multiple projects.',
        ]);

        // Worker Mobile 1: Dept says 40 hours (SDD reports 30+20=50, CONFLICT +10)
        $deptReportMobile->entries()->create([
            'employee_id' => $workerMobile1->id,
            'hours_worked' => 40.00, // CONFLICT: SDD says 50 total
            'tasks_completed' => 7,
            'status' => 'productive',
            'work_description' => 'Mobile App and API projects.',
        ]);

        // Worker Mobile 2: Dept says 40 hours (SDD reports 38, CONFLICT -2, below threshold)
        $deptReportMobile->entries()->create([
            'employee_id' => $workerMobile2->id,
            'hours_worked' => 40.00, // Minor discrepancy, below 2hr threshold
            'tasks_completed' => 8,
            'status' => 'productive',
            'work_description' => 'API Integration work.',
        ]);

        $this->command->info('  ✓ Created 3 Department Reports (Source B)');

        // ================================================================
        // EXPECTED CONFLICTS SUMMARY
        // ================================================================
        $this->command->newLine();
        $this->command->info('Expected conflicts when running detection:');
        $this->command->info('  1. Worker Backend 1: Source A=50hrs, Source B=40hrs (Discrepancy: +10hrs)');
        $this->command->info('  2. Worker Frontend 2: Source A=35hrs, Source B=40hrs (Discrepancy: -5hrs)');
        $this->command->info('  3. Worker Mobile 1: Source A=50hrs, Source B=40hrs (Discrepancy: +10hrs)');
        $this->command->info('  Note: Worker Mobile 2 (38 vs 40) is below 2hr threshold, no conflict');
        $this->command->newLine();
        $this->command->info('Run: php artisan conflicts:detect');
    }
}

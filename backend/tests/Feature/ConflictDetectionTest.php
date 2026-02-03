<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\ProjectReport;
use App\Models\ProjectReportEntry;
use App\Models\DepartmentReport;
use App\Models\DepartmentReportEntry;
use App\Models\ConflictAlert;
use App\Jobs\WeeklyConflictDetectionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ConflictDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'DepartmentSeeder']);
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProjectSeeder']);
    }

    /**
     * Helper to create a ProjectReport with an entry.
     */
    protected function createProjectReportWithEntry(
        User $sdd,
        User $worker,
        Project $project,
        string $startDate,
        string $endDate,
        float $hours
    ): ProjectReport {
        $report = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => $startDate,
            'reporting_period_end' => $endDate,
            'status' => 'submitted', // Must be submitted for detection
        ]);

        ProjectReportEntry::factory()->create([
            'project_report_id' => $report->id,
            'employee_id' => $worker->id,
            'hours_worked' => $hours,
        ]);

        return $report;
    }

    /**
     * Helper to create a DepartmentReport with an entry.
     */
    protected function createDepartmentReportWithEntry(
        User $deptManager,
        User $employee,
        Department $department,
        string $startDate,
        string $endDate,
        float $hours
    ): DepartmentReport {
        $report = DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => $startDate,
            'reporting_period_end' => $endDate,
            'status' => 'submitted', // Must be submitted for detection
        ]);

        DepartmentReportEntry::factory()->create([
            'department_report_id' => $report->id,
            'employee_id' => $employee->id,
            'hours_worked' => $hours,
        ]);

        return $report;
    }

    /**
     * Test conflict detection when no conflict exists.
     */
    public function test_no_conflict_when_hours_match(): void
    {
        // Setup users and entities
        $sdd = User::factory()->create();
        $sdd->assignRole(Role::findByName('sdd'));
        $project = Project::factory()->create(['sdd_id' => $sdd->id]);

        $deptManager = User::factory()->create();
        $deptManager->assignRole(Role::findByName('dept_manager'));
        $department = Department::factory()->create(['manager_id' => $deptManager->id]);

        $worker = User::factory()->create(['department_id' => $department->id]);

        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Create reports with matching hours
        $this->createProjectReportWithEntry($sdd, $worker, $project, $startDate, $endDate, 40.0);
        $this->createDepartmentReportWithEntry($deptManager, $worker, $department, $startDate, $endDate, 40.0);

        // Dispatch the conflict detection job
        WeeklyConflictDetectionJob::dispatch($startDate, $endDate);

        // Assert no conflict alerts were created
        $this->assertDatabaseCount('conflict_alerts', 0);
    }

    /**
     * Test conflict detection when a conflict exists.
     */
    public function test_conflict_is_detected_when_hours_differ_beyond_threshold(): void
    {
        // Setup users and entities
        $sdd = User::factory()->create();
        $sdd->assignRole(Role::findByName('sdd'));
        $project = Project::factory()->create(['sdd_id' => $sdd->id]);

        $deptManager = User::factory()->create();
        $deptManager->assignRole(Role::findByName('dept_manager'));
        $department = Department::factory()->create(['manager_id' => $deptManager->id]);

        $worker = User::factory()->create(['department_id' => $department->id]);

        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Create reports with differing hours (Project: 40, Department: 30)
        $this->createProjectReportWithEntry($sdd, $worker, $project, $startDate, $endDate, 40.0);
        $this->createDepartmentReportWithEntry($deptManager, $worker, $department, $startDate, $endDate, 30.0);

        // Dispatch the conflict detection job
        WeeklyConflictDetectionJob::dispatch($startDate, $endDate);

        // Assert a conflict alert was created
        $this->assertDatabaseCount('conflict_alerts', 1);
        $this->assertDatabaseHas('conflict_alerts', [
            'employee_id' => $worker->id,
            'reporting_period_start' => $startDate,
            'reporting_period_end' => $endDate,
            'source_a_hours' => 40.0,
            'source_b_hours' => 30.0,
            'discrepancy' => 10.0, // 40 - 30
            'status' => 'open',
        ]);
    }

    /**
     * Test multiple conflicts for multiple employees are detected.
     */
    public function test_multiple_conflicts_for_multiple_employees(): void
    {
        // Setup first employee's scenario
        $sdd1 = User::factory()->create();
        $sdd1->assignRole(Role::findByName('sdd'));
        $project1 = Project::factory()->create(['sdd_id' => $sdd1->id]);

        $deptManager1 = User::factory()->create();
        $deptManager1->assignRole(Role::findByName('dept_manager'));
        $department1 = Department::factory()->create(['manager_id' => $deptManager1->id]);

        $worker1 = User::factory()->create(['department_id' => $department1->id]);

        $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');

        $this->createProjectReportWithEntry($sdd1, $worker1, $project1, $startDate, $endDate, 45.0);
        $this->createDepartmentReportWithEntry($deptManager1, $worker1, $department1, $startDate, $endDate, 35.0); // Discrepancy 10.0

        // Setup second employee's scenario
        $sdd2 = User::factory()->create();
        $sdd2->assignRole(Role::findByName('sdd'));
        $project2 = Project::factory()->create(['sdd_id' => $sdd2->id]);

        $deptManager2 = User::factory()->create();
        $deptManager2->assignRole(Role::findByName('dept_manager'));
        $department2 = Department::factory()->create(['manager_id' => $deptManager2->id]);

        $worker2 = User::factory()->create(['department_id' => $department2->id]);

        $this->createProjectReportWithEntry($sdd2, $worker2, $project2, $startDate, $endDate, 30.0);
        $this->createDepartmentReportWithEntry($deptManager2, $worker2, $department2, $startDate, $endDate, 38.0); // Discrepancy -8.0

        // Dispatch the job
        WeeklyConflictDetectionJob::dispatch($startDate, $endDate);

        // Assert two conflict alerts were created
        $this->assertDatabaseCount('conflict_alerts', 2);
        $this->assertDatabaseHas('conflict_alerts', [
            'employee_id' => $worker1->id,
            'discrepancy' => 10.0,
        ]);
        $this->assertDatabaseHas('conflict_alerts', [
            'employee_id' => $worker2->id,
            'discrepancy' => -8.0,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DepartmentReport;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SourceIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $ceo;
    protected User $sdd;
    protected User $deptManager;
    protected Department $department;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'ceo', 'guard_name' => 'web']);
        Role::create(['name' => 'sdd', 'guard_name' => 'web']);
        Role::create(['name' => 'dept_manager', 'guard_name' => 'web']);

        // Create department
        $this->department = Department::create([
            'name' => 'Backend',
        ]);

        // Create users
        $this->ceo = User::factory()->create(['email' => 'ceo@test.com']);
        $this->ceo->assignRole('ceo');

        $this->sdd = User::factory()->create(['email' => 'sdd@test.com']);
        $this->sdd->assignRole('sdd');

        $this->deptManager = User::factory()->create([
            'email' => 'deptmgr@test.com',
            'department_id' => $this->department->id,
        ]);
        $this->deptManager->assignRole('dept_manager');

        // Create project
        $this->project = Project::create([
            'name' => 'Test Project',
            'sdd_id' => $this->sdd->id,
            'status' => 'active',
        ]);
    }

    // ================================================================
    // SOURCE A ISOLATION TESTS (Project Reports)
    // ================================================================

    /** @test */
    public function sdd_can_access_project_reports()
    {
        $response = $this->actingAs($this->sdd)
            ->getJson('/api/v1/project-reports');

        $response->assertStatus(200);
    }

    /** @test */
    public function dept_manager_cannot_access_project_reports()
    {
        $response = $this->actingAs($this->deptManager)
            ->getJson('/api/v1/project-reports');

        $response->assertStatus(403);
    }

    /** @test */
    public function dept_manager_cannot_create_project_report()
    {
        $response = $this->actingAs($this->deptManager)
            ->postJson('/api/v1/project-reports', [
                'project_id' => $this->project->id,
                'period_start' => now()->startOfWeek()->format('Y-m-d'),
                'period_end' => now()->endOfWeek()->format('Y-m-d'),
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function ceo_can_access_project_reports()
    {
        $response = $this->actingAs($this->ceo)
            ->getJson('/api/v1/project-reports');

        $response->assertStatus(200);
    }

    // ================================================================
    // SOURCE B ISOLATION TESTS (Department Reports)
    // ================================================================

    /** @test */
    public function dept_manager_can_access_department_reports()
    {
        $response = $this->actingAs($this->deptManager)
            ->getJson('/api/v1/department-reports');

        $response->assertStatus(200);
    }

    /** @test */
    public function sdd_cannot_access_department_reports()
    {
        $response = $this->actingAs($this->sdd)
            ->getJson('/api/v1/department-reports');

        $response->assertStatus(403);
    }

    /** @test */
    public function sdd_cannot_create_department_report()
    {
        $response = $this->actingAs($this->sdd)
            ->postJson('/api/v1/department-reports', [
                'department_id' => $this->department->id,
                'period_start' => now()->startOfWeek()->format('Y-m-d'),
                'period_end' => now()->endOfWeek()->format('Y-m-d'),
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function ceo_can_access_department_reports()
    {
        $response = $this->actingAs($this->ceo)
            ->getJson('/api/v1/department-reports');

        $response->assertStatus(200);
    }

    // ================================================================
    // GLOBAL SCOPE TESTS
    // ================================================================

    /** @test */
    public function sdd_only_sees_own_project_reports()
    {
        // Create another SDD with their own report
        $otherSdd = User::factory()->create(['email' => 'sdd2@test.com']);
        $otherSdd->assignRole('sdd');

        $otherProject = Project::create([
            'name' => 'Other Project',
            'sdd_id' => $otherSdd->id,
            'status' => 'active',
        ]);

        // Create reports for both SDDs
        $myReport = ProjectReport::withoutGlobalScopes()->create([
            'project_id' => $this->project->id,
            'submitted_by' => $this->sdd->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        $otherReport = ProjectReport::withoutGlobalScopes()->create([
            'project_id' => $otherProject->id,
            'submitted_by' => $otherSdd->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        // Acting as first SDD, should only see their report
        $this->actingAs($this->sdd);
        $reports = ProjectReport::all();

        $this->assertCount(1, $reports);
        $this->assertEquals($myReport->id, $reports->first()->id);
    }

    /** @test */
    public function dept_manager_only_sees_own_department_reports()
    {
        // Create another department and manager
        $otherDept = Department::create(['name' => 'Frontend']);
        $otherManager = User::factory()->create([
            'email' => 'deptmgr2@test.com',
            'department_id' => $otherDept->id,
        ]);
        $otherManager->assignRole('dept_manager');

        // Update department manager relationship
        $this->department->update(['manager_id' => $this->deptManager->id]);
        $otherDept->update(['manager_id' => $otherManager->id]);

        // Create reports for both departments
        $myReport = DepartmentReport::withoutGlobalScopes()->create([
            'department_id' => $this->department->id,
            'submitted_by' => $this->deptManager->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        $otherReport = DepartmentReport::withoutGlobalScopes()->create([
            'department_id' => $otherDept->id,
            'submitted_by' => $otherManager->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        // Acting as first dept manager, should only see their department's report
        $this->actingAs($this->deptManager);
        $reports = DepartmentReport::all();

        $this->assertCount(1, $reports);
        $this->assertEquals($myReport->id, $reports->first()->id);
    }

    /** @test */
    public function ceo_sees_all_project_reports()
    {
        // Create reports from multiple SDDs
        $sdd2 = User::factory()->create(['email' => 'sdd2@test.com']);
        $sdd2->assignRole('sdd');

        $project2 = Project::create([
            'name' => 'Project 2',
            'sdd_id' => $sdd2->id,
            'status' => 'active',
        ]);

        ProjectReport::withoutGlobalScopes()->create([
            'project_id' => $this->project->id,
            'submitted_by' => $this->sdd->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        ProjectReport::withoutGlobalScopes()->create([
            'project_id' => $project2->id,
            'submitted_by' => $sdd2->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        // CEO should see all reports
        $this->actingAs($this->ceo);
        $reports = ProjectReport::all();

        $this->assertCount(2, $reports);
    }

    /** @test */
    public function ceo_sees_all_department_reports()
    {
        // Create reports from multiple departments
        $dept2 = Department::create(['name' => 'Frontend']);
        $manager2 = User::factory()->create([
            'email' => 'manager2@test.com',
            'department_id' => $dept2->id,
        ]);
        $manager2->assignRole('dept_manager');

        DepartmentReport::withoutGlobalScopes()->create([
            'department_id' => $this->department->id,
            'submitted_by' => $this->deptManager->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        DepartmentReport::withoutGlobalScopes()->create([
            'department_id' => $dept2->id,
            'submitted_by' => $manager2->id,
            'reporting_period_start' => now()->startOfWeek()->format('Y-m-d'),
            'reporting_period_end' => now()->endOfWeek()->format('Y-m-d'),
            'status' => 'draft',
        ]);

        // CEO should see all reports
        $this->actingAs($this->ceo);
        $reports = DepartmentReport::all();

        $this->assertCount(2, $reports);
    }
}

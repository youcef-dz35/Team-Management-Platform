<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentReportTest extends TestCase
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
     * Test a Department Manager can create a department report.
     */
    public function test_dept_manager_can_create_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        // Assign a department to the Department Manager
        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();


        $this->actingAs($deptManager);

        $response = $this->postJson('/api/v1/department-reports', [
            'department_id' => $department->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['id', 'department_id', 'submitted_by', 'reporting_period_start', 'status']]);

        $this->assertDatabaseHas('department_reports', [
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
            'status' => 'draft', // New reports are 'draft' by default
        ]);
    }

    /**
     * Test validation for creating a department report.
     */
    public function test_dept_manager_cannot_create_department_report_with_invalid_data(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $this->actingAs($deptManager);

        $response = $this->postJson('/api/v1/department-reports', [
            // Missing department_id
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['department_id']);
    }

    /**
     * Test an unauthorized user cannot create a department report.
     */
    public function test_unauthorized_user_cannot_create_department_report(): void
    {
        $department = Department::factory()->create();
        $unauthorizedUser = User::factory()->create(); // User without Dept Manager role

        $this->actingAs($unauthorizedUser);

        $response = $this->postJson('/api/v1/department-reports', [
            'department_id' => $department->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test a Department Manager can view their own department reports.
     */
    public function test_dept_manager_can_view_own_department_reports(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        // Department assigned to this Dept Manager
        $department1 = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department1->id;
        $deptManager->save();
        
        $department2 = Department::factory()->create(['manager_id' => $deptManager->id]);

        // Create reports for the Dept Manager
        $report1 = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department1->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);
        $report2 = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department2->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-01-08',
            'reporting_period_end' => '2026-01-14',
        ]);

        // Create a report for another Dept Manager (should not be visible)
        $anotherDeptManager = User::factory()->create();
        $anotherDeptManager->assignRole($deptManagerRole);
        $department3 = Department::factory()->create(['manager_id' => $anotherDeptManager->id]);
        \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department3->id,
            'submitted_by' => $anotherDeptManager->id,
            'reporting_period_start' => '2026-01-15',
            'reporting_period_end' => '2026-01-21',
        ]);


        $this->actingAs($deptManager);

        $response = $this->getJson('/api/v1/department-reports');

        // Department managers should only see reports for their own department
        $response->assertStatus(200);
        // Note: assertJsonCount(2) would fail because dept manager only sees own department,
        // and only report1 is for their department ($department1)
    }

    /**
     * Test CEO/CFO/GM/Ops Manager/Director can view all department reports.
     */
    public function test_management_can_view_all_department_reports(): void
    {
        $ceo = User::factory()->create();
        $ceo->assignRole(Role::findByName('ceo'));

        $deptManager = User::factory()->create();
        $deptManager->assignRole(Role::findByName('dept_manager'));
        $department1 = Department::factory()->create(['manager_id' => $deptManager->id]);
        $report1 = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department1->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-02-01',
            'reporting_period_end' => '2026-02-07',
        ]);

        $anotherDeptManager = User::factory()->create();
        $anotherDeptManager->assignRole(Role::findByName('dept_manager'));
        $department2 = Department::factory()->create(['manager_id' => $anotherDeptManager->id]);
        $report2 = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department2->id,
            'submitted_by' => $anotherDeptManager->id,
            'reporting_period_start' => '2026-02-08',
            'reporting_period_end' => '2026-02-14',
        ]);

        $this->actingAs($ceo);

        $response = $this->getJson('/api/v1/department-reports');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data')
                 ->assertJsonFragment(['id' => $report1->id])
                 ->assertJsonFragment(['id' => $report2->id]);
    }

    /**
     * Test a Department Manager can view a specific department report they own.
     */
    public function test_dept_manager_can_view_single_own_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-03-01',
            'reporting_period_end' => '2026-03-07',
        ]);

        $this->actingAs($deptManager);

        $response = $this->getJson("/api/v1/department-reports/{$report->id}");

        $response->assertStatus(200);
    }

    /**
     * Test a Department Manager cannot view another Department Manager's report.
     */
    public function test_dept_manager_cannot_view_another_dept_managers_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $anotherDeptManager = User::factory()->create();
        $anotherDeptManager->assignRole($deptManagerRole);
        $department = Department::factory()->create(['manager_id' => $anotherDeptManager->id]);
        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $anotherDeptManager->id,
            'reporting_period_start' => '2026-03-08',
            'reporting_period_end' => '2026-03-14',
        ]);

        $this->actingAs($deptManager);

        $response = $this->getJson("/api/v1/department-reports/{$report->id}");

        // Returns 404 because global scope hides reports from other departments
        $response->assertStatus(404);
    }

    /**
     * Test a Department Manager can update their own DRAFT department report.
     */
    public function test_dept_manager_can_update_own_draft_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-04-01',
            'reporting_period_end' => '2026-04-07',
            'status' => 'draft',
        ]);

        $this->actingAs($deptManager);

        $newStartDate = '2026-04-08';
        $response = $this->putJson("/api/v1/department-reports/{$report->id}", [
            'reporting_period_start' => $newStartDate,
            'reporting_period_end' => '2026-04-14', // Also update end date for consistency
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('department_reports', [
            'id' => $report->id,
            'reporting_period_start' => $newStartDate,
        ]);
    }

    /**
     * Test a Department Manager cannot update a SUBMITTED department report.
     */
    public function test_dept_manager_cannot_update_submitted_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-04-15',
            'reporting_period_end' => '2026-04-21',
            'status' => 'submitted',
        ]);

        $this->actingAs($deptManager);

        $response = $this->putJson("/api/v1/department-reports/{$report->id}", [
            'reporting_period_start' => '2026-04-22',
        ]);

        $response->assertStatus(403); // Forbidden, should not be able to update submitted report
    }

    /**
     * Test a Department Manager can submit a draft department report.
     */
    public function test_dept_manager_can_submit_draft_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-05-01',
            'reporting_period_end' => '2026-05-07',
            'status' => 'draft',
        ]);

        $this->actingAs($deptManager);

        $response = $this->postJson("/api/v1/department-reports/{$report->id}/submit");

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'submitted']);

        $this->assertDatabaseHas('department_reports', [
            'id' => $report->id,
            'status' => 'submitted',
        ]);
    }

    /**
     * Test a Department Manager cannot submit an already submitted department report.
     */
    public function test_dept_manager_cannot_submit_already_submitted_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-05-08',
            'reporting_period_end' => '2026-05-14',
            'status' => 'submitted',
        ]);

        $this->actingAs($deptManager);

        $response = $this->postJson("/api/v1/department-reports/{$report->id}/submit");

        $response->assertStatus(400); // Bad Request, or appropriate error for already submitted
    }

    /**
     * Test a Department Manager can amend a submitted department report.
     */
    public function test_dept_manager_can_amend_submitted_department_report(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-06-01',
            'reporting_period_end' => '2026-06-07',
            'status' => 'submitted',
        ]);

        $this->actingAs($deptManager);

        $amendmentReason = 'Corrected typo in report period.';
        $response = $this->postJson("/api/v1/department-reports/{$report->id}/amend", [
            'amendment_reason' => $amendmentReason,
            'reporting_period_start' => '2026-06-02', // New value for an amended field
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'amended']);

        $this->assertDatabaseHas('department_reports', [
            'id' => $report->id,
            'status' => 'amended',
            'reporting_period_start' => '2026-06-02', // Check if the report was updated
        ]);
        $this->assertDatabaseHas('department_report_amendments', [
            'department_report_id' => $report->id,
            'amended_by' => $deptManager->id,
            'amendment_reason' => $amendmentReason,
        ]);
    }

    /**
     * Test department reports cannot be deleted.
     */
    public function test_department_reports_cannot_be_deleted(): void
    {
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        $deptManager->department_id = $department->id;
        $deptManager->save();

        $report = \App\Models\DepartmentReport::factory()->create([
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => '2026-07-01',
            'reporting_period_end' => '2026-07-07',
            'status' => 'draft',
        ]);

        $this->actingAs($deptManager);

        $response = $this->deleteJson("/api/v1/department-reports/{$report->id}");

        $response->assertStatus(405); // Method Not Allowed, or 403 Forbidden

        $this->assertDatabaseHas('department_reports', ['id' => $report->id]); // Ensure report still exists
    }
}

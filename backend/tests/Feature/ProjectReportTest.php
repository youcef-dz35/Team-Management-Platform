<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectReportTest extends TestCase
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
     * Test an SDD can create a project report.
     */
    public function test_sdd_can_create_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        // Assign a project to the SDD
        $project = Project::factory()->create(['sdd_id' => $sdd->id]);

        $this->actingAs($sdd);

        $response = $this->postJson('/api/v1/project-reports', [
            'project_id' => $project->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['id', 'project_id', 'submitted_by', 'reporting_period_start', 'status']]);

        $this->assertDatabaseHas('project_reports', [
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
            'status' => 'draft', // New reports are 'draft' by default
        ]);
    }

    /**
     * Test validation for creating a project report.
     */
    public function test_sdd_cannot_create_project_report_with_invalid_data(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $this->actingAs($sdd);

        $response = $this->postJson('/api/v1/project-reports', [
            // Missing project_id
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['project_id']);
    }

    /**
     * Test an unauthorized user cannot create a project report.
     */
    public function test_unauthorized_user_cannot_create_project_report(): void
    {
        $project = Project::factory()->create(); // Project without specific SDD assignment
        $unauthorizedUser = User::factory()->create(); // User without SDD role

        $this->actingAs($unauthorizedUser);

        $response = $this->postJson('/api/v1/project-reports', [
            'project_id' => $project->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test an SDD can view their own project reports.
     */
    public function test_sdd_can_view_own_project_reports(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        // Project assigned to this SDD
        $project1 = Project::factory()->create(['sdd_id' => $sdd->id]);
        $project2 = Project::factory()->create(['sdd_id' => $sdd->id]);

        // Create reports for the SDD
        $report1 = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project1->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-01-01',
            'reporting_period_end' => '2026-01-07',
        ]);
        $report2 = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project2->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-01-08',
            'reporting_period_end' => '2026-01-14',
        ]);

        // Create a report for another SDD (should not be visible)
        $anotherSdd = User::factory()->create();
        $anotherSdd->assignRole($sddRole);
        $project3 = Project::factory()->create(['sdd_id' => $anotherSdd->id]);
        \App\Models\ProjectReport::factory()->create([
            'project_id' => $project3->id,
            'submitted_by' => $anotherSdd->id,
            'reporting_period_start' => '2026-01-15',
            'reporting_period_end' => '2026-01-21',
        ]);


        $this->actingAs($sdd);

        $response = $this->getJson('/api/v1/project-reports');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data'); // Only reports owned by $sdd
    }

    /**
     * Test CEO/CFO/GM/Ops Manager/Director can view all project reports.
     */
    public function test_management_can_view_all_project_reports(): void
    {
        $ceo = User::factory()->create();
        $ceo->assignRole(Role::findByName('ceo'));

        $sdd = User::factory()->create();
        $sdd->assignRole(Role::findByName('sdd'));
        $project1 = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report1 = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project1->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-02-01',
            'reporting_period_end' => '2026-02-07',
        ]);

        $anotherSdd = User::factory()->create();
        $anotherSdd->assignRole(Role::findByName('sdd'));
        $project2 = Project::factory()->create(['sdd_id' => $anotherSdd->id]);
        $report2 = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project2->id,
            'submitted_by' => $anotherSdd->id,
            'reporting_period_start' => '2026-02-08',
            'reporting_period_end' => '2026-02-14',
        ]);

        $this->actingAs($ceo);

        $response = $this->getJson('/api/v1/project-reports');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data')
                 ->assertJsonFragment(['id' => $report1->id])
                 ->assertJsonFragment(['id' => $report2->id]);
    }

    /**
     * Test an SDD can view a specific project report they own.
     */
    public function test_sdd_can_view_single_own_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-03-01',
            'reporting_period_end' => '2026-03-07',
        ]);

        $this->actingAs($sdd);

        $response = $this->getJson("/api/v1/project-reports/{$report->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $report->id]);
    }

    /**
     * Test an SDD cannot view another SDD's project report.
     */
    public function test_sdd_cannot_view_another_sdds_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $anotherSdd = User::factory()->create();
        $anotherSdd->assignRole($sddRole);
        $project = Project::factory()->create(['sdd_id' => $anotherSdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $anotherSdd->id,
            'reporting_period_start' => '2026-03-08',
            'reporting_period_end' => '2026-03-14',
        ]);

        $this->actingAs($sdd);

        $response = $this->getJson("/api/v1/project-reports/{$report->id}");

        // Returns 404 because global scope hides reports from other SDDs
        $response->assertStatus(404);
    }

    /**
     * Test an SDD can update their own DRAFT project report.
     */
    public function test_sdd_can_update_own_draft_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-04-01',
            'reporting_period_end' => '2026-04-07',
            'status' => 'draft',
        ]);

        $this->actingAs($sdd);

        $newStartDate = '2026-04-08';
        $response = $this->putJson("/api/v1/project-reports/{$report->id}", [
            'reporting_period_start' => $newStartDate,
            'reporting_period_end' => '2026-04-14', // Also update end date for consistency
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('project_reports', [
            'id' => $report->id,
            'reporting_period_start' => $newStartDate,
        ]);
    }

    /**
     * Test an SDD cannot update a SUBMITTED project report.
     */
    public function test_sdd_cannot_update_submitted_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-04-15',
            'reporting_period_end' => '2026-04-21',
            'status' => 'submitted',
        ]);

        $this->actingAs($sdd);

        $response = $this->putJson("/api/v1/project-reports/{$report->id}", [
            'reporting_period_start' => '2026-04-22',
        ]);

        $response->assertStatus(403); // Forbidden, should not be able to update submitted report
    }

    /**
     * Test an SDD can submit a draft project report.
     */
    public function test_sdd_can_submit_draft_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-05-01',
            'reporting_period_end' => '2026-05-07',
            'status' => 'draft',
        ]);

        $this->actingAs($sdd);

        $response = $this->postJson("/api/v1/project-reports/{$report->id}/submit");

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'submitted']);

        $this->assertDatabaseHas('project_reports', [
            'id' => $report->id,
            'status' => 'submitted',
        ]);
    }

    /**
     * Test an SDD cannot submit an already submitted project report.
     */
    public function test_sdd_cannot_submit_already_submitted_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-05-08',
            'reporting_period_end' => '2026-05-14',
            'status' => 'submitted',
        ]);

        $this->actingAs($sdd);

        $response = $this->postJson("/api/v1/project-reports/{$report->id}/submit");

        $response->assertStatus(400); // Bad Request, or appropriate error for already submitted
    }

    /**
     * Test an SDD can amend a submitted project report.
     */
    public function test_sdd_can_amend_submitted_project_report(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-06-01',
            'reporting_period_end' => '2026-06-07',
            'status' => 'submitted',
        ]);

        $this->actingAs($sdd);

        $amendmentReason = 'Corrected typo in report period.';
        $response = $this->postJson("/api/v1/project-reports/{$report->id}/amend", [
            'amendment_reason' => $amendmentReason,
            'reporting_period_start' => '2026-06-02', // New value for an amended field
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'amended']);

        $this->assertDatabaseHas('project_reports', [
            'id' => $report->id,
            'status' => 'amended',
            'reporting_period_start' => '2026-06-02', // Check if the report was updated
        ]);
        $this->assertDatabaseHas('project_report_amendments', [
            'project_report_id' => $report->id,
            'user_id' => $sdd->id,
            'reason' => $amendmentReason,
        ]);
    }

    /**
     * Test project reports cannot be deleted.
     */
    public function test_project_reports_cannot_be_deleted(): void
    {
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => '2026-07-01',
            'reporting_period_end' => '2026-07-07',
            'status' => 'draft',
        ]);

        $this->actingAs($sdd);

        $response = $this->deleteJson("/api/v1/project-reports/{$report->id}");

        $response->assertStatus(405); // Method Not Allowed, or 403 Forbidden

        $this->assertDatabaseHas('project_reports', ['id' => $report->id]); // Ensure report still exists
    }
}

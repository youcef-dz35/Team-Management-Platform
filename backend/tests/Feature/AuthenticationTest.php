<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed users
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'DepartmentSeeder']);
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProjectSeeder']); // This should create default users
    }

    /**
     * Test a user can log in successfully with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        // Get a user created by UserSeeder or create a new one
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user']);

        // Verify the response contains a token
        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test a user cannot log in with incorrect credentials.
     */
    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['email']);

        // Verify the user is not authenticated
        $this->assertGuest();
    }

    /**
     * Test a user can log out successfully.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Login the user first
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('token');

        // Logout the user
        $response = $this->postJson('/api/v1/auth/logout', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logout successful']);
    }

    /**
     * Test a protected route cannot be accessed without authentication.
     */
    public function test_protected_route_cannot_be_accessed_without_authentication(): void
    {
        // Attempt to access a protected route (e.g., project reports list) without a token
        $response = $this->getJson('/api/v1/project-reports');

        $response->assertStatus(401); // Unauthorized
    }

    /**
     * Test a protected route can be accessed with authentication.
     */
    public function test_protected_route_can_be_accessed_with_authentication(): void
    {
        $sdd = User::factory()->create();
        $sdd->assignRole(Role::findByName('sdd'));
        $project = Project::factory()->create(['sdd_id' => $sdd->id]);

        $this->createProjectReportWithEntry($sdd, $sdd, $project, '2026-01-01', '2026-01-07', 40.0);

        // Login the user first
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $sdd->email,
            'password' => 'password', // Assuming default password for factory user
        ]);

        $token = $loginResponse->json('token');

        // Access the protected route with the token
        $response = $this->withToken($token)->getJson('/api/v1/project-reports');

        $response->assertStatus(200); // OK
    }

    /**
     * Helper to create a ProjectReport with an entry.
     * This is duplicated from ConflictDetectionTest to make this test self-contained,
     * but in a real project, common test helpers would be in a base class or separate file.
     */
    protected function createProjectReportWithEntry(
        User $sdd,
        User $worker,
        Project $project,
        string $startDate,
        string $endDate,
        float $hours
    ): \App\Models\ProjectReport {
        $report = \App\Models\ProjectReport::factory()->create([
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => $startDate,
            'reporting_period_end' => $endDate,
            'status' => 'submitted', // Must be submitted for detection
        ]);

        \App\Models\ProjectReportEntry::factory()->create([
            'project_report_id' => $report->id,
            'employee_id' => $worker->id,
            'hours_worked' => $hours,
        ]);

        return $report;
    }
}

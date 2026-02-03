<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacIsolationTest extends TestCase
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
     * Test that a Service Delivery Director (SDD) cannot access department reports (Source B).
     */
    public function test_sdd_cannot_access_department_reports(): void
    {
        // Create an SDD user
        $sddRole = Role::findByName('sdd');
        $sdd = User::factory()->create();
        $sdd->assignRole($sddRole);

        // Authenticate as the SDD user
        $this->actingAs($sdd);

        // Attempt to access the department reports endpoint
        $response = $this->getJson('/api/v1/department-reports');

        // Assert that the request is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that a Department Manager cannot access project reports (Source A).
     */
    public function test_dept_manager_cannot_access_project_reports(): void
    {
        // Create a Department Manager user
        $deptManagerRole = Role::findByName('dept_manager');
        $deptManager = User::factory()->create();
        $deptManager->assignRole($deptManagerRole);

        // Authenticate as the Department Manager user
        $this->actingAs($deptManager);

        // Attempt to access the project reports endpoint
        $response = $this->getJson('/api/v1/project-reports');

        // Assert that the request is forbidden
        $response->assertStatus(403);
    }
}
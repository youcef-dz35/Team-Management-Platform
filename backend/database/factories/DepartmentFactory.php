<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Ensure there's a dept_manager role
        $deptManagerRole = Role::firstOrCreate(['name' => 'dept_manager'], ['guard_name' => 'web']);

        // Find an existing Dept Manager or create one if none exists
        $deptManager = User::role('dept_manager')->inRandomOrder()->first();

        if (!$deptManager) {
            $deptManager = User::factory()->create();
            $deptManager->assignRole($deptManagerRole);
        }

        return [
            'name' => $this->faker->unique()->word() . ' Department',
            'manager_id' => $deptManager->id,
        ];
    }
}

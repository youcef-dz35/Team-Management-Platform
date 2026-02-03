<?php

namespace Database\Factories;

use App\Models\DepartmentReport;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class DepartmentReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DepartmentReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Ensure there's a dept_manager role
        $deptManagerRole = Role::firstOrCreate(['name' => 'dept_manager'], ['guard_name' => 'web']);

        // Find an existing Dept Manager user or create one
        $deptManager = User::role('dept_manager')->inRandomOrder()->first();
        if (!$deptManager) {
            $deptManager = User::factory()->create();
            $deptManager->assignRole($deptManagerRole);
        }

        // Find an existing Department or create one associated with this Dept Manager
        $department = Department::where('manager_id', $deptManager->id)->inRandomOrder()->first();
        if (!$department) {
            $department = Department::factory()->create(['manager_id' => $deptManager->id]);
        }

        $startDate = Carbon::parse($this->faker->dateTimeBetween('-3 months', 'now'))->startOfWeek();
        $endDate = Carbon::parse($startDate)->endOfWeek();

        return [
            'department_id' => $department->id,
            'submitted_by' => $deptManager->id,
            'reporting_period_start' => $startDate->format('Y-m-d'),
            'reporting_period_end' => $endDate->format('Y-m-d'),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'amended']),
        ];
    }
}

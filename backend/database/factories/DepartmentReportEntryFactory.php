<?php

namespace Database\Factories;

use App\Models\DepartmentReportEntry;
use App\Models\DepartmentReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentReportEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DepartmentReportEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'department_report_id' => DepartmentReport::factory(),
            'employee_id' => User::factory(),
            'hours_worked' => $this->faker->randomFloat(2, 0, 60),
            'tasks_completed' => $this->faker->numberBetween(0, 20),
            'status' => $this->faker->randomElement(['productive', 'underperforming', 'on_leave']),
            'work_description' => $this->faker->optional()->paragraph(),
        ];
    }
}

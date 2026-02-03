<?php

namespace Database\Factories;

use App\Models\ProjectReportEntry;
use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectReportEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectReportEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'project_report_id' => ProjectReport::factory(),
            'employee_id' => User::factory(),
            'hours_worked' => $this->faker->randomFloat(2, 0, 60),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}

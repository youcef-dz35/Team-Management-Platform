<?php

namespace Database\Factories;

use App\Models\ProjectReport;
use App\Models\Project;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProjectReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Ensure there's an SDD role
        $sddRole = Role::firstOrCreate(['name' => 'sdd'], ['guard_name' => 'web']);

        // Find an existing SDD user or create one
        $sdd = User::role('sdd')->inRandomOrder()->first();
        if (!$sdd) {
            $sdd = User::factory()->create();
            $sdd->assignRole($sddRole);
        }

        // Find an existing Project or create one associated with this SDD
        $project = Project::where('sdd_id', $sdd->id)->inRandomOrder()->first();
        if (!$project) {
            $project = Project::factory()->create(['sdd_id' => $sdd->id]);
        }

        $startDate = Carbon::parse($this->faker->dateTimeBetween('-3 months', 'now'))->startOfWeek();
        $endDate = Carbon::parse($startDate)->endOfWeek();

        return [
            'project_id' => $project->id,
            'submitted_by' => $sdd->id,
            'reporting_period_start' => $startDate->format('Y-m-d'),
            'reporting_period_end' => $endDate->format('Y-m-d'),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'amended']),
        ];
    }
}

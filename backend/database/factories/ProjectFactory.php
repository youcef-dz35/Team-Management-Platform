<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Ensure there's an SDD role
        $sddRole = Role::firstOrCreate(['name' => 'sdd'], ['guard_name' => 'web']);

        // Find an existing SDD or create one if none exists
        $sdd = User::role('sdd')->inRandomOrder()->first();

        if (!$sdd) {
            $sdd = User::factory()->create();
            $sdd->assignRole($sddRole);
        }
        
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'name' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(),
            'sdd_id' => $sdd->id,
            'status' => $this->faker->randomElement(['active', 'completed', 'on_hold', 'cancelled']),
            'budget' => $this->faker->randomFloat(2, 10000, 1000000),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }
}

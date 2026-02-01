<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the 5 departments as per specification
        $departments = [
            ['name' => 'Frontend'],
            ['name' => 'Backend'],
            ['name' => 'Mobile'],
            ['name' => 'AI'],
            ['name' => 'BD'], // Business Development
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate($dept);
        }

        $this->command->info('✅ Created 5 departments: Frontend, Backend, Mobile, AI, BD');
    }
}

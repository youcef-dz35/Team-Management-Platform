<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get SDDs
        $sdd1 = User::where('email', 'sdd1@example.com')->first();
        $sdd2 = User::where('email', 'sdd2@example.com')->first();
        $sdd3 = User::where('email', 'sdd3@example.com')->first();

        // Get workers
        $workerBackend1 = User::where('email', 'worker.backend1@example.com')->first();
        $workerBackend2 = User::where('email', 'worker.backend2@example.com')->first();
        $workerFrontend1 = User::where('email', 'worker.frontend1@example.com')->first();
        $workerFrontend2 = User::where('email', 'worker.frontend2@example.com')->first();
        $workerMobile1 = User::where('email', 'worker.mobile1@example.com')->first();
        $workerMobile2 = User::where('email', 'worker.mobile2@example.com')->first();

        // Project 1: E-Commerce Platform (Backend SDD)
        $project1 = Project::firstOrCreate(
            ['name' => 'E-Commerce Platform'],
            [
                'description' => 'Building a scalable e-commerce platform with Laravel and React',
                'sdd_id' => $sdd1->id,
                'status' => 'active',
                'budget' => 150000.00,
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
            ]
        );

        // Assign workers to Project 1
        $project1->assignedWorkers()->syncWithoutDetaching([
            $workerBackend1->id => ['allocated_hours' => 40],
            $workerBackend2->id => ['allocated_hours' => 40],
            $workerFrontend1->id => ['allocated_hours' => 20],
        ]);

        // Project 2: Mobile App Redesign (Frontend SDD)
        $project2 = Project::firstOrCreate(
            ['name' => 'Mobile App Redesign'],
            [
                'description' => 'Complete redesign of the mobile application UI/UX',
                'sdd_id' => $sdd2->id,
                'status' => 'active',
                'budget' => 80000.00,
                'start_date' => '2026-02-01',
                'end_date' => '2026-05-31',
            ]
        );

        // Assign workers to Project 2
        $project2->assignedWorkers()->syncWithoutDetaching([
            $workerFrontend1->id => ['allocated_hours' => 20],
            $workerFrontend2->id => ['allocated_hours' => 40],
            $workerMobile1->id => ['allocated_hours' => 40],
        ]);

        // Project 3: API Integration (Mobile SDD)
        $project3 = Project::firstOrCreate(
            ['name' => 'API Integration'],
            [
                'description' => 'Integrate third-party APIs for payment and analytics',
                'sdd_id' => $sdd3->id,
                'status' => 'active',
                'budget' => 60000.00,
                'start_date' => '2026-01-15',
                'end_date' => '2026-04-15',
            ]
        );

        // Assign workers to Project 3
        $project3->assignedWorkers()->syncWithoutDetaching([
            $workerMobile1->id => ['allocated_hours' => 20],
            $workerMobile2->id => ['allocated_hours' => 40],
            $workerBackend1->id => ['allocated_hours' => 10],
        ]);

        $this->command->info('✅ Created 3 sample projects with worker assignments');
        $this->command->info('   - E-Commerce Platform (SDD1, 3 workers)');
        $this->command->info('   - Mobile App Redesign (SDD2, 3 workers)');
        $this->command->info('   - API Integration (SDD3, 3 workers)');
    }
}

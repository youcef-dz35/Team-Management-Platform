<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the 8 roles as per Constitution
        $roles = [
            ['name' => 'ceo', 'guard_name' => 'web'],
            ['name' => 'cfo', 'guard_name' => 'web'],
            ['name' => 'gm', 'guard_name' => 'web'],
            ['name' => 'ops_manager', 'guard_name' => 'web'],
            ['name' => 'director', 'guard_name' => 'web'],
            ['name' => 'sdd', 'guard_name' => 'web'],
            ['name' => 'dept_manager', 'guard_name' => 'web'],
            ['name' => 'worker', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('✅ Created 8 roles: CEO, CFO, GM, Ops Manager, Director, SDD, Dept Manager, Worker');
    }
}

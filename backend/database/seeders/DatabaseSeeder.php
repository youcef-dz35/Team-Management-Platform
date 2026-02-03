<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');
        $this->command->newLine();

        // Order matters: roles → departments → users → projects → reports → conflicts
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            ReportSeeder::class,
            ConflictAlertSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📧 Login credentials:');
        $this->command->info('   CEO:          ceo@example.com / password');
        $this->command->info('   CFO:          cfo@example.com / password');
        $this->command->info('   GM:           gm@example.com / password');
        $this->command->info('   Ops Manager:  ops@example.com / password');
        $this->command->info('   Director:     dammy@example.com / password');
        $this->command->info('   SDD:          sdd1@example.com / password');
        $this->command->info('   Dept Manager: deptmgr.backend@example.com / password');
        $this->command->info('   Worker:       worker.backend1@example.com / password');
    }
}

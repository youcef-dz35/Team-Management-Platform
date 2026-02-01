<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $frontend = Department::where('name', 'Frontend')->first();
        $backend = Department::where('name', 'Backend')->first();
        $mobile = Department::where('name', 'Mobile')->first();
        $ai = Department::where('name', 'AI')->first();
        $bd = Department::where('name', 'BD')->first();

        // CEO
        $ceo = User::firstOrCreate(
            ['email' => 'ceo@example.com'],
            [
                'name' => 'CEO User',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP001',
            ]
        );
        $ceo->assignRole('ceo');

        // CFO
        $cfo = User::firstOrCreate(
            ['email' => 'cfo@example.com'],
            [
                'name' => 'CFO User',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP002',
            ]
        );
        $cfo->assignRole('cfo');

        // GM (Ramzi)
        $gm = User::firstOrCreate(
            ['email' => 'gm@example.com'],
            [
                'name' => 'Ramzi (General Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP003',
            ]
        );
        $gm->assignRole('gm');

        // Operations Manager (Youcef)
        $opsManager = User::firstOrCreate(
            ['email' => 'ops@example.com'],
            [
                'name' => 'Youcef (Operations Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP004',
            ]
        );
        $opsManager->assignRole('ops_manager');

        // Directors (Dammy and Mami)
        $directorDammy = User::firstOrCreate(
            ['email' => 'dammy@example.com'],
            [
                'name' => 'Dammy (Director)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP005',
            ]
        );
        $directorDammy->assignRole('director');

        $directorMami = User::firstOrCreate(
            ['email' => 'mami@example.com'],
            [
                'name' => 'Mami (Director)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP006',
            ]
        );
        $directorMami->assignRole('director');

        // SDDs (Project Managers) - 3 examples
        $sdd1 = User::firstOrCreate(
            ['email' => 'sdd1@example.com'],
            [
                'name' => 'John Doe (SDD)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP101',
                'department_id' => $backend->id,
            ]
        );
        $sdd1->assignRole('sdd');

        $sdd2 = User::firstOrCreate(
            ['email' => 'sdd2@example.com'],
            [
                'name' => 'Jane Smith (SDD)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP102',
                'department_id' => $frontend->id,
            ]
        );
        $sdd2->assignRole('sdd');

        $sdd3 = User::firstOrCreate(
            ['email' => 'sdd3@example.com'],
            [
                'name' => 'Bob Johnson (SDD)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP103',
                'department_id' => $mobile->id,
            ]
        );
        $sdd3->assignRole('sdd');

        // Department Managers - one per department
        $deptMgrFrontend = User::firstOrCreate(
            ['email' => 'deptmgr.frontend@example.com'],
            [
                'name' => 'Alice Frontend (Dept Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP201',
                'department_id' => $frontend->id,
            ]
        );
        $deptMgrFrontend->assignRole('dept_manager');
        $frontend->update(['manager_id' => $deptMgrFrontend->id]);

        $deptMgrBackend = User::firstOrCreate(
            ['email' => 'deptmgr.backend@example.com'],
            [
                'name' => 'Charlie Backend (Dept Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP202',
                'department_id' => $backend->id,
            ]
        );
        $deptMgrBackend->assignRole('dept_manager');
        $backend->update(['manager_id' => $deptMgrBackend->id]);

        $deptMgrMobile = User::firstOrCreate(
            ['email' => 'deptmgr.mobile@example.com'],
            [
                'name' => 'Diana Mobile (Dept Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP203',
                'department_id' => $mobile->id,
            ]
        );
        $deptMgrMobile->assignRole('dept_manager');
        $mobile->update(['manager_id' => $deptMgrMobile->id]);

        $deptMgrAI = User::firstOrCreate(
            ['email' => 'deptmgr.ai@example.com'],
            [
                'name' => 'Eve AI (Dept Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP204',
                'department_id' => $ai->id,
            ]
        );
        $deptMgrAI->assignRole('dept_manager');
        $ai->update(['manager_id' => $deptMgrAI->id]);

        $deptMgrBD = User::firstOrCreate(
            ['email' => 'deptmgr.bd@example.com'],
            [
                'name' => 'Frank BD (Dept Manager)',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP205',
                'department_id' => $bd->id,
            ]
        );
        $deptMgrBD->assignRole('dept_manager');
        $bd->update(['manager_id' => $deptMgrBD->id]);

        // Workers - 2 per department
        $workers = [
            ['email' => 'worker.frontend1@example.com', 'name' => 'Worker Frontend 1', 'employee_id' => 'EMP301', 'department_id' => $frontend->id],
            ['email' => 'worker.frontend2@example.com', 'name' => 'Worker Frontend 2', 'employee_id' => 'EMP302', 'department_id' => $frontend->id],
            ['email' => 'worker.backend1@example.com', 'name' => 'Worker Backend 1', 'employee_id' => 'EMP303', 'department_id' => $backend->id],
            ['email' => 'worker.backend2@example.com', 'name' => 'Worker Backend 2', 'employee_id' => 'EMP304', 'department_id' => $backend->id],
            ['email' => 'worker.mobile1@example.com', 'name' => 'Worker Mobile 1', 'employee_id' => 'EMP305', 'department_id' => $mobile->id],
            ['email' => 'worker.mobile2@example.com', 'name' => 'Worker Mobile 2', 'employee_id' => 'EMP306', 'department_id' => $mobile->id],
            ['email' => 'worker.ai1@example.com', 'name' => 'Worker AI 1', 'employee_id' => 'EMP307', 'department_id' => $ai->id],
            ['email' => 'worker.ai2@example.com', 'name' => 'Worker AI 2', 'employee_id' => 'EMP308', 'department_id' => $ai->id],
            ['email' => 'worker.bd1@example.com', 'name' => 'Worker BD 1', 'employee_id' => 'EMP309', 'department_id' => $bd->id],
            ['email' => 'worker.bd2@example.com', 'name' => 'Worker BD 2', 'employee_id' => 'EMP310', 'department_id' => $bd->id],
        ];

        foreach ($workers as $workerData) {
            $worker = User::firstOrCreate(
                ['email' => $workerData['email']],
                array_merge($workerData, ['password' => Hash::make('password')])
            );
            $worker->assignRole('worker');
        }

        $this->command->info('✅ Created test users for all 8 roles');
        $this->command->info('   - CEO, CFO, GM, Ops Manager');
        $this->command->info('   - 2 Directors (Dammy, Mami)');
        $this->command->info('   - 3 SDDs');
        $this->command->info('   - 5 Department Managers (one per department)');
        $this->command->info('   - 10 Workers (2 per department)');
        $this->command->info('   - Default password: password');
    }
}

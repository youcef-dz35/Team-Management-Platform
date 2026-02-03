<?php
// Test Script for Department Reports via Tinker
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\DepartmentReport;
use Illuminate\Support\Facades\Auth;

try {
    echo "--- Setup ---\n";
    // 1. Setup Data: Ensure we have a Manager and an Employee in the SAME department
    $manager = User::whereHas('roles', fn($q) => $q->where('name', 'ops_manager'))->first();
    if (!$manager) {
        $manager = User::factory()->create();
        $manager->assignRole('ops_manager');
    }

    // Assign Manager to a department if not already
    $dept = Department::first();
    if (!$dept) {
        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG', 'budget' => 50000]);
    }
    $manager->department_id = $dept->id;
    $manager->save();

    // Create an Employee in the SAME department
    $employee = User::factory()->create(['department_id' => $dept->id, 'name' => 'Eng Employee']);

    // Create an Employee in DIFFERENT department
    $otherDept = Department::create(['name' => 'HR', 'code' => 'HR', 'budget' => 10000]);
    $otherEmployee = User::factory()->create(['department_id' => $otherDept->id, 'name' => 'HR Employee']);

    $project = Project::first();

    echo "Manager: {$manager->name} (Dept: {$dept->name})\n";
    echo "Employee (Same Dept): {$employee->name}\n";
    echo "Employee (Other Dept): {$otherEmployee->name}\n";

    Auth::login($manager);

    echo "\n--- 1. Testing Valid Allocation (Same Dept) ---\n";
    $controller = app(App\Http\Controllers\Api\V1\DepartmentReportController::class);

    $validRequest = new \App\Http\Requests\StoreDepartmentReportRequest([
        'department_id' => $dept->id,
        'period_start' => '2026-03-02',
        'period_end' => '2026-03-08',
        'entries' => [
            ['user_id' => $employee->id, 'project_id' => $project->id, 'hours_allocated' => 40, 'notes' => 'Dev Work']
        ]
    ]);
    $validRequest->setUserResolver(fn() => $manager);

    // We need to simulate validation? The form request validation runs before controller in real app.
    // In Tinker, direct controller call skips FormRequest validation unless manually triggered.
    // For this test, we trust the validation logic exists (we wrote it) and test the Controller's DB insertion.
    // However, to really test the "Other Dept" restriction, we need to invoke the Validator.

    $validator = \Illuminate\Support\Facades\Validator::make($validRequest->all(), $validRequest->rules());
    if ($validator->fails()) {
        echo "Valid Request FAILED Validation! " . print_r($validator->errors()->all(), true) . "\n";
    } else {
        echo "Valid Request Passed Validation.\n";
        $response = $controller->store($validRequest);
        $content = json_decode($response->getContent());
        echo "Report Created! ID: {$content->id}\n";
    }

    echo "\n--- 2. Testing INVALID Allocation (Other Dept) ---\n";
    $invalidRequest = new \App\Http\Requests\StoreDepartmentReportRequest([
        'department_id' => $dept->id,
        'period_start' => '2026-03-09',
        'period_end' => '2026-03-15',
        'entries' => [
            ['user_id' => $otherEmployee->id, 'project_id' => $project->id, 'hours_allocated' => 40, 'notes' => 'Should Fail']
        ]
    ]);
    $invalidRequest->setUserResolver(fn() => $manager);

    $validatorInvalid = \Illuminate\Support\Facades\Validator::make($invalidRequest->all(), $invalidRequest->rules());

    if ($validatorInvalid->fails()) {
        echo "Invalid Request CORRECTLY Failed Validation:\n";
        print_r($validatorInvalid->errors()->all());
    } else {
        echo "!!! ERROR: Invalid Request PASSED Validation! (Security Risk)\n";
    }

} catch (\Exception $e) {
    echo "\n!!! ERROR !!!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

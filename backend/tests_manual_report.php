<?php
// Test Script for Project Reports via Tinker
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectReport;
use Illuminate\Support\Facades\Auth;

try {
    echo "--- Setup ---\n";
    $sdd = User::role('sdd')->firstOrFail();
    $project = Project::where('sdd_id', $sdd->id)->first();

    if (!$project) {
        // Assign one if missing (cleanup from previous tests)
        $project = Project::first();
        $project->sdd_id = $sdd->id;
        $project->save();
    }

    echo "Using SDD: {$sdd->name} (ID: {$sdd->id})\n";
    echo "Using Project: {$project->name} (ID: {$project->id})\n";

    // Login as SDD
    Auth::login($sdd);

    // Clean up old reports for this week to avoid unique constraint
    ProjectReport::where('project_id', $project->id)
        ->where('period_start', '2026-02-09')
        ->delete();

    // 1. Create Draft
    echo "\n--- 1. Testing Create Draft ---\n";
    $controller = app(App\Http\Controllers\Api\V1\ProjectReportController::class);

    $request = new \App\Http\Requests\StoreProjectReportRequest([
        'project_id' => $project->id,
        'period_start' => '2026-02-09',
        'period_end' => '2026-02-15',
        'entries' => [
            ['user_id' => $sdd->id, 'hours_worked' => 40, 'notes' => 'Management']
        ],
        'comments' => 'Initial Draft'
    ]);

    // Manual validation simulation if needed, but Controller call handles logic
    $response = $controller->store($request);
    $report = $response->getData()->entries ? $response->getData() : null; // Adaptation for JsonResponse
    // Actually the response is JsonResponse, need to decode
    $content = json_decode($response->getContent());
    $reportId = $content->id;

    echo "Draft Created! ID: {$reportId}, Status: {$content->status}\n";

    // 2. Submit Report
    echo "\n--- 2. Testing Submit ---\n";
    $reportModel = ProjectReport::find($reportId);
    $submitReq = new \Illuminate\Http\Request();
    $controller->submit($submitReq, $reportModel);

    $reportModel->refresh();
    echo "Report Submitted! Status: {$reportModel->status}\n";

    // 3. Amend Report
    echo "\n--- 3. Testing Amendment ---\n";
    $amendReq = new \App\Http\Requests\AmendProjectReportRequest([
        'reason' => 'Forgot overtime',
        'entries' => [
            ['user_id' => $sdd->id, 'hours_worked' => 45, 'notes' => 'Management + OT']
        ],
        'comments' => 'Amended Report'
    ]);

    // Hack: Route binding logic usually handles injection, but here we pass model directly
    $amendResponse = $controller->amend($amendReq, $reportModel);
    $amendContent = json_decode($amendResponse->getContent());

    echo "Report Amended! New Comments: {$amendContent->comments}\n";
    echo "Amendment Count: " . count($amendContent->amendments) . "\n";
    echo "New Hours: " . $amendContent->entries[0]->hours_worked . "\n";

    echo "\n--- SUCCESS ---\n";

} catch (\Exception $e) {
    echo "\n!!! ERROR !!!\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConflictAlertController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'backend-api',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// API V1 Routes
Route::prefix('v1')->group(function () {

    // Authentication Routes (Public)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1'); // 5 attempts per minute

        // Protected Auth Routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Protected API Routes
    Route::middleware('auth:sanctum')->group(function () {
        // User routes will go here
        // Project routes will go here
        Route::get('projects', [App\Http\Controllers\Api\V1\ProjectController::class, 'index']);
        Route::get('projects/{project}/assigned-users', [App\Http\Controllers\Api\V1\ProjectController::class, 'assignedUsers']);
        // Department routes will go here
        Route::get('departments', [App\Http\Controllers\Api\V1\DepartmentController::class, 'index']);
        Route::get('departments/{department}/employees', [App\Http\Controllers\Api\V1\DepartmentController::class, 'employees']);

        // ================================================================
        // SOURCE A: Project Reports - ISOLATED FROM DEPARTMENT MANAGERS
        // ================================================================
        Route::middleware(['source.a', 'log.access'])->group(function () {
            Route::post('project-reports/{project_report}/submit', [App\Http\Controllers\Api\V1\ProjectReportController::class, 'submit']);
            Route::post('project-reports/{project_report}/amend', [App\Http\Controllers\Api\V1\ProjectReportController::class, 'amend']);
            Route::apiResource('project-reports', App\Http\Controllers\Api\V1\ProjectReportController::class);

            // Project Report Entries
            Route::get('project-reports/{project_report}/entries', [App\Http\Controllers\Api\V1\ProjectReportEntryController::class, 'index']);
            Route::post('project-reports/{project_report}/entries', [App\Http\Controllers\Api\V1\ProjectReportEntryController::class, 'store']);
            Route::get('project-reports/{project_report}/entries/{entry}', [App\Http\Controllers\Api\V1\ProjectReportEntryController::class, 'show']);
            Route::put('project-reports/{project_report}/entries/{entry}', [App\Http\Controllers\Api\V1\ProjectReportEntryController::class, 'update']);
            Route::delete('project-reports/{project_report}/entries/{entry}', [App\Http\Controllers\Api\V1\ProjectReportEntryController::class, 'destroy']);
        });

        // ================================================================
        // SOURCE B: Department Reports - ISOLATED FROM SDDs
        // ================================================================
        Route::middleware(['source.b', 'log.access'])->group(function () {
            Route::post('department-reports/{department_report}/submit', [App\Http\Controllers\Api\V1\DepartmentReportController::class, 'submit']);
            Route::post('department-reports/{department_report}/amend', [App\Http\Controllers\Api\V1\DepartmentReportController::class, 'amend']);
            Route::apiResource('department-reports', App\Http\Controllers\Api\V1\DepartmentReportController::class);

            // Department Report Entries
            Route::get('department-reports/{department_report}/entries', [App\Http\Controllers\Api\V1\DepartmentReportEntryController::class, 'index']);
            Route::post('department-reports/{department_report}/entries', [App\Http\Controllers\Api\V1\DepartmentReportEntryController::class, 'store']);
            Route::get('department-reports/{department_report}/entries/{entry}', [App\Http\Controllers\Api\V1\DepartmentReportEntryController::class, 'show']);
            Route::put('department-reports/{department_report}/entries/{entry}', [App\Http\Controllers\Api\V1\DepartmentReportEntryController::class, 'update']);
            Route::delete('department-reports/{department_report}/entries/{entry}', [App\Http\Controllers\Api\V1\DepartmentReportEntryController::class, 'destroy']);
        });

        // ================================================================
        // CONFLICT ALERTS - CEO, CFO, GM, OPS MANAGER ONLY
        // ================================================================
        Route::middleware('role:ceo,cfo,gm,ops_manager')->prefix('conflicts')->group(function () {
            Route::get('/', [ConflictAlertController::class, 'index']);
            Route::get('/stats', [ConflictAlertController::class, 'stats']);
            Route::get('/{conflictAlert}', [ConflictAlertController::class, 'show']);
            Route::post('/{conflictAlert}/resolve', [ConflictAlertController::class, 'resolve']);
            Route::post('/run-detection', [ConflictAlertController::class, 'runDetection']);
        });

        // ================================================================
        // AUDIT LOGS - CEO AND CFO ONLY
        // ================================================================
        Route::middleware('role:ceo,cfo')->prefix('audit-logs')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\V1\AuditLogController::class, 'index']);
            Route::get('/{auditLog}', [App\Http\Controllers\Api\V1\AuditLogController::class, 'show']);
        });

        // ================================================================
        // ROLE-SPECIFIC DASHBOARDS
        // ================================================================
        Route::prefix('dashboard')->group(function () {
            // SDD Dashboard
            Route::middleware('role:sdd')
                ->get('/sdd', [App\Http\Controllers\Api\V1\SddDashboardController::class, 'index']);

            // Department Manager Dashboard
            Route::middleware('role:dept_manager')
                ->get('/dept-manager', [App\Http\Controllers\Api\V1\DeptManagerDashboardController::class, 'index']);

            // GM Dashboard (also accessible by CEO, CFO, Ops Manager)
            Route::middleware('role:gm,ceo,cfo,ops_manager')
                ->get('/gm', [App\Http\Controllers\Api\V1\GmDashboardController::class, 'index']);
        });
    });
});

// Legacy route (can be removed later)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConflictAlert;
use App\Models\Department;
use App\Models\DepartmentReport;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GmDashboardController extends Controller
{
    /**
     * Get dashboard data for GM role.
     */
    public function index(): JsonResponse
    {
        // Conflict stats
        $conflictsTotal = ConflictAlert::count();
        $conflictsOpen = ConflictAlert::open()->count();
        $conflictsEscalated = ConflictAlert::escalated()->count();
        $conflictsResolved = ConflictAlert::where('status', 'resolved')->count();

        // Report stats
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');

        $projectReportsTotal = ProjectReport::withoutGlobalScopes()->count();
        $projectReportsThisWeek = ProjectReport::withoutGlobalScopes()
            ->whereIn('status', ['submitted', 'amended'])
            ->where('reporting_period_start', '>=', $weekStart)
            ->where('reporting_period_end', '<=', $weekEnd)
            ->count();

        $deptReportsTotal = DepartmentReport::withoutGlobalScopes()->count();
        $deptReportsThisWeek = DepartmentReport::withoutGlobalScopes()
            ->whereIn('status', ['submitted', 'amended'])
            ->where('reporting_period_start', '>=', $weekStart)
            ->where('reporting_period_end', '<=', $weekEnd)
            ->count();

        // Team overview
        $totalEmployees = User::whereNull('deleted_at')->count();
        $totalDepartments = Department::count();
        $totalProjects = Project::where('status', 'active')->count();

        // Recent conflicts
        $recentConflicts = ConflictAlert::with(['employee.department'])
            ->orderByRaw("CASE WHEN status = 'escalated' THEN 0 WHEN status = 'open' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($conflict) {
                return [
                    'id' => $conflict->id,
                    'employee_name' => $conflict->employee->name ?? 'Unknown',
                    'department_name' => $conflict->employee->department->name ?? 'Unknown',
                    'discrepancy' => (float) $conflict->discrepancy,
                    'status' => $conflict->status,
                    'created_at' => $conflict->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'conflicts' => [
                'total' => $conflictsTotal,
                'open' => $conflictsOpen,
                'escalated' => $conflictsEscalated,
                'resolved' => $conflictsResolved,
            ],
            'reports' => [
                'projectReports' => [
                    'total' => $projectReportsTotal,
                    'thisWeek' => $projectReportsThisWeek,
                ],
                'departmentReports' => [
                    'total' => $deptReportsTotal,
                    'thisWeek' => $deptReportsThisWeek,
                ],
            ],
            'recentConflicts' => $recentConflicts,
            'teamOverview' => [
                'totalEmployees' => $totalEmployees,
                'totalDepartments' => $totalDepartments,
                'totalProjects' => $totalProjects,
            ],
        ]);
    }
}

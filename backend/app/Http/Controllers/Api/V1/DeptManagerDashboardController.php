<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DepartmentReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeptManagerDashboardController extends Controller
{
    /**
     * Get dashboard data for Department Manager role.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $department = $user->department;

        // Employee count in department
        $employeeCount = User::where('department_id', $user->department_id)->count();

        // Get report stats (global scope will filter to this manager's department)
        $reportsTotal = DepartmentReport::count();
        $reportsDraft = DepartmentReport::where('status', 'draft')->count();
        $reportsSubmitted = DepartmentReport::whereIn('status', ['submitted', 'amended'])->count();

        // This week's reports
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');
        $reportsThisWeek = DepartmentReport::whereIn('status', ['submitted', 'amended'])
            ->where('reporting_period_start', '>=', $weekStart)
            ->where('reporting_period_end', '<=', $weekEnd)
            ->count();

        // Recent reports
        $recentReports = DepartmentReport::with(['entries'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'period_start' => $report->reporting_period_start?->format('Y-m-d'),
                    'period_end' => $report->reporting_period_end?->format('Y-m-d'),
                    'status' => $report->status,
                    'entry_count' => $report->entries->count(),
                    'total_hours' => $report->entries->sum('hours_worked'),
                ];
            });

        return response()->json([
            'department' => [
                'id' => $department->id ?? 0,
                'name' => $department->name ?? 'Unknown',
                'employee_count' => $employeeCount,
            ],
            'reports' => [
                'total' => $reportsTotal,
                'draft' => $reportsDraft,
                'submitted' => $reportsSubmitted,
                'thisWeek' => $reportsThisWeek,
            ],
            'recentReports' => $recentReports,
        ]);
    }
}

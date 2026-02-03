<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SddDashboardController extends Controller
{
    /**
     * Get dashboard data for SDD role.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        // Get project stats for this SDD
        $projectsTotal = Project::where('sdd_id', $user->id)->count();
        $projectsActive = Project::where('sdd_id', $user->id)->where('status', 'active')->count();

        // Get report stats (global scope will filter to this SDD's reports)
        $reportsTotal = ProjectReport::count();
        $reportsDraft = ProjectReport::where('status', 'draft')->count();
        $reportsSubmitted = ProjectReport::whereIn('status', ['submitted', 'amended'])->count();

        // This week's reports
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');
        $reportsThisWeek = ProjectReport::whereIn('status', ['submitted', 'amended'])
            ->where('reporting_period_start', '>=', $weekStart)
            ->where('reporting_period_end', '<=', $weekEnd)
            ->count();

        // Recent reports
        $recentReports = ProjectReport::with(['project', 'entries'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'project_name' => $report->project->name ?? 'Unknown Project',
                    'period_start' => $report->reporting_period_start?->format('Y-m-d'),
                    'period_end' => $report->reporting_period_end?->format('Y-m-d'),
                    'status' => $report->status,
                    'entry_count' => $report->entries->count(),
                ];
            });

        return response()->json([
            'projects' => [
                'total' => $projectsTotal,
                'active' => $projectsActive,
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

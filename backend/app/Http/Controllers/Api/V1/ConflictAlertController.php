<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\WeeklyConflictDetectionJob;
use App\Models\ConflictAlert;
use App\Services\ConflictDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConflictAlertController extends Controller
{
    /**
     * Display a listing of conflict alerts.
     * Only accessible by CEO, CFO, GM, and Ops Manager.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Only executives can view conflict alerts
        if (!$user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ConflictAlert::with(['employee', 'resolver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Default: show unresolved first
        $query->orderByRaw("CASE WHEN status = 'escalated' THEN 0 WHEN status = 'open' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate(20));
    }

    /**
     * Display a specific conflict alert with details.
     */
    public function show(ConflictAlert $conflictAlert): JsonResponse
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conflictAlert->load(['employee.department', 'resolver']);

        return response()->json($conflictAlert);
    }

    /**
     * Resolve a conflict alert.
     */
    public function resolve(Request $request, ConflictAlert $conflictAlert): JsonResponse
    {
        $user = Auth::user();

        // Only executives can resolve conflicts
        if (!$user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'resolution_notes' => 'required|string|min:10',
        ]);

        if ($conflictAlert->status === 'resolved') {
            return response()->json(['message' => 'Conflict is already resolved'], 422);
        }

        $conflictAlert->resolve($user, $request->resolution_notes);

        return response()->json([
            'message' => 'Conflict resolved successfully',
            'conflict' => $conflictAlert->fresh(['employee', 'resolver']),
        ]);
    }

    /**
     * Get conflict statistics/summary.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Use a single query with grouping instead of 5 separate queries
        $stats = \Illuminate\Support\Facades\DB::table('conflict_alerts')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = \'open\' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = \'escalated\' THEN 1 ELSE 0 END) as escalated,
                SUM(CASE WHEN status = \'resolved\' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status != \'resolved\' THEN 1 ELSE 0 END) as unresolved
            ')
            ->first();

        return response()->json([
            'total' => (int) $stats->total,
            'open' => (int) $stats->open,
            'escalated' => (int) $stats->escalated,
            'resolved' => (int) $stats->resolved,
            'unresolved' => (int) $stats->unresolved,
        ]);
    }

    /**
     * Manually trigger conflict detection (for testing/admin).
     * Only CEO and CFO can trigger this.
     */
    public function runDetection(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['ceo', 'cfo'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        // Dispatch job (can be async or sync based on config)
        WeeklyConflictDetectionJob::dispatch(
            $request->period_start,
            $request->period_end
        );

        return response()->json([
            'message' => 'Conflict detection job dispatched',
            'period' => [
                'start' => $request->period_start,
                'end' => $request->period_end,
            ],
        ]);
    }
}

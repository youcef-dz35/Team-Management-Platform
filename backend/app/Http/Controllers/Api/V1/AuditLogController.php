<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Audit Log Controller
 *
 * Provides read-only access to audit logs for compliance review.
 * Restricted to CEO and CFO only (God-mode).
 */
class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        // Filter by auditable type (model class)
        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Filter by specific record
        if ($request->has('auditable_id')) {
            $query->where('auditable_id', $request->auditable_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $perPage = min($request->get('per_page', 50), 100);
        $auditLogs = $query->paginate($perPage);

        return response()->json([
            'data' => $auditLogs->items(),
            'meta' => [
                'current_page' => $auditLogs->currentPage(),
                'last_page' => $auditLogs->lastPage(),
                'per_page' => $auditLogs->perPage(),
                'total' => $auditLogs->total(),
            ],
        ]);
    }

    /**
     * Display the specified audit log entry.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user:id,name,email');

        return response()->json([
            'data' => $auditLog,
        ]);
    }
}

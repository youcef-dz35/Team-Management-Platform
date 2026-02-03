<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Source A Isolation Middleware
 *
 * CONSTITUTION PRINCIPLE I - Zero Trust Architecture:
 * Department Managers MUST NOT have access to Source A (Project Reports) under any circumstance.
 * This middleware blocks dept_manager role from accessing Source A routes.
 */
class SourceAIsolation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // CRITICAL: Department Managers CANNOT access Source A (Project Reports)
        if ($user->hasRole('dept_manager')) {
            // Log the unauthorized access attempt
            \App\Models\AuditLog::create([
                'auditable_type' => 'SourceAViolation',
                'auditable_id' => 0,
                'action' => 'source_isolation_blocked',
                'user_id' => $user->id,
                'user_role' => 'dept_manager',
                'old_values' => null,
                'new_values' => [
                    'blocked_source' => 'Source A (Project Reports)',
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'attempted_at' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Access denied. Department Managers cannot access Project Reports (Source A).',
                'error' => 'source_isolation_violation',
            ], 403);
        }

        return $next($request);
    }
}

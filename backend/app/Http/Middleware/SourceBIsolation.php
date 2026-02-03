<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Source B Isolation Middleware
 *
 * CONSTITUTION PRINCIPLE I - Zero Trust Architecture:
 * SDDs MUST NOT have access to Source B (Department Reports) under any circumstance.
 * This middleware blocks sdd role from accessing Source B routes.
 */
class SourceBIsolation
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

        // CRITICAL: SDDs CANNOT access Source B (Department Reports)
        if ($user->hasRole('sdd')) {
            // Log the unauthorized access attempt
            \App\Models\AuditLog::create([
                'auditable_type' => 'SourceBViolation',
                'auditable_id' => 0,
                'action' => 'source_isolation_blocked',
                'user_id' => $user->id,
                'user_role' => 'sdd',
                'old_values' => null,
                'new_values' => [
                    'blocked_source' => 'Source B (Department Reports)',
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'attempted_at' => now()->toIso8601String(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Access denied. SDDs cannot access Department Reports (Source B).',
                'error' => 'source_isolation_violation',
            ], 403);
        }

        return $next($request);
    }
}

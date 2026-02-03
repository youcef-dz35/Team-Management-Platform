<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if the authenticated user has the required role(s).
 *
 * Usage in routes:
 *   ->middleware('role:ceo,cfo')       // User must have ceo OR cfo role
 *   ->middleware('role:sdd')           // User must have sdd role
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more role names (user must have at least one)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the specified roles
        if (!$user->hasAnyRole($roles)) {
            // Log the access attempt
            \App\Models\AuditLog::create([
                'auditable_type' => 'AccessDenied',
                'auditable_id' => 0,
                'action' => 'access_denied',
                'user_id' => $user->id,
                'user_role' => $user->roles->first()?->name ?? 'unknown',
                'old_values' => null,
                'new_values' => [
                    'required_roles' => $roles,
                    'user_roles' => $user->roles->pluck('name')->toArray(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Access denied. Insufficient permissions.',
            ], 403);
        }

        return $next($request);
    }
}

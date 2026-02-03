<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAccessAttempt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $startTime = microtime(true);

        // Log the access attempt before processing
        $logData = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->roles->pluck('name')->first() ?? 'guest',
            'method' => $request->method(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        // Process the request
        $response = $next($request);

        // Calculate response time
        $duration = (microtime(true) - $startTime) * 1000;
        $logData['duration_ms'] = round($duration, 2);
        $logData['status_code'] = $response->getStatusCode();

        // Determine if this was an unauthorized attempt (403)
        $isUnauthorized = $response->getStatusCode() === 403;

        if ($isUnauthorized) {
            // Log unauthorized access attempts with higher severity
            Log::warning('Unauthorized access attempt', $logData);

            // Create audit log entry for unauthorized attempts
            if ($user) {
                AuditLog::create([
                    'auditable_type' => 'access_attempt',
                    'auditable_id' => 0,
                    'action' => 'unauthorized_access',
                    'user_id' => $user->id,
                    'user_role' => $logData['user_role'],
                    'old_values' => null,
                    'new_values' => [
                        'path' => $request->path(),
                        'method' => $request->method(),
                        'status' => 'denied',
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        } else {
            // Log successful access attempts at debug level
            Log::debug('Access granted', $logData);
        }

        return $response;
    }
}

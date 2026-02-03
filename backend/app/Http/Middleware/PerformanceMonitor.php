<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query log for this request
        DB::enableQueryLog();

        // Process the request
        $response = $next($request);

        // Calculate metrics
        $duration = (microtime(true) - $startTime) * 1000; // Convert to ms
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024; // Convert to MB
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $queryTime = collect($queries)->sum('time');

        // Log performance metrics
        Log::info('[PERF] ' . $request->method() . ' ' . $request->path(), [
            'duration_ms' => round($duration, 2),
            'memory_mb' => round($memoryUsed, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($queryTime, 2),
            'non_query_time_ms' => round($duration - $queryTime, 2),
        ]);

        // Add performance headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Query-Time', round($queryTime, 2) . 'ms');
            $response->headers->set('X-Total-Time', round($duration, 2) . 'ms');
        }

        return $response;
    }
}

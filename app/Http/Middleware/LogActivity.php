<?php

namespace App\Http\Middleware;
 
use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
 
class LogActivity
{
    private array $skipRoutes = [
        'admin.logs.*',       // don't log the log viewer itself
        'admin.*.latest',     // AJAX polling
        'admin.*.export',     // CSV exports
        'debugbar.*',
    ];
 
    private array $eventMap = [
        'POST'   => 'created',
        'PUT'    => 'updated',
        'PATCH'  => 'updated',
        'DELETE' => 'deleted',
    ];
 
    private array $moduleMap = [
        'users'    => 'users',
        'sessions' => 'sessions',
        'plans'    => 'plans',
        'jobs'     => 'jobs',
        'curriculum' => 'curriculum',
        'settings' => 'settings',
        'logs'     => 'system',
    ];
 
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
 
        // Only log mutating requests (non-GET) that succeed
        if (
            $request->isMethod('GET') ||
            !auth()->check() ||
            !$response->isSuccessful() && !$response->isRedirection()
        ) {
            return $response;
        }
 
        // Skip certain routes
        $routeName = $request->route()?->getName() ?? '';
        foreach ($this->skipRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) return $response;
        }
 
        try {
            $event  = $this->eventMap[$request->method()] ?? strtolower($request->method());
            $module = $this->detectModule($routeName);
            $level  = $request->isMethod('DELETE') ? 'danger' : ($request->isMethod('POST') ? 'success' : 'info');
 
            activity()
                ->event($event)
                ->description(auth()->user()->name . " performed {$event} via {$routeName}")
                ->module($module)
                ->level($level)
                ->log();
        } catch (\Throwable $e) {
            // Never let logging break the app
            logger()->error('ActivityLogger failed: ' . $e->getMessage());
        }
 
        return $response;
    }
 
    private function detectModule(string $routeName): string
    {
        foreach ($this->moduleMap as $segment => $module) {
            if (str_contains($routeName, $segment)) return $module;
        }
        return 'system';
    }
}

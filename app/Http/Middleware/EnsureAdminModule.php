<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModule
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $route = optional($request->route())->getName();

        if ($user && $user->isAdmin()) {
            $user->loadMissing('adminRole');
        }

        if ($user && $user->isAdmin() && ! $user->canAccessAdminRoute($route)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'You do not have access to this module.'], 403);
            }

            abort(403, 'You do not have access to this module.');
        }

        return $next($request);
    }
}

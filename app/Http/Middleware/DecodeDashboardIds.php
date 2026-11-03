<?php

namespace App\Http\Middleware;

use App\Support\UrlId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecodeDashboardIds
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        if (! $route || ! UrlId::shouldHide($route->getName())) {
            return $next($request);
        }

        foreach ($route->parameters() as $key => $value) {
            if (! is_string($value) && ! is_int($value)) {
                continue;
            }

            $decoded = UrlId::decrypt($value);
            if ($decoded === null) {
                continue;
            }

            $route->setParameter($key, $decoded);
        }

        foreach ($request->query() as $key => $value) {
            if (! is_string($key) || ! UrlId::isQueryIdKey($key)) {
                continue;
            }
            if (! is_string($value) && ! is_int($value)) {
                continue;
            }

            $decoded = UrlId::decrypt($value);
            if ($decoded === null) {
                continue;
            }

            $request->query->set($key, $decoded);
        }

        return $next($request);
    }
}

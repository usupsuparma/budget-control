<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class PermissionProtect
{
    public function handle($request, Closure $next)
    {
        $route = Route::currentRouteName();

        if ($route && !auth()->user()->can($route)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserPermission
{
    public function handle(Request $request, Closure $next, $perm)
    {
        if (!auth()->user()->hasPermission($perm)) {
            abort(404);
        }

        return $next($request);
    }
}

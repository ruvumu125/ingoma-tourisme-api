<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, string ...$roles) // Accept multiple roles
    {
        // Check if the authenticated user has at least one of the required roles
        if (!auth()->check() || !collect($roles)->contains(auth()->user()->role)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }

}

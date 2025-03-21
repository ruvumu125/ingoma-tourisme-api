<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, string $role)
    {
        // Check if the authenticated user has the required role
        if (!auth()->check() || !auth()->user()->hasRole($role)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }

}

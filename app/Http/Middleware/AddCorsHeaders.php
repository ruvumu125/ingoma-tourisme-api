<?php

namespace App\Http\Middleware;


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('storage/hotel_images/*')) {
            $response->header('Access-Control-Allow-Origin', 'http://localhost:3000');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $response;
    }
}

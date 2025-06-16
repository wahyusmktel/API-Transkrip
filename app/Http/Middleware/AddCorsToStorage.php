<?php

namespace App\Http\Middleware;

use Closure;

class AddCorsToStorage
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        if (str_starts_with($request->getPathInfo(), '/storage/')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        }

        return $response;
    }
}

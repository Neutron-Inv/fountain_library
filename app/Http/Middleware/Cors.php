<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        
        $allowedOrigins = [
        'https://fountainlibrary.vercel.app',
        'http://localhost:3000',
        'https://fountainlibrary.com'
    ];

    $origin = $request->headers->get('Origin');
    
        return $next($request)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
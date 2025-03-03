<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'https://fountainlibrary.vercel.app',
            'https://www.fountainlibrary.com',
            'https://fountainlibrary.com',
            'http://fountainlibrary.com',
            'http://localhost:3000',
        ];

        $origin = $request->headers->get('Origin');
        \Log::info('Origin Header:', [$request->headers->get('Origin')]);
        // Check if the origin is allowed
        // if (in_array($origin, $allowedOrigins)) {
            // Handle preflight (OPTIONS) requests
            if ($request->getMethod() === "OPTIONS") {
                return response('', 204)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            // Handle regular requests
            $response = $next($request);

            return $response
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept')
                ->header('Access-Control-Allow-Credentials', 'true');
        // }

        // If the origin is not allowed, return a 403 response
        // return response('Origin not allowed', 403);
    }
}


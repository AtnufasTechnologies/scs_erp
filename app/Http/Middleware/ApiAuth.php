<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = env('ERP_APIKEY');
        $apikey = $request->header('authorization');
        
        if ($apikey != $key) {
            return response()->json(['message' => ' Api Key Error'], 401);
        }
        return $next($request);
    }
}

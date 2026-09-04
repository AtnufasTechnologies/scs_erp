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

        // ✅ Skip ERP key check for Biometric Webhook
        if ($request->is('api/biometric/attendance')) {
            return $next($request);
        }

        // ✅ Skip ERP key check for Easebuzz webhook
        if ($request->is('api/easebuzz/webhook')) {
            return $next($request);
        }

        // ✅ Skip generic API key check for ERP NAAC webhook (uses dedicated token middleware)
        if ($request->is('api/webhooks/erp/naac') || $request->is('api/webhooks/erp/naac/*')) {
            return $next($request);
        }

        $key = env('ERP_APIKEY');
        $apikey = $request->header('authorization');

        if ($apikey != $key) {
            return response()->json(['message' => ' Api Key Error'], 401);
        }
        return $next($request);
    }
}

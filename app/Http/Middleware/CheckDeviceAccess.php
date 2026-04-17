<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceAccess
{
  public function handle(Request $request, Closure $next): Response
  {
    $clientIp = $request->ip();
    $macAddress = $request->header('X-Device-Mac');

    // Check by MAC address first, then by IP
    $identifier = $macAddress ?: $clientIp;
    $allowed = DB::table('exam_mac_whitelists')
      ->where('mac_address', $identifier)
      ->exists();

    Log::info('Device access attempt', [
      'user_id' => Auth::id(),
      'ip' => $clientIp,
      'mac' => $macAddress,
      'identifier' => $identifier,
      'allowed' => $allowed,
    ]);

    if (!$allowed) {
      if ($request->expectsJson()) {
        return response()->json([
          'message' => 'Device not authorized for marks entry. Contact COE to whitelist your device.'
        ], 403);
      }

      return redirect()->back()->with('error', 'Unauthorized device. Your IP (' . e($clientIp) . ') is not whitelisted for marks entry. Contact COE.');
    }

    // Store in request for logging during save
    $request->merge(['_device_ip' => $clientIp, '_device_mac' => $macAddress ?? $clientIp]);

    return $next($request);
  }
}

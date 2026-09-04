<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyErpNaacWebhookToken
{
  public function handle(Request $request, Closure $next): Response
  {
    $expectedToken = trim((string) env('ERP_NAAC_WEBHOOK_TOKEN', ''));
    if ($expectedToken === '') {
      return response()->json([
        'message' => 'ERP NAAC webhook token is not configured.',
      ], 500);
    }

    $providedToken = $this->extractToken($request);

    if (!hash_equals($expectedToken, $providedToken)) {
      return response()->json([
        'message' => 'Unauthorized webhook token.',
      ], 401);
    }

    return $next($request);
  }

  private function extractToken(Request $request): string
  {
    $headerToken = trim((string) $request->header('X-ERP-WEBHOOK-TOKEN', ''));
    if ($headerToken !== '') {
      return $headerToken;
    }

    $headerToken = trim((string) $request->header('X-WEBHOOK-TOKEN', ''));
    if ($headerToken !== '') {
      return $headerToken;
    }

    $authHeader = trim((string) $request->header('Authorization', ''));
    if (stripos($authHeader, 'Bearer ') === 0) {
      return trim(substr($authHeader, 7));
    }

    return '';
  }
}

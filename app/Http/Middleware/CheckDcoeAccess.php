<?php

namespace App\Http\Middleware;

use App\Models\DcoeMenuPermission;
use App\Models\UserHasRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDcoeAccess
{
  /**
   * Restrict DCOE users to their assigned campus.
   * Injects campus_id into the request so controllers can use it for scoping.
   * COE users pass through without restriction.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();
    if (!$user) {
      return redirect('/');
    }

    $roleType = UserHasRole::where('user_id', $user->id)->value('role_name');

    // COE has unrestricted access
    if ($roleType === 'coe') {
      return $next($request);
    }

    // DCOE must have campus assigned
    if ($roleType === 'dcoe') {
      $campusSetting = $user->campuspermission;
      if (!$campusSetting || !$campusSetting->campus_id) {
        return redirect()->route('coe.dashboard')
          ->with('error', 'No campus assigned. Please contact the COE.');
      }

      // Inject campus scope into request
      $request->merge(['_dcoe_campus_id' => $campusSetting->campus_id]);

      return $next($request);
    }

    // Other roles should not access COE routes
    abort(403, 'Unauthorized access.');
  }
}

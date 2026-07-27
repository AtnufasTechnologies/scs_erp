<?php

namespace App\Http\Middleware;

use App\Models\RoleMaster;
use App\Models\UserHasRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDeanAccess
{
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if (!$user) {
      return redirect('/')->with('error', 'Please login to continue.');
    }

    $roleName = (string) (UserHasRole::where('user_id', $user->id)->value('role_name') ?? '');

    if ($roleName === '') {
      $roleId = (int) (UserHasRole::where('user_id', $user->id)->value('role_id') ?? 0);
      if ($roleId > 0) {
        $roleName = (string) (RoleMaster::where('id', $roleId)->value('slug') ?? '');
      }
    }

    $normalizedRole = strtolower(trim($roleName));
    $allowedRoles = [
      'dean',
      'dean-of-student-affairs',
      'dean-student-affairs',
      'student-affairs-dean',
      'admin',
      'itcell',
    ];

    if (!in_array($normalizedRole, $allowedRoles, true)) {
      return redirect('/erp/faculty/dashboard')->with('error', 'Unauthorized Access');
    }

    return $next($request);
  }
}

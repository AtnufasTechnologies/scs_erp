<?php

namespace App\Http\Middleware;

use App\Models\UserHasRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockStudentAccess
{
  /**
   * Prevent student-role users from accessing non-student ERP routes.
   * Students must use their own /erp/student/* routes.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if ($user) {
      $role = UserHasRole::where('user_id', $user->id)->value('role_name');

      if ($role === 'student') {
        Auth::logout();
        return redirect()->route('student.login')
          ->with('error', 'Access denied. Please use the Student Portal.');
      }
    }

    return $next($request);
  }
}

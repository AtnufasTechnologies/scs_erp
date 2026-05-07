<?php

namespace App\Http\Middleware;

use App\Models\StudentMaster;
use App\Models\UserHasRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentAccess
{
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if (!$user) {
      return redirect()->route('student.login')->with('error', 'Please login to continue.');
    }

    $roleType = UserHasRole::where('user_id', $user->id)->value('role_name');


    if ($roleType !== 'student') {
      Auth::logout();
      return redirect()->route('student.login')->with('error', 'Unauthorized Access');
    }

    return $next($request);
  }
}

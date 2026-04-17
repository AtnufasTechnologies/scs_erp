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
      return redirect('/')->with('error', 'Please login to continue.');
    }

    $roleType = UserHasRole::where('user_id', $user->id)->value('role_name');

    if ($roleType !== 'student') {
      Auth::logout();
      return redirect('/')->with('error', 'Unauthorized Access');
    }

    if (!$user->student_id) {
      Auth::logout();
      return redirect('/')->with('error', 'Student profile not linked. Please contact admin.');
    }

    $student = StudentMaster::find($user->student_id);

    if (!$student) {
      Auth::logout();
      return redirect('/')->with('error', 'Student record not found. Please contact admin.');
    }

    // Share student data with all views in this request
    view()->share('authStudent', $student);

    return $next($request);
  }
}

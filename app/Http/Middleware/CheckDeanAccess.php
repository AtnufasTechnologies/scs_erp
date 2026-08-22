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

    $roleNames = UserHasRole::where('user_id', $user->id)
      ->pluck('role_name')
      ->map(fn($role) => strtolower(trim((string) $role)))
      ->filter(fn($role) => $role !== '')
      ->values();

    if ($roleNames->isEmpty()) {
      $roleIds = UserHasRole::where('user_id', $user->id)
        ->whereNotNull('role_id')
        ->pluck('role_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      if ($roleIds->isNotEmpty()) {
        $roleNames = RoleMaster::whereIn('id', $roleIds->all())
          ->pluck('slug')
          ->map(fn($slug) => strtolower(trim((string) $slug)))
          ->filter(fn($slug) => $slug !== '')
          ->values();
      }
    }

    $allowedRoles = [
      'dean',
      'dean-of-student-affairs',
      'dean-student-affairs',
      'student-affairs-dean',
      'admin',
      'itcell',
    ];

    $hasAccess = $roleNames->contains(fn($role) => in_array($role, $allowedRoles, true));

    if (!$hasAccess) {
      return redirect('/erp/faculty/dashboard')->with('error', 'Unauthorized Access');
    }

    return $next($request);
  }
}

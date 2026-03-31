<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacultyRemuneration;
use Illuminate\Support\Facades\Gate;

class FacultyRemunerationApprovalController extends Controller
{
  // Helper to check if user is admin
  protected function isAdmin($user)
  {
    // Check user->userroletype->role_name === 'admin' (or similar logic)
    return $user && $user->userroletype && strtolower($user->userroletype->role_name) === 'admin';
  }

  // Approve a remuneration (admin only)
  public function approve($id)
  {
    if (!auth()->user() || !$this->isAdmin(auth()->user())) {
      abort(403, 'Only admin can approve remuneration.');
    }
    $rem = FacultyRemuneration::findOrFail($id);
    if ($rem->status !== 'pending') {
      return back()->with('error', 'Only pending remunerations can be approved.');
    }
    $rem->status = 'approved';
    $rem->save();
    return back()->with('success', 'Remuneration approved.');
  }

  // Mark a remuneration as paid (admin only)
  public function markPaid($id)
  {
    if (!auth()->user() || !$this->isAdmin(auth()->user())) {
      abort(403, 'Only admin can mark as paid.');
    }
    $rem = FacultyRemuneration::findOrFail($id);
    if ($rem->status !== 'approved') {
      return back()->with('error', 'Only approved remunerations can be marked as paid.');
    }
    $rem->status = 'paid';
    $rem->save();
    return back()->with('success', 'Remuneration marked as paid.');
  }
}

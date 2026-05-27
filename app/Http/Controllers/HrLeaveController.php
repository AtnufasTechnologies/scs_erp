<?php

namespace App\Http\Controllers;

use App\Models\FacultyLeaveApplication;
use App\Models\LeaveMaster;
use App\Models\Faculty;
use App\Models\AnnualSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HrLeaveController extends Controller
{
  /**
   * Display a listing of all leave applications
   */
  public function index(Request $request)
  {
    $search = $request->get('search');
    $status = $request->get('status');
    $leaveType = $request->get('leave_type');
    $sessionId = $request->get('session_id');

    $query = FacultyLeaveApplication::with(['faculty', 'leaveMaster', 'annualSession', 'approver']);

    if ($search) {
      $query->whereHas('faculty', function ($q) use ($search) {
        $q->where('FIRST_NAME', 'like', "%$search%")
          ->orWhere('LAST_NAME', 'like', "%$search%")
          ->orWhere('USER_CODE', 'like', "%$search%");
      });
    }

    if ($status) {
      $query->where('status', $status);
    }

    if ($leaveType) {
      $query->where('leave_type_id', $leaveType);
    }

    if ($sessionId) {
      $query->where('annual_session_id', $sessionId);
    } else {
      // Default to current session
      $query->where('annual_session_id', StaticController::activeSessionId());
    }

    $leaveApplications = $query->orderBy('created_at', 'desc')->paginate(20);

    $leaveTypes = LeaveMaster::active()->ordered()->get();
    $sessions = AnnualSession::orderBy('title', 'desc')->get();

    return view('hr.leave.index', compact('leaveApplications', 'leaveTypes', 'sessions', 'search', 'status', 'leaveType', 'sessionId'));
  }

  /**
   * Display the specified leave application
   */
  public function show($id)
  {
    $application = FacultyLeaveApplication::with([
      'faculty',
      'leaveMaster',
      'annualSession',
      'approver'
    ])->findOrFail($id);

    return view('hr.leave.show', compact('application'));
  }

  /**
   * Show form to approve/reject leave application
   */
  public function reviewForm($id)
  {
    $application = FacultyLeaveApplication::with([
      'faculty.department',
      'leaveMaster',
      'annualSession',
      'approver',
      'forwarder',
      'deptActionUser'
    ])->findOrFail($id);

    if ($application->status !== 'pending') {
      return redirect()->route('hr.leave.show', $id)
        ->with('error', 'This application has already been processed.');
    }

    // Get all active leave types for changing leave type
    $leaveTypes = \App\Models\LeaveMaster::active()->ordered()->get();

    return view('hr.leave.review', compact('application', 'leaveTypes'));
  }

  /**
   * Approve a leave application
   */
  public function approve(Request $request, $id)
  {
    $request->validate([
      'admin_remarks' => 'nullable|string|max:1000',
    ]);

    $application = FacultyLeaveApplication::findOrFail($id);

    if ($application->status !== 'pending') {
      return redirect()->back()
        ->with('error', 'This application has already been processed.');
    }

    $application->update([
      'status' => 'approved',
      'approved_by' => Auth::id(),
      'approved_at' => now(),
      'admin_remarks' => $request->admin_remarks,
    ]);

    return redirect()->route('hr.leave.index')
      ->with('success', 'Leave application approved successfully!');
  }

  /**
   * Reject a leave application
   */
  public function reject(Request $request, $id)
  {
    $request->validate([
      'rejection_reason' => 'required|string|max:1000',
      'admin_remarks' => 'nullable|string|max:1000',
    ]);

    $application = FacultyLeaveApplication::findOrFail($id);

    if ($application->status !== 'pending') {
      return redirect()->back()
        ->with('error', 'This application has already been processed.');
    }

    $application->update([
      'status' => 'rejected',
      'rejection_reason' => $request->rejection_reason,
      'approved_by' => Auth::id(),
      'approved_at' => now(),
      'admin_remarks' => $request->admin_remarks,
    ]);

    return redirect()->route('hr.leave.index')
      ->with('success', 'Leave application rejected!');
  }

  /**
   * Forward a leave application to principal
   */
  public function forward(Request $request, $id)
  {
    $request->validate([
      'forwarded_to' => 'required|in:Principal,DeanOfStudentStudies,DCOE',
      'forwarded_remarks' => 'nullable|string|max:1000',
    ]);

    $application = FacultyLeaveApplication::findOrFail($id);

    if ($application->status !== 'pending') {
      return redirect()->back()
        ->with('error', 'This application has already been processed.');
    }

    $application->update([
      'forwarded_to' => $request->forwarded_to,
      'forwarded_by' => Auth::id(),
      'forwarded_at' => now(),
      'forwarded_remarks' => $request->forwarded_remarks,
      'dept_action' => 'forwarded',
      'dept_action_by' => Auth::id(),
      'dept_action_at' => now(),
    ]);

    return redirect()->route('hr.leave.index')
      ->with('success', 'Leave application forwarded to ' . $request->forwarded_to . '!');
  }

  /**
   * Change leave type of an application
   */
  public function changeLeaveType(Request $request, $id)
  {
    $request->validate([
      'leave_type_id' => 'required|exists:leave_masters,id',
      'change_reason' => 'nullable|string|max:500',
    ]);

    $application = FacultyLeaveApplication::findOrFail($id);
    $oldLeaveType = $application->leaveMaster->leave_type_name;
    $newLeaveType = LeaveMaster::findOrFail($request->leave_type_id);

    $application->update([
      'leave_type_id' => $request->leave_type_id,
      'leave_type' => $newLeaveType->leave_type_code,
      'admin_remarks' => ($application->admin_remarks ? $application->admin_remarks . "\n\n" : '') .
        "Leave type changed from '{$oldLeaveType}' to '{$newLeaveType->leave_type_name}' by " .
        Auth::user()->name . " on " . now()->format('d M Y') .
        ($request->change_reason ? ". Reason: {$request->change_reason}" : ''),
    ]);

    return redirect()->route('hr.leave.show', $id)
      ->with('success', 'Leave type changed successfully!');
  }

  /**
   * Get leave statistics
   */
  public function statistics(Request $request)
  {
    $sessionId = $request->get('session_id', StaticController::activeSessionId());
    $session = AnnualSession::find($sessionId);

    $stats = [
      'total_applications' => FacultyLeaveApplication::forSession($sessionId)->count(),
      'pending' => FacultyLeaveApplication::forSession($sessionId)->pending()->count(),
      'approved' => FacultyLeaveApplication::forSession($sessionId)->approved()->count(),
      'rejected' => FacultyLeaveApplication::forSession($sessionId)->rejected()->count(),
      'total_leave_days' => FacultyLeaveApplication::forSession($sessionId)->approved()->sum('total_days'),
    ];

    // Leave breakdown by type
    $leaveBreakdown = LeaveMaster::active()->get()->map(function ($leaveType) use ($sessionId) {
      return [
        'name' => $leaveType->leave_type_name,
        'code' => $leaveType->leave_type_code,
        'count' => FacultyLeaveApplication::forSession($sessionId)
          ->where('leave_type_id', $leaveType->id)
          ->count(),
        'days' => FacultyLeaveApplication::forSession($sessionId)
          ->approved()
          ->where('leave_type_id', $leaveType->id)
          ->sum('total_days'),
      ];
    });

    // Top faculty by leave days
    $topFacultyByLeave = Faculty::with('leaveApplications')
      ->get()
      ->map(function ($faculty) use ($sessionId) {
        $leaveDays = FacultyLeaveApplication::where('faculty_id', $faculty->id)
          ->forSession($sessionId)
          ->approved()
          ->sum('total_days');

        return [
          'faculty' => $faculty,
          'leave_days' => $leaveDays,
        ];
      })
      ->sortByDesc('leave_days')
      ->take(10);

    $sessions = AnnualSession::orderBy('title', 'desc')->get();

    return view('hr.leave.statistics', compact('stats', 'leaveBreakdown', 'topFacultyByLeave', 'sessions', 'session'));
  }

  /**
   * Bulk approve leave applications
   */
  public function bulkApprove(Request $request)
  {
    $request->validate([
      'application_ids' => 'required|array',
      'application_ids.*' => 'exists:faculty_leave_applications,id',
    ]);

    $count = FacultyLeaveApplication::whereIn('id', $request->application_ids)
      ->where('status', 'pending')
      ->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
      ]);

    return redirect()->back()
      ->with('success', "{$count} leave applications approved successfully!");
  }
}

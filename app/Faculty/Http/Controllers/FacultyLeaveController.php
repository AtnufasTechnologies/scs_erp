<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLeaveApplication;
use App\Models\LeaveMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FacultyLeaveController extends Controller
{
  /**
   * Display a listing of leave applications
   */
  public function index(Request $request)
  {
    $facultyId = $this->getFacultyId();

    $filter = $request->get('filter', 'all');

    // Only show current session leave applications
    $query = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->currentSession()
      ->orderBy('created_at', 'desc');

    if ($filter !== 'all') {
      $query->where('status', $filter);
    }

    $leaveApplications = $query->paginate(10);

    // Get current session
    $currentSessionId = StaticController::activeSessionId();

    // Calculate leave statistics for current session
    $stats = [
      'total' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->count(),
      'pending' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->pending()
        ->count(),
      'approved' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->approved()
        ->count(),
      'rejected' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->rejected()
        ->count(),
    ];

    // Calculate days taken by leave type for current session
    $leaveTypes = LeaveMaster::active()->ordered()->get();
    $leaveDaysByType = [];

    foreach ($leaveTypes as $leaveType) {
      $leaveDaysByType[$leaveType->id] = [
        'name' => $leaveType->leave_type_name,
        'code' => $leaveType->leave_type_code,
        'allowed' => $leaveType->allowed_days_per_year,
        'taken' => FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->currentSession()
          ->approved()
          ->where('leave_type_id', $leaveType->id)
          ->sum('total_days'),
        'badge_color' => $leaveType->badge_color,
      ];
    }

    return view('faculty.leave.index', compact(
      'leaveApplications',
      'stats',
      'filter',
      'leaveDaysByType'
    ));
  }

  /**
   * Show the form for creating a new leave application
   */
  public function create()
  {
    $leaveTypes = LeaveMaster::active()->ordered()->get();
    return view('faculty.leave.create', compact('leaveTypes'));
  }

  /**
   * Store a newly created leave application
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'leave_type_id' => 'required|exists:leave_masters,id',
      'start_date' => 'required|date|after_or_equal:today',
      'end_date' => 'required|date|after_or_equal:start_date',
      'reason' => 'required|string|max:1000',
      'contact_during_leave' => 'nullable|string|max:255',
      'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();

    // Get leave type details
    $leaveMaster = LeaveMaster::findOrFail($request->leave_type_id);

    // Calculate total days
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $totalDays = $startDate->diffInDays($endDate) + 1;

    $data = [
      'faculty_id' => $facultyId,
      'annual_session_id' => StaticController::activeSessionId(),
      'leave_type_id' => $request->leave_type_id,
      'leave_type' => $leaveMaster->leave_type_code, // For backward compatibility
      'start_date' => $request->start_date,
      'end_date' => $request->end_date,
      'total_days' => $totalDays,
      'reason' => $request->reason,
      'contact_during_leave' => $request->contact_during_leave,
      'status' => 'pending'
    ];

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $filename = StaticController::s3_file_uploader($file, 'leave_attachments');
      $data['attachment'] = $filename;
    }

    FacultyLeaveApplication::create($data);

    return redirect()->route('faculty.leave.index')
      ->with('success', 'Leave application submitted successfully!');
  }

  /**
   * Display the specified leave application
   */
  public function show($id)
  {
    $facultyId = $this->getFacultyId();

    $leaveApplication = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->with(['approver'])
      ->findOrFail($id);

    return view('faculty.leave.show', compact('leaveApplication'));
  }

  /**
   * Show the form for editing the specified leave application
   */
  public function edit($id)
  {
    $facultyId = $this->getFacultyId();

    $leaveApplication = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->where('status', 'pending')
      ->findOrFail($id);

    $leaveTypes = LeaveMaster::active()->ordered()->get();
    return view('faculty.leave.edit', compact('leaveApplication', 'leaveTypes'));
  }

  /**
   * Update the specified leave application
   */
  public function update(Request $request, $id)
  {
    $request->validate([
      'leave_type_id' => 'required|exists:leave_masters,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'reason' => 'required|string|max:1000',
      'contact_during_leave' => 'nullable|string|max:255',
      'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();

    $leaveApplication = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->where('status', 'pending')
      ->findOrFail($id);

    // Get leave type details
    $leaveMaster = LeaveMaster::findOrFail($request->leave_type_id);

    // Calculate total days
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $totalDays = $startDate->diffInDays($endDate) + 1;

    $data = [
      'leave_type_id' => $request->leave_type_id,
      'leave_type' => $leaveMaster->leave_type_code, // For backward compatibility
      'start_date' => $request->start_date,
      'end_date' => $request->end_date,
      'total_days' => $totalDays,
      'reason' => $request->reason,
      'contact_during_leave' => $request->contact_during_leave,
    ];

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $filename = StaticController::s3_file_uploader($file, 'leave_attachments');
      $data['attachment'] = $filename;
    }

    $leaveApplication->update($data);

    return redirect()->route('faculty.leave.index')
      ->with('success', 'Leave application updated successfully!');
  }

  /**
   * Cancel the specified leave application
   */
  public function cancel($id)
  {
    $facultyId = $this->getFacultyId();

    $leaveApplication = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->where('status', 'pending')
      ->findOrFail($id);

    $leaveApplication->update(['status' => 'cancelled']);

    return redirect()->route('faculty.leave.index')
      ->with('success', 'Leave application cancelled successfully!');
  }

  /**
   * Remove the specified leave application
   */
  public function destroy($id)
  {
    $facultyId = $this->getFacultyId();

    $leaveApplication = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->where('status', 'pending')
      ->findOrFail($id);

    $leaveApplication->delete();

    return redirect()->route('faculty.leave.index')
      ->with('success', 'Leave application deleted successfully!');
  }

  /**
   * Display leave history from past sessions
   */
  public function history(Request $request)
  {
    $facultyId = $this->getFacultyId();

    // Get all sessions
    $sessions = AnnualSession::orderBy('title', 'desc')->get();
    $currentSessionId = StaticController::activeSessionId();

    // Get selected session or default to previous session
    $selectedSessionId = $request->get('session_id');
    if (!$selectedSessionId) {
      $selectedSessionId = AnnualSession::where('id', '!=', $currentSessionId)
        ->orderBy('title', 'desc')
        ->value('id');
    }

    $query = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->with(['annualSession', 'leaveMaster'])
      ->orderBy('created_at', 'desc');

    if ($selectedSessionId) {
      $query->forSession($selectedSessionId);
    } else {
      // Show all archived sessions if no specific session selected
      $query->where('annual_session_id', '!=', $currentSessionId);
    }

    $leaveApplications = $query->paginate(15);

    // Calculate statistics for selected session
    $stats = [];
    if ($selectedSessionId) {
      $selectedSession = AnnualSession::find($selectedSessionId);

      $stats = [
        'session_title' => $selectedSession ? $selectedSession->title : 'All Sessions',
        'total' => FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->forSession($selectedSessionId)
          ->count(),
        'approved' => FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->forSession($selectedSessionId)
          ->approved()
          ->count(),
        'rejected' => FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->forSession($selectedSessionId)
          ->rejected()
          ->count(),
        'total_days_taken' => FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->forSession($selectedSessionId)
          ->approved()
          ->sum('total_days'),
      ];

      // Leave breakdown by type
      $stats['leave_breakdown'] = LeaveMaster::active()->ordered()->get()->map(function ($leaveType) use ($facultyId, $selectedSessionId) {
        $daysTaken = FacultyLeaveApplication::where('faculty_id', $facultyId)
          ->forSession($selectedSessionId)
          ->approved()
          ->where('leave_type_id', $leaveType->id)
          ->sum('total_days');

        return [
          'name' => $leaveType->leave_type_name,
          'code' => $leaveType->leave_type_code,
          'days_taken' => $daysTaken,
          'allowed' => $leaveType->allowed_days_per_year,
          'badge_color' => $leaveType->badge_color,
        ];
      })->filter(function ($item) {
        return $item['days_taken'] > 0; // Only show leave types that were used
      });
    }

    return view('faculty.leave.history', compact(
      'leaveApplications',
      'sessions',
      'selectedSessionId',
      'stats'
    ));
  }

  /**
   * Get faculty ID from authenticated user
   */
  private function getFacultyId()
  {
    $userId = Auth::user()->id;
    return SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
  }
}

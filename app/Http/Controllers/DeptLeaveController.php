<?php

namespace App\Http\Controllers;

use App\Http\Controllers\StaticController;
use App\Models\FacultyLeaveApplication;
use App\Models\LeaveMaster;
use App\Models\Subject;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeptLeaveController extends Controller
{
  private function getDeptSubject()
  {
    $userId = Auth::user()->id;
    $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
    return Subject::find($subjectId);
  }

  private function getDeptFacultyIds($subjectId)
  {
    return SubjectFacultyMaster::where('subject_id', $subjectId)
      ->pluck('faculty_id')
      ->toArray();
  }

  /**
   * Leave Category Master - list all leave types
   */
  public function categoryIndex()
  {
    $categories = LeaveMaster::ordered()->get();
    return view('admin.department.leave.category-index', compact('categories'));
  }

  /**
   * Store a new leave category
   */
  public function categoryStore(Request $request)
  {
    $request->validate([
      'leave_type_name' => 'required|string|max:255',
      'leave_type_code' => 'required|string|max:50|unique:leave_masters,leave_type_code',
      'allowed_days_per_year' => 'nullable|integer|min:0',
      'description' => 'nullable|string|max:500',
      'requires_attachment' => 'nullable|boolean',
      'badge_color' => 'nullable|string|max:50',
    ]);

    LeaveMaster::create([
      'leave_type_name' => $request->leave_type_name,
      'leave_type_code' => strtoupper($request->leave_type_code),
      'allowed_days_per_year' => $request->allowed_days_per_year,
      'description' => $request->description,
      'requires_attachment' => $request->has('requires_attachment'),
      'badge_color' => $request->badge_color ?? 'primary',
      'is_active' => true,
    ]);

    return redirect()->back()->with('success', 'Leave category added successfully');
  }

  /**
   * Update an existing leave category
   */
  public function categoryUpdate(Request $request, $id)
  {
    $category = LeaveMaster::findOrFail($id);

    $request->validate([
      'leave_type_name' => 'required|string|max:255',
      'leave_type_code' => 'required|string|max:50|unique:leave_masters,leave_type_code,' . $id,
      'allowed_days_per_year' => 'nullable|integer|min:0',
      'description' => 'nullable|string|max:500',
      'badge_color' => 'nullable|string|max:50',
    ]);

    $category->update([
      'leave_type_name' => $request->leave_type_name,
      'leave_type_code' => strtoupper($request->leave_type_code),
      'allowed_days_per_year' => $request->allowed_days_per_year,
      'description' => $request->description,
      'requires_attachment' => $request->has('requires_attachment'),
      'badge_color' => $request->badge_color ?? $category->badge_color,
    ]);

    return redirect()->back()->with('success', 'Leave category updated');
  }

  /**
   * Toggle leave category active status
   */
  public function categoryToggle($id)
  {
    $category = LeaveMaster::findOrFail($id);
    $category->update(['is_active' => !$category->is_active]);
    return redirect()->back()->with('success', 'Leave category status updated');
  }

  /**
   * Show all leave applications from dept faculty
   */
  public function index(Request $request)
  {
    $subject = $this->getDeptSubject();
    if (!$subject) {
      return redirect()->back()->with('info', 'Department not found');
    }

    $facultyIds = $this->getDeptFacultyIds($subject->id);

    $query = FacultyLeaveApplication::with(['faculty', 'leaveMaster', 'forwarder'])
      ->whereIn('faculty_id', $facultyIds)
      ->currentSession()
      ->orderBy('created_at', 'desc');

    if ($request->filled('status')) {
      if ($request->status === 'pending') {
        $query->where('status', 'pending')->whereNull('dept_action');
      } elseif ($request->status === 'forwarded') {
        $query->where('dept_action', 'forwarded');
      } elseif ($request->status === 'dept_rejected') {
        $query->where('dept_action', 'rejected');
      } else {
        $query->where('status', $request->status);
      }
    }

    if ($request->filled('leave_type')) {
      $query->where('leave_type_id', $request->leave_type);
    }

    $applications = $query->paginate(15);
    $leaveTypes = LeaveMaster::active()->ordered()->get();

    // Stats
    $allDeptApplications = FacultyLeaveApplication::whereIn('faculty_id', $facultyIds)->currentSession();
    $stats = [
      'total' => (clone $allDeptApplications)->count(),
      'pending' => (clone $allDeptApplications)->where('status', 'pending')->whereNull('dept_action')->count(),
      'forwarded' => (clone $allDeptApplications)->where('dept_action', 'forwarded')->count(),
      'dept_rejected' => (clone $allDeptApplications)->where('dept_action', 'rejected')->count(),
      'approved' => (clone $allDeptApplications)->where('status', 'approved')->count(),
    ];

    return view('admin.department.leave.index', compact(
      'applications',
      'leaveTypes',
      'stats',
      'subject'
    ));
  }

  /**
   * Show single leave application detail
   */
  public function show($id)
  {
    $subject = $this->getDeptSubject();
    if (!$subject) {
      return redirect()->back()->with('info', 'Department not found');
    }

    $facultyIds = $this->getDeptFacultyIds($subject->id);

    $application = FacultyLeaveApplication::with(['faculty', 'leaveMaster', 'approver', 'forwarder'])
      ->whereIn('faculty_id', $facultyIds)
      ->findOrFail($id);

    return view('admin.department.leave.show', compact('application', 'subject'));
  }

  /**
   * Reject a leave application with remarks
   */
  public function reject(Request $request, $id)
  {
    $request->validate([
      'rejection_reason' => 'required|string|max:1000',
    ]);

    $subject = $this->getDeptSubject();
    if (!$subject) {
      return redirect()->back()->with('info', 'Department not found');
    }

    $facultyIds = $this->getDeptFacultyIds($subject->id);

    $application = FacultyLeaveApplication::whereIn('faculty_id', $facultyIds)
      ->where('status', 'pending')
      ->whereNull('dept_action')
      ->findOrFail($id);

    $application->update([
      'status' => 'rejected',
      'dept_action' => 'rejected',
      'dept_action_by' => Auth::id(),
      'dept_action_at' => now(),
      'rejection_reason' => $request->rejection_reason,
      'admin_remarks' => $request->admin_remarks,
      'approved_by' => Auth::id(),
      'approved_at' => now(),
    ]);

    return redirect()->route('department.leave.index')
      ->with('success', 'Leave application rejected');
  }

  /**
   * Forward a leave application to higher authority
   */
  public function forward(Request $request, $id)
  {
    $request->validate([
      'forwarded_to' => 'required|in:DeanOfStudentStudies,DCOE,HR',
      'forwarded_remarks' => 'nullable|string|max:1000',
    ]);

    $subject = $this->getDeptSubject();
    if (!$subject) {
      return redirect()->back()->with('info', 'Department not found');
    }

    $facultyIds = $this->getDeptFacultyIds($subject->id);

    $application = FacultyLeaveApplication::whereIn('faculty_id', $facultyIds)
      ->where('status', 'pending')
      ->whereNull('dept_action')
      ->findOrFail($id);

    $application->update([
      'dept_action' => 'forwarded',
      'dept_action_by' => Auth::id(),
      'dept_action_at' => now(),
      'forwarded_to' => $request->forwarded_to,
      'forwarded_by' => Auth::id(),
      'forwarded_at' => now(),
      'forwarded_remarks' => $request->forwarded_remarks,
    ]);

    return redirect()->route('department.leave.index')
      ->with('success', 'Leave application forwarded to ' . $request->forwarded_to);
  }
}

<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\HrFacultyStatusHistory;
use App\Models\NationalityMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SubjectFacultyMaster;

class HrFacultyController extends Controller
{
  /**
   * Display a listing of faculty members
   */
  public function index(Request $request)
  {
    $search = $request->get('search');
    $status = $request->get('status');

    $query = Faculty::with(['nationality']);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('USER_CODE', 'like', "%$search%")
          ->orWhere('FIRST_NAME', 'like', "%$search%")
          ->orWhere('LAST_NAME', 'like', "%$search%")
          ->orWhere('MAIL_ID', 'like', "%$search%")
          ->orWhere('MOBILE_NO', 'like', "%$search%");
      });
    }

    if ($status === 'active') {
      $query->where('IS_LEFT', 0);
    } elseif ($status === 'left') {
      $query->where('IS_LEFT', 1);
    }

    $faculties = $query->latest()->paginate(20);

    return view('hr.faculty.index', compact('faculties', 'search', 'status'));
  }

  /**
   * Show the form for creating a new faculty member
   */
  public function create()
  {
    $nationalities = NationalityMaster::orderBy('name')->get();
    return view('hr.faculty.create', compact('nationalities'));
  }

  /**
   * Store a newly created faculty member
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'USER_CODE' => 'required|string|max:50|unique:faculties,USER_CODE',
      'FIRST_NAME' => 'required|string|max:100',
      'MIDDLE_NAME' => 'nullable|string|max:100',
      'LAST_NAME' => 'nullable|string|max:100',
      'GENDER' => 'required|in:1,2,3',
      'MAIL_ID' => 'required|email|max:100|unique:faculties,MAIL_ID',
      'MOBILE_NO' => 'required|string|max:15',
      'ADDRESS' => 'nullable|string',
      'DOB' => 'nullable|date',
      'DOJ' => 'nullable|date',
      'reactivation_date' => 'nullable|date|after_or_equal:DOJ',
      'hr_remark' => 'nullable|string|max:1000',
      'NATIONALITY' => 'nullable|exists:nationality_masters,id',
      'employee_type' => 'nullable|string|max:50',
      'designation' => 'nullable|string|max:100',
      'qualification' => 'nullable|string|max:255',
      'specialization' => 'nullable|string|max:255',
      'experience_years' => 'nullable|integer|min:0',
      'pan_number' => 'nullable|string|max:20',
      'aadhar_number' => 'nullable|string|max:20',
      'bank_account_number' => 'nullable|string|max:50',
      'bank_ifsc_code' => 'nullable|string|max:20',
      'bank_name' => 'nullable|string|max:100',
      'emergency_contact_name' => 'nullable|string|max:100',
      'emergency_contact_number' => 'nullable|string|max:15',
      'permanent_address' => 'nullable|string',
      'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'user_type' => 'required|in:1,2',
      'CAMPUS_ID' => 'required|exists:campuses,id',
      'responsibility' => 'nullable|string|max:255',
      'paper_publications_count' => 'nullable|integer|min:0',
      'orcid_id' => 'nullable|string|max:50',
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $validated['photo'] = StaticController::s3_file_uploader($file, 'faculty_photos');
    }

    $faculty = Faculty::create($validated);

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Faculty member created successfully!');
  }

  /**
   * Display the specified faculty member
   */
  public function show($id)
  {
    $faculty = Faculty::with([
      'nationality',
      'statusHistories' => function ($q) {
        $q->latest('status_on')->latest('id');
      },
      'leaveApplications' => function ($q) {
        $q->orderBy('created_at', 'desc')->limit(10);
      },
      'fdpParticipations.fdpProgram',
      'salaryMaster'
    ])->findOrFail($id);

    // Calculate leave statistics
    $leaveStats = [
      'total_applied' => $faculty->leaveApplications()->count(),
      'approved' => $faculty->leaveApplications()->where('status', 'approved')->count(),
      'pending' => $faculty->leaveApplications()->where('status', 'pending')->count(),
      'rejected' => $faculty->leaveApplications()->where('status', 'rejected')->count(),
    ];

    // Calculate FDP statistics
    $fdpStats = [
      'total_participated' => $faculty->fdpParticipations()->count(),
      'completed' => $faculty->completedFdpPrograms()->count(),
      'ongoing' => $faculty->fdpParticipations()
        ->whereHas('fdpProgram', function ($q) {
          $q->where('status', 'ongoing');
        })->count(),
    ];

    return view('hr.faculty.show', compact('faculty', 'leaveStats', 'fdpStats'));
  }

  /**
   * Show the form for editing the specified faculty member
   */
  public function edit($id)
  {
    $faculty = Faculty::findOrFail($id);
    $nationalities = NationalityMaster::orderBy('name')->get();
    return view('hr.faculty.edit', compact('faculty', 'nationalities'));
  }

  /**
   * Update the specified faculty member
   */
  public function update(Request $request, $id)
  {
    $faculty = Faculty::findOrFail($id);

    $validated = $request->validate([
      'USER_CODE' => 'required|string|max:50|unique:faculties,USER_CODE,' . $id,
      'FIRST_NAME' => 'required|string|max:100',
      'MIDDLE_NAME' => 'nullable|string|max:100',
      'LAST_NAME' => 'nullable|string|max:100',
      'GENDER' => 'required|in:1,2,3',
      'MAIL_ID' => 'required|email|max:100|unique:faculties,MAIL_ID,' . $id,
      'MOBILE_NO' => 'required|string|max:15',
      'ADDRESS' => 'nullable|string',
      'DOB' => 'nullable|date',
      'DOJ' => 'nullable|date',
      'DOL' => 'nullable|date',
      'reactivation_date' => 'nullable|date|after_or_equal:DOJ',
      'hr_remark' => 'nullable|string|max:1000',
      'IS_LEFT' => 'nullable|boolean',
      'NATIONALITY' => 'nullable|exists:nationality_masters,id',
      'employee_type' => 'nullable|string|max:50',
      'designation' => 'nullable|string|max:100',
      'qualification' => 'nullable|string|max:255',
      'specialization' => 'nullable|string|max:255',
      'experience_years' => 'nullable|integer|min:0',
      'pan_number' => 'nullable|string|max:20',
      'aadhar_number' => 'nullable|string|max:20',
      'bank_account_number' => 'nullable|string|max:50',
      'bank_ifsc_code' => 'nullable|string|max:20',
      'bank_name' => 'nullable|string|max:100',
      'emergency_contact_name' => 'nullable|string|max:100',
      'emergency_contact_number' => 'nullable|string|max:15',
      'permanent_address' => 'nullable|string',
      'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'CAMPUS_ID' => 'required|exists:campuses,id',
      'responsibility' => 'nullable|string|max:255',
      'paper_publications_count' => 'nullable|integer|min:0',
      'orcid_id' => 'nullable|string|max:50',
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
      $file = $request->file('photo');
      $validated['photo'] = StaticController::s3_file_uploader($file, 'faculty_photos');
    }

    $faculty->update($validated);

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty member updated successfully!');
  }

  /**
   * Remove the specified faculty member
   */
  public function destroy($id)
  {
    $faculty = Faculty::findOrFail($id);
    $faculty->delete();

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Faculty member deleted successfully!');
  }

  /**
   * Mark faculty as left
   */
  public function markAsLeft(Request $request, $id)
  {
    $validated = $request->validate([
      'DOL' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($id);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 1,
      'DOL' => $validated['DOL'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'deactivated',
      $validated['DOL'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      1
    );

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty marked as left!');
  }

  /**
   * Restore faculty (mark as active)
   */
  public function restore(Request $request, $id)
  {
    $validated = $request->validate([
      'DOJ' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($id);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 0,
      'DOL' => null,
      'DOJ' => $validated['DOJ'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'reactivated',
      $validated['DOJ'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      0
    );

    return redirect()->route('hr.faculty.show', $faculty->id)
      ->with('success', 'Faculty restored to active status!');
  }

  function deactivateFaculty(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:faculties,id',
      'resignation_date' => 'required|date',
      'hr_remark' => 'nullable|string|max:1000',
    ]);

    $faculty = Faculty::findOrFail($validated['id']);
    $oldStatus = (int) $faculty->IS_LEFT;
    $faculty->update([
      'IS_LEFT' => 1,
      'DOL' => $validated['resignation_date'],
      'reactivation_date' => null,
      'hr_remark' => $validated['hr_remark'] ?? $faculty->hr_remark,
    ]);

    $this->logStatusEvent(
      $faculty,
      'deactivated',
      $validated['resignation_date'],
      $validated['hr_remark'] ?? null,
      $oldStatus,
      1
    );

    return redirect()->route('hr.faculty.index')
      ->with('success', 'Staff deactivated successfully!');
  }

  /**
   * Create a faculty status transition audit record.
   */
  private function logStatusEvent(Faculty $faculty, string $eventType, ?string $statusOn, ?string $remark, ?int $oldStatus, ?int $newStatus): void
  {
    HrFacultyStatusHistory::create([
      'faculty_id' => $faculty->id,
      'event_type' => $eventType,
      'status_on' => $statusOn,
      'remark' => $remark,
      'old_status' => $oldStatus,
      'new_status' => $newStatus,
      'acted_by' => Auth::id(),
    ]);
  }
}

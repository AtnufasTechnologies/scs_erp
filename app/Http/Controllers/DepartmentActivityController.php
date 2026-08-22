<?php

namespace App\Http\Controllers;

use App\Models\DepartmentActivity;
use App\Models\DepartmentActivityHasParticipant;
use App\Models\StudentMaster;
use App\Models\Subject;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectFacultyMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DepartmentActivityController extends Controller
{
  /**
   * Display a listing of activities for a department
   */
  public function index($subjectId)
  {
    $subject = Subject::findOrFail($subjectId);

    $activities = DepartmentActivity::where('subject_id', $subjectId)
      ->with(['creator', 'updater', 'participants'])
      ->orderBy('activity_date', 'desc')
      ->paginate(10);

    $upcomingActivities = DepartmentActivity::where('subject_id', $subjectId)
      ->upcoming()
      ->take(5)
      ->get();

    $stats = [
      'total' => DepartmentActivity::where('subject_id', $subjectId)->count(),
      'upcoming' => DepartmentActivity::where('subject_id', $subjectId)->upcoming()->count(),
      'completed' => DepartmentActivity::where('subject_id', $subjectId)->completed()->count(),
      'this_month' => DepartmentActivity::where('subject_id', $subjectId)
        ->whereMonth('activity_date', now()->month)
        ->whereYear('activity_date', now()->year)
        ->count()
    ];

    $activityTypes = [
      'seminar' => 'Seminar',
      'workshop' => 'Workshop',
      'conference' => 'Conference',
      'fete' => 'Fete',
      'cultural' => 'Cultural Event',
      'sports' => 'Sports Event',
      'guest_lecture' => 'Guest Lecture',
      'competition' => 'Competition',
      'exhibition' => 'Exhibition',
      'other' => 'Other'
    ];

    return view('admin.department.activities.index', compact(
      'subject',
      'activities',
      'upcomingActivities',
      'stats',
      'activityTypes'
    ));
  }

  /**
   * Store a newly created activity
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'subject_id' => 'required|exists:subjects,id',
      'title' => 'required|string|max:255',
      'activity_type' => 'required|string',
      'description' => 'nullable|string',
      'venue' => 'nullable|string|max:255',
      'activity_date' => 'required|date',
      'start_time' => 'nullable|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'organizer_name' => 'nullable|string|max:255',
      'organizer_email' => 'nullable|email|max:255',
      'organizer_phone' => 'nullable|string|max:20',
      'expected_participants' => 'nullable|integer|min:0',
      'budget' => 'nullable|numeric|min:0',
      'status' => 'nullable|in:planned,ongoing,completed,cancelled',
      'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    $data = $request->except('banner_image');
    $data['created_by'] = Auth::id();

    // Handle banner image upload
    if ($request->hasFile('banner_image')) {
      $file = $request->file('banner_image');
      $filename = time() . '_' . $file->getClientOriginalName();
      $path = $file->storeAs('department_activities', $filename, 'public');
      $data['banner_image'] = $path;
    }

    DepartmentActivity::create($data);

    return redirect()->back()->with('success', 'Activity created successfully!');
  }

  /**
   * Update the specified activity
   */
  public function update(Request $request, $id)
  {
    $activity = DepartmentActivity::findOrFail($id);

    $validator = Validator::make($request->all(), [
      'title' => 'required|string|max:255',
      'activity_type' => 'required|string',
      'description' => 'nullable|string',
      'venue' => 'nullable|string|max:255',
      'activity_date' => 'required|date',
      'start_time' => 'nullable|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'organizer_name' => 'nullable|string|max:255',
      'organizer_email' => 'nullable|email|max:255',
      'organizer_phone' => 'nullable|string|max:20',
      'expected_participants' => 'nullable|integer|min:0',
      'actual_participants' => 'nullable|integer|min:0',
      'budget' => 'nullable|numeric|min:0',
      'actual_expense' => 'nullable|numeric|min:0',
      'status' => 'nullable|in:planned,ongoing,completed,cancelled',
      'remarks' => 'nullable|string',
      'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    $data = $request->except('banner_image');
    $data['updated_by'] = Auth::id();

    // Handle banner image upload
    if ($request->hasFile('banner_image')) {
      // Delete old image
      if ($activity->banner_image) {
        Storage::disk('public')->delete($activity->banner_image);
      }

      $file = $request->file('banner_image');
      $filename = time() . '_' . $file->getClientOriginalName();
      $path = $file->storeAs('department_activities', $filename, 'public');
      $data['banner_image'] = $path;
    }

    $activity->update($data);

    return redirect()->back()->with('success', 'Activity updated successfully!');
  }

  /**
   * Remove the specified activity
   */
  public function destroy($id)
  {
    $activity = DepartmentActivity::findOrFail($id);

    // Delete banner image if exists
    if ($activity->banner_image) {
      Storage::disk('s3')->delete($activity->banner_image);
    }
    if ($activity->report_file) {
      Storage::disk('s3')->delete($activity->report_file);
    }

    DepartmentActivityHasParticipant::where('activity_id', $id)->delete();

    $activity->delete();

    return redirect()->back()->with('success', 'Activity deleted successfully!');
  }

  /**
   * Get activity details for viewing/editing
   */
  public function show($id)
  {
    $activity = DepartmentActivity::with(['subject', 'creator', 'updater'])
      ->findOrFail($id);

    return response()->json($activity);
  }

  /**
   * Update activity status
   */
  public function updateStatus(Request $request, $id)
  {
    $activity = DepartmentActivity::findOrFail($id);

    $request->validate([
      'status' => 'required|in:planned,ongoing,completed,cancelled'
    ]);

    $activity->update([
      'status' => $request->status,
      'updated_by' => Auth::id()
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Status updated successfully!'
    ]);
  }

  /**
   * Get activities by type
   */
  public function getByType(Request $request, $subjectId)
  {
    $type = $request->input('type');

    $activities = DepartmentActivity::where('subject_id', $subjectId)
      ->when($type, function ($query) use ($type) {
        return $query->byType($type);
      })
      ->orderBy('activity_date', 'desc')
      ->paginate(10);

    return response()->json($activities);
  }


  function activityParticipants($activityId)
  {
    $activity = DepartmentActivity::findOrFail($activityId);
    $participants = $activity->participants()->orderBy('created_at', 'desc')->get();

    $deptId = SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
    if (empty($deptId)) {
      $deptId = $activity->subject_id;
    }

    $internalFaculties = SubjectFacultyMaster::with('faculty')
      ->where('subject_id', $deptId)
      ->get()
      ->pluck('faculty')
      ->filter()
      ->unique('id')
      ->map(function ($faculty) {
        $fullName = trim(($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? ''));
        return [
          'id' => $faculty->id,
          'name' => $fullName !== '' ? $fullName : 'Faculty #' . $faculty->id,
          'email' => $faculty->MAIL_ID,
          'phone' => $faculty->MOBILE_NO,
        ];
      })
      ->sortBy('name')
      ->values();

    $internalStudents = StudentMaster::query()
      ->where('department', $deptId)
      ->where('is_left', 0)
      ->where('is_deleted', 0)
      ->orderBy('first_name')
      ->orderBy('last_name')
      ->get(['id', 'first_name', 'last_name', 'roll_no', 'mail_id', 'mobile_no'])
      ->map(function ($student) {
        $fullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        return [
          'id' => $student->id,
          'name' => $fullName !== '' ? $fullName : 'Student #' . $student->id,
          'roll_no' => $student->roll_no,
          'email' => $student->mail_id,
          'phone' => $student->mobile_no,
        ];
      })
      ->values();

    return view('admin.department.activities.participants', compact('activity', 'participants', 'internalFaculties', 'internalStudents'));
  }

  function addParticipant(Request $request, $activityId)
  {
    $activity = DepartmentActivity::findOrFail($activityId);

    $request->validate([
      'participant_category' => 'required|in:faculty,student,other',
      'participant_type' => 'required|in:internal,external',
      'participant_name' => 'nullable|string|max:255',
      'participant_email' => 'nullable|email|max:255',
      'participant_phone' => 'nullable|string|max:30',
      'participant_rollno' => 'nullable|string|max:100',
      'internal_faculty_id' => 'nullable|integer',
      'internal_student_id' => 'nullable|integer',
      'is_incharge' => 'nullable|boolean',
      'hours_spent' => 'nullable|numeric|min:0|max:999.99',
      'institution_name' => 'nullable|string|max:255',
    ]);

    $userId = Auth::user()->id;
    $dept_id = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
    if (empty($dept_id)) {
      $dept_id = $activity->subject_id;
    }

    $participantName = trim((string) $request->participant_name);
    $participantEmail = $request->participant_email;
    $participantPhone = $request->participant_phone;
    $participantRollNo = $request->participant_rollno;

    if ($request->participant_type === 'internal' && $request->participant_category === 'faculty') {
      $facultyMap = SubjectFacultyMaster::where('subject_id', $dept_id)
        ->where('faculty_id', (int) $request->internal_faculty_id)
        ->first();

      if (!$facultyMap || !$facultyMap->faculty) {
        return redirect()->back()->withErrors(['internal_faculty_id' => 'Please select a valid department faculty.'])->withInput();
      }

      $faculty = $facultyMap->faculty;
      $participantName = trim(($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? ''));
      $participantEmail = $faculty->MAIL_ID;
      $participantPhone = $faculty->MOBILE_NO;
      $participantRollNo = null;
    }

    if ($request->participant_type === 'internal' && $request->participant_category === 'student') {
      $student = StudentMaster::where('department', $dept_id)
        ->where('is_left', 0)
        ->where('is_deleted', 0)
        ->where('id', (int) $request->internal_student_id)
        ->first();

      if (!$student) {
        return redirect()->back()->withErrors(['internal_student_id' => 'Please select a valid department student.'])->withInput();
      }

      $participantName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
      $participantEmail = $student->mail_id;
      $participantPhone = $student->mobile_no;
      $participantRollNo = $student->roll_no;
    }

    if ($participantName === '') {
      return redirect()->back()->withErrors(['participant_name' => 'Participant name is required.'])->withInput();
    }

    $isIncharge = false;
    $hoursSpent = null;
    if ($request->participant_type === 'internal' && $request->participant_category === 'faculty') {
      $isIncharge = $request->boolean('is_incharge');
      if ($isIncharge) {
        if (!$request->filled('hours_spent')) {
          return redirect()->back()->withErrors(['hours_spent' => 'Please enter hours spent for incharge.'])->withInput();
        }

        $hoursSpent = (float) $request->hours_spent;
      }
    }

    $activity->participants()->create([
      'dept_id' => $dept_id,
      'activity_id' => $request->activityId,
      'participant_category' => $request->participant_category,
      'participation_type' => $request->participant_type,
      'participant_name' => $participantName,
      'participant_email' => $participantEmail,
      'participant_phone' => $participantPhone,
      'participant_rollno' => $participantRollNo,
      'institution_name' => $request->institution_name,
      'is_incharge' => $isIncharge,
      'hours_spent' => $hoursSpent

    ]);

    return redirect()->back()->with('success', 'Participant added successfully!');
  }

  function removeParticipant($id)
  {
    $participant = DepartmentActivityHasParticipant::findOrFail($id);
    $participant->delete();

    return redirect()->back()->with('success', 'Participant removed successfully!');
  }

  function updateParticipantHours(Request $request, $id)
  {
    $request->validate([
      'hours_spent' => 'required|numeric|min:0|max:999.99',
    ]);

    $participant = DepartmentActivityHasParticipant::findOrFail($id);
    $deptId = SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');

    if ((int) $participant->dept_id !== (int) $deptId) {
      return redirect()->back()->with('error', 'Unauthorized participant update attempt.');
    }

    if (
      $participant->participation_type !== 'internal' ||
      $participant->participant_category !== 'faculty' ||
      !(bool) $participant->is_incharge
    ) {
      return redirect()->back()->with('error', 'Hours can be updated only for internal faculty marked as incharge.');
    }

    $participant->update([
      'hours_spent' => (float) $request->hours_spent,
    ]);

    return redirect()->back()->with('success', 'Incharge hours updated successfully!');
  }

  function uploadActivityReport(Request $request, $activityId)
  {


    $request->validate([
      'report_file' => 'required|file|mimes:pdf|max:10240', // Max size 10MB
    ]);

    $activity = DepartmentActivity::findOrFail($activityId);

    // Handle file upload
    if ($request->hasFile('report_file')) {
      $file = $request->file('report_file');
      $filename = StaticController::s3_file_uploader($file, 'dept_activity_reports');

      // Save the report path in the database (you may want to create a new column for this)
      $activity->update([
        'report_file' => $filename,
        'updated_by' => Auth::id()
      ]);

      return redirect()->back()->with('success', 'Report uploaded successfully!');
    }

    return redirect()->back()->with('error', 'No file uploaded.');
  }
}

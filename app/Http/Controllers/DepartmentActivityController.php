<?php

namespace App\Http\Controllers;

use App\Models\DepartmentActivity;
use App\Models\DepartmentActivityHasParticipant;
use App\Models\Subject;
use App\Models\SubjectHasDeptAdmin;
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
      ->with(['creator', 'updater'])
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
      Storage::disk('public')->delete($activity->banner_image);
    }

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

    return view('admin.department.activities.participants', compact('activity', 'participants'));
  }

  function addParticipant(Request $request, $activityId)
  {
    $activity = DepartmentActivity::findOrFail($activityId);

    $request->validate([
      'participant_category' => 'required',
      'participant_type' => 'required',
      'participant_name' => 'required|string|max:255',
      'institution_name' => 'nullable|string|max:255',
    ]);

    $userId = Auth::user()->id;
    $dept_id = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');

    $activity->participants()->create([
      'dept_id' => $dept_id,
      'activity_id' => $request->activityId,
      'participant_category' => $request->participant_category,
      'participant_type' => $request->participant_type,
      'participant_name' => $request->participant_name,
      'participant_email' => $request->participant_email,
      'participant_phone' => $request->participant_phone,
      'participant_rollno' => $request->participant_rollno,
      'institution_name' => $request->institution_name

    ]);

    return redirect()->back()->with('success', 'Participant added successfully!');
  }

  function removeParticipant($id)
  {
    $participant = DepartmentActivityHasParticipant::findOrFail($id);
    $participant->delete();

    return redirect()->back()->with('success', 'Participant removed successfully!');
  }
}

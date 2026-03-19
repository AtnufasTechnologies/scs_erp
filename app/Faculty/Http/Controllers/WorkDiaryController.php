<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\HourMaster;
use App\Models\MethodologyMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\WorkDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkDiaryController extends Controller
{
  public function index(Request $request)
  {
    $facultyId = $this->getFacultyId();

    // Get current week or the requested week
    $weekStart = $request->input('week_start')
      ? Carbon::parse($request->input('week_start'))
      : Carbon::now()->startOfWeek();

    $weekEnd = $weekStart->copy()->endOfWeek();

    // Get work diary entries for the week
    $entries = WorkDiary::where('faculty_id', $facultyId)
      ->whereBetween('date', [$weekStart, $weekEnd])
      ->get();

    // Get hours from HourMaster table
    $hours = HourMaster::orderBy('id')->get();

    // Organize entries by weekday and hour
    $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $calendar = [];

    foreach ($weekdays as $day) {
      $calendar[$day] = [];
      foreach ($hours as $hour) {
        $calendar[$day][$hour->title] = [];
      }
    }

    foreach ($entries as $entry) {
      $weekday = $entry->date->format('l');
      $hour = $entry->hour;
      if (isset($calendar[$weekday][$hour])) {
        $calendar[$weekday][$hour][] = $entry;
      }
    }

    // Get active methodologies
    $methodologies = MethodologyMaster::active()->ordered()->get();

    return view('faculty.workdiary', [
      'weekStart' => $weekStart,
      'weekEnd' => $weekEnd,
      'entries' => $entries,
      'hours' => $hours,
      'calendar' => $calendar,
      'weekdays' => $weekdays,
      'methodologies' => $methodologies
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'date' => 'required|date',
      'hour' => 'required|integer|min:0|max:23',
      'description' => 'required|string|max:1000',
      'methodology' => 'nullable|exists:methodology_masters,name',
      'class_type' => 'nullable|string|in:extra,regular,substitution'
    ]);

    $facultyId = $this->getFacultyId();

    $workDiary = WorkDiary::updateOrCreate(
      [
        'faculty_id' => $facultyId,
        'date' => $request->date,
        'hour' => $request->hour
      ],
      [
        'description' => $request->description,
        'methodology' => $request->methodology,
        'class_type' => $request->class_type,
        'status' => 'pending'
      ]
    );

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry saved successfully',
      'data' => $workDiary
    ]);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'description' => 'required|string|max:1000',
      'methodology' => 'nullable|exists:methodology_masters,name',
      'class_type' => 'nullable|string|in:extra,regular,substitution'
    ]);

    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);

    $workDiary->update([
      'description' => $request->description,
      'methodology' => $request->methodology,
      'class_type' => $request->class_type
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry updated successfully',
      'data' => $workDiary
    ]);
  }

  public function destroy($id)
  {
    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);
    $workDiary->delete();

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry deleted successfully'
    ]);
  }

  public function toggleStatus($id)
  {
    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);

    $workDiary->status = $workDiary->status === 'pending' ? 'completed' : 'pending';
    $workDiary->save();

    return response()->json([
      'success' => true,
      'status' => $workDiary->status,
      'message' => 'Status updated successfully'
    ]);
  }

  private function getFacultyId()
  {
    $userId = Auth::user()->id;
    return SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
  }
}

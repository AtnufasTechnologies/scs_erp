<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\CourseSeatAllocation;
use App\Models\Semester;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseSeatController extends Controller
{
  // ──────────────────────────────────────────────────────────────────
  //  INDEX  – show all seat allocations grouped by batch → semester
  // ──────────────────────────────────────────────────────────────────

  public function index(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $batches   = BatchMaster::all();
    $semesters = Semester::all();

    // All courses assigned to this department's subject
    $courses = SubjectCourseMaster::with([
      'courseMaster.semestermaster',
      'courseMaster.coursetypemaster',
    ])
      ->where('subject_id', $subjectId)
      ->get()
      ->map(fn($scm) => $scm->courseMaster)
      ->filter()
      ->values();

    // Existing allocations
    $allocations = CourseSeatAllocation::with(['batch', 'semester', 'courseMaster'])
      ->where('subject_id', $subjectId)
      ->get()
      ->keyBy(fn($a) => "{$a->batch_id}_{$a->semester_id}_{$a->course_master_id}");

    return view('admin.subject.course-seat-manager', compact(
      'batches',
      'semesters',
      'courses',
      'allocations',
      'subjectId'
    ));
  }

  // ──────────────────────────────────────────────────────────────────
  //  STORE  – add a new seat allocation
  // ──────────────────────────────────────────────────────────────────

  public function store(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $request->validate([
      'batch_id'         => 'required|exists:batch_masters,id',
      'semester_id'      => 'required|exists:semesters,id',
      'course_master_id' => 'required|exists:program_course_masters,id',
      'total_seats'      => 'required|integer|min:1',
    ]);

    // Ensure the course belongs to this department
    $courseOwned = SubjectCourseMaster::where('subject_id', $subjectId)
      ->where('course_master_id', $request->course_master_id)
      ->exists();

    if (!$courseOwned) {
      return redirect()->back()->with('error', 'Selected course does not belong to your department.');
    }

    $exists = CourseSeatAllocation::where('subject_id', $subjectId)
      ->where('batch_id', $request->batch_id)
      ->where('semester_id', $request->semester_id)
      ->where('course_master_id', $request->course_master_id)
      ->exists();

    if ($exists) {
      return redirect()->back()->with('error', 'A seat allocation for this Batch / Semester / Course already exists. Use the edit option to update it.');
    }

    CourseSeatAllocation::create([
      'subject_id'       => $subjectId,
      'batch_id'         => $request->batch_id,
      'semester_id'      => $request->semester_id,
      'course_master_id' => $request->course_master_id,
      'total_seats'      => $request->total_seats,
      'is_open'          => false,
    ]);

    return redirect()->back()->with('success', 'Seat allocation added successfully.');
  }

  // ──────────────────────────────────────────────────────────────────
  //  UPDATE  – change seat count or open/close status
  // ──────────────────────────────────────────────────────────────────

  public function update(Request $request, int $id)
  {
    $allocation = CourseSeatAllocation::where('subject_id', $this->resolveSubjectId())
      ->findOrFail($id);

    $request->validate([
      'total_seats' => 'required|integer|min:1',
    ]);

    $allocation->total_seats = $request->total_seats;
    $allocation->save();

    return redirect()->back()->with('success', 'Seat count updated successfully.');
  }

  // ──────────────────────────────────────────────────────────────────
  //  TOGGLE  – open / close registration for a course
  // ──────────────────────────────────────────────────────────────────

  public function toggle(int $id)
  {
    $allocation = CourseSeatAllocation::where('subject_id', $this->resolveSubjectId())
      ->findOrFail($id);

    $allocation->is_open = !$allocation->is_open;
    $allocation->save();

    $state = $allocation->is_open ? 'opened' : 'closed';
    return redirect()->back()->with('success', "Registration {$state} for this course allocation.");
  }

  // ──────────────────────────────────────────────────────────────────
  //  BULK TOGGLE  – open / close all allocations for a batch+semester
  // ──────────────────────────────────────────────────────────────────

  public function bulkToggle(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $request->validate([
      'batch_id'    => 'required|exists:batch_masters,id',
      'semester_id' => 'required|exists:semesters,id',
      'is_open'     => 'required|boolean',
    ]);

    CourseSeatAllocation::where('subject_id', $subjectId)
      ->where('batch_id', $request->batch_id)
      ->where('semester_id', $request->semester_id)
      ->update(['is_open' => $request->is_open]);

    $state = $request->is_open ? 'opened' : 'closed';
    return redirect()->back()->with('success', "Registration {$state} for all courses in this batch/semester.");
  }

  // ──────────────────────────────────────────────────────────────────
  //  DESTROY  – delete a seat allocation
  // ──────────────────────────────────────────────────────────────────

  public function destroy(int $id)
  {
    $allocation = CourseSeatAllocation::where('subject_id', $this->resolveSubjectId())
      ->findOrFail($id);

    $allocation->delete();

    return redirect()->back()->with('success', 'Seat allocation removed.');
  }

  // ──────────────────────────────────────────────────────────────────
  //  HELPER
  // ──────────────────────────────────────────────────────────────────

  private function resolveSubjectId(): int
  {
    return (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
  }
}

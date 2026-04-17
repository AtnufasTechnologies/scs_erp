<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InterMark;
use App\Models\InternalMarkLog;
use App\Models\ProgramCourseMaster;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalMarksController extends Controller
{
  /**
   * Show course/semester/year selection form
   */
  public function index()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get syllabus IDs assigned to this faculty
    $syllabusIds = SubjectHasRoutine::where('faculty_id', $facultyId)->pluck('syllabus_id');

    // Get unique courses from syllabi (course_id in subject_has_syllabi = ProgramCourseMaster id)
    $courseIds = SubjectHasSyllabus::whereIn('id', $syllabusIds)
      ->pluck('course_id')
      ->unique()
      ->values();

    $courses = ProgramCourseMaster::whereIn('id', $courseIds)
      ->with(['semestermaster', 'departmentmaster'])
      ->get();

    // Get unique semesters from syllabi
    $semesterIds = SubjectHasSyllabus::whereIn('id', $syllabusIds)
      ->pluck('semester_id')
      ->unique()
      ->values();

    $semesters = \App\Models\Semester::whereIn('id', $semesterIds)->get();

    return view('faculty.internal-marks.index', compact('courses', 'semesters'));
  }

  /**
   * Show the marks entry form for a specific course/semester
   */
  public function enter(Request $request)
  {
    $request->validate([
      'course_id'     => 'required|integer',
      'semester'      => 'required',
      'academic_year' => 'nullable|integer',
    ]);

    $courseId = $request->input('course_id');
    $semester = $request->input('semester');
    $academicYear = $request->input('academic_year');

    $course = ProgramCourseMaster::with(['semestermaster', 'departmentmaster'])->findOrFail($courseId);

    // Fetch students enrolled in this course/semester
    $studentIds = StudentCourseInfo::where('course_id', $courseId)
      ->where('semester', $semester)
      ->pluck('student_id');

    $students = StudentMaster::whereIn('id', $studentIds)
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('roll_no')
      ->orderBy('first_name')
      ->get(['id', 'first_name', 'last_name', 'roll_no', 'register_no']);

    // Load existing marks
    $existingMarks = InterMark::where('course_id', $courseId)
      ->where('semester', $semester)
      ->where('is_deleted', 0)
      ->whereIn('student_id', $studentIds)
      ->get()
      ->keyBy('student_id');

    return view('faculty.internal-marks.enter', compact(
      'course',
      'students',
      'existingMarks',
      'semester',
      'academicYear'
    ));
  }

  /**
   * Store/update internal marks
   */
  public function store(Request $request)
  {
    $request->validate([
      'course_id'            => 'required|integer',
      'semester'             => 'required',
      'marks'                => 'required|array',
      'marks.*.student_id'   => 'required|exists:student_masters,id',
      'marks.*.internal_mark' => 'nullable|string|max:45',
    ]);

    $courseId = $request->course_id;
    $semester = $request->semester;
    $saved = 0;
    $user = Auth::user();

    foreach ($request->marks as $entry) {
      if ($entry['internal_mark'] === null || $entry['internal_mark'] === '') {
        continue;
      }

      // Check if mark already exists
      $existing = InterMark::where('student_id', $entry['student_id'])
        ->where('course_id', $courseId)
        ->where('semester', $semester)
        ->first();

      if ($existing && $existing->internal_mark !== $entry['internal_mark']) {
        // Log the change
        InternalMarkLog::create([
          'internal_mark_id' => $existing->id,
          'student_id'       => $entry['student_id'],
          'course_id'        => $courseId,
          'semester'         => $semester,
          'old_mark'         => $existing->internal_mark,
          'new_mark'         => $entry['internal_mark'],
          'changed_by'       => $user->id,
          'changed_by_name'  => $user->name,
        ]);
      }

      InterMark::updateOrCreate(
        [
          'student_id' => $entry['student_id'],
          'course_id'  => $courseId,
          'semester'   => $semester,
        ],
        [
          'internal_mark'  => $entry['internal_mark'],
          'academic_year'  => $request->academic_year ?? null,
          'semester_type'  => $request->semester_type ?? null,
          'is_deleted'     => 0,
        ]
      );
      $saved++;
    }

    return redirect()->route('faculty.internal-marks.index')
      ->with('success', "Internal marks saved for {$saved} students.");
  }

  /**
   * View submitted marks for a course/semester
   */
  public function view(Request $request)
  {
    $courseId = $request->input('course_id');
    $semester = $request->input('semester');

    $course = ProgramCourseMaster::with(['semestermaster', 'departmentmaster'])->findOrFail($courseId);

    $marks = InterMark::where('course_id', $courseId)
      ->where('semester', $semester)
      ->where('is_deleted', 0)
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderBy('created_at', 'desc')
      ->get();

    return view('faculty.internal-marks.view', compact('course', 'marks', 'semester'));
  }
}

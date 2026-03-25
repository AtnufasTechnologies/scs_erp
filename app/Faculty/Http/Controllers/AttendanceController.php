<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
  /**
   * Display the attendance interface
   */
  public function index()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get all subjects assigned to this faculty
    $syllabusAssignments = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->distinct()
      ->get(['id', 'syllabus_id']);

    return view('faculty.attendance.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }

  /**
   * Show attendance form for a specific class
   */
  public function takeAttendance(Request $request, $routineId)
  {
    $syllabusAssignment = SubjectHasRoutine::with([
      'syllabus.subject',
      'syllabus.semestermaster',
      'syllabus.courseLink.courseMaster'
    ])->findOrFail($routineId);

    $date = $request->get('date', date('Y-m-d'));
    $lectureTime = $request->get('lecture_time', date('H:i'));

    // Get students enrolled in this subject
    $students = $this->getEnrolledStudents($syllabusAssignment->syllabus_id);

    // Get existing attendance records for today
    $existingAttendance = StudentAttendance::where('routine_id', $routineId)
      ->where('attendance_date', $date)
      ->where('lecture_start_time', $lectureTime . ':00')
      ->get()
      ->keyBy('student_id');

    return view('faculty.attendance.take', compact(
      'syllabusAssignment',
      'students',
      'date',
      'lectureTime',
      'existingAttendance'
    ));
  }

  /**
   * Store attendance records
   */
  public function storeAttendance(Request $request)
  {
    $request->validate([
      'routine_id' => 'required|exists:subject_has_routines,id',
      'attendance_date' => 'required|date',
      'lecture_start_time' => 'required',
      'lecture_end_time' => 'nullable',
      'attendance' => 'required|array',
      'attendance.*' => 'in:present,absent,late,excused',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = \Carbon\Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      foreach ($request->attendance as $studentId => $status) {
        StudentAttendance::updateOrCreate(
          [
            'routine_id' => $request->routine_id,
            'student_id' => $studentId,
            'attendance_date' => $request->attendance_date,
            'lecture_start_time' => $request->lecture_start_time,
          ],
          [
            'lecture_end_time' => $request->lecture_end_time,
            'status' => $status,
            'remarks' => $request->remarks[$studentId] ?? null,
          ]
        );
      }

      DB::commit();
      return redirect()
        ->route('faculty.attendance.index')
        ->with('success', 'Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Failed to record attendance. Please try again.');
    }
  }

  /**
   * View attendance records
   */
  public function viewAttendance($routineId)
  {
    $syllabusAssignment = SubjectHasRoutine::with([
      'syllabus.subject',
      'syllabus.semestermaster',
      'syllabus.courseLink.courseMaster'
    ])->findOrFail($routineId);

    $students = $this->getEnrolledStudents($syllabusAssignment->syllabus_id);

    // Get all attendance records for this subject
    $attendanceRecords = StudentAttendance::where('routine_id', $routineId)
      ->orderBy('attendance_date', 'desc')
      ->orderBy('lecture_start_time', 'desc')
      ->get()
      ->groupBy('attendance_date');

    // Calculate attendance statistics
    $statistics = [];
    foreach ($students as $student) {
      $statistics[$student->id] = [
        'student' => $student,
        'percentage' => StudentAttendance::getAttendancePercentage($student->id, $routineId),
        'present' => StudentAttendance::where('student_id', $student->id)
          ->where('routine_id', $routineId)
          ->where('status', 'present')
          ->count(),
        'absent' => StudentAttendance::where('student_id', $student->id)
          ->where('routine_id', $routineId)
          ->where('status', 'absent')
          ->count(),
        'late' => StudentAttendance::where('student_id', $student->id)
          ->where('routine_id', $routineId)
          ->where('status', 'late')
          ->count(),
      ];
    }

    return view('faculty.attendance.view', compact(
      'syllabusAssignment',
      'attendanceRecords',
      'statistics'
    ));
  }

  /**
   * Get enrolled students for a subject
   */
  private function getEnrolledStudents($syllabusId)
  {
    return StudentMaster::whereHas('studentsyllabusinfo', function ($query) use ($syllabusId) {
      $query->where('course_id', $syllabusId);
      $query->where('is_deleted', 0);
    })->where('is_deleted', 0)->get();
  }

  /**
   * Delete attendance record
   */
  public function deleteAttendance($id)
  {
    try {
      $attendance = StudentAttendance::findOrFail($id);
      $attendance->delete();

      return back()->with('success', 'Attendance record deleted successfully!');
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to delete attendance record.');
    }
  }
}

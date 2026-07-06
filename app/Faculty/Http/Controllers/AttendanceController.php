<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\ExtraClassAttendance;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
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
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->unique(fn($routine) => (string) ($routine->syllabus_id ?? '0') . '_' . strtolower(trim((string) ($routine->shift ?? 'common'))))
      ->values();

    return view('faculty.attendance.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }


  /**
   * Store attendance records
   */
  public function storeAttendance(Request $request)
  {
    $request->validate([
      'routine_id' => 'required|exists:subject_has_routines,id',
      'attendance_date' => 'required|date',
      'attendance' => 'required|array',
      'attendance.*' => 'in:present,absent,late,excused',
      'course_id' => 'required',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      $userId = Auth::user()->id;
      $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
      foreach ($request->attendance as $studentId => $status) {
        StudentAttendance::updateOrCreate(
          [
            'routine_id' => $request->routine_id,
            'student_id' => $studentId,
            'attendance_date' => $request->attendance_date,
            'course_id' => $request->course_id,
            'hour_id' => $request->hour_id,
            'faculty_id' => $facultyId,
            'semester_id' => $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
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
  public function viewAttendance(Request $request)
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
      ->get()
      ->unique('syllabus_id');


    $query = StudentAttendance::orderBy('attendance_date', 'desc')
      ->orderBy('hour_id', 'asc')
      ->where('faculty_id', $facultyId);

    if (!empty($request->attendance_date)) {
      $query->where('attendance_date', $request->attendance_date);
    }
    if (!empty($request->course_filter)) {
      $query->where('course_id', $request->course_filter);
    }

    $data = $query->get();


    return view('faculty.attendance.view', [
      'syllabusAssignments' => $syllabusAssignments,
      'attendanceRecords' => $data,

    ]);
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

  /**
   * Show attendance creation form for selected subject, hour, and date
   */
  public function getStudentList(Request $request)
  {
    $id = $request->input('rec_id');
    $syllabus_id = $request->input('syllabus_id');
    $hourId = $request->input('hour_id');
    $attendanceDate = $request->input('attendance_date', date('Y-m-d'));
    $semesterId = $request->input('semester_id');
    $batchId = (int) $request->input('batch_id');

    $record =  SubjectHasSyllabus::find($syllabus_id); // Validate syllabus_id
    if (!$record) {
      return back()->with('error', 'Invalid syllabus selected.');
    }

    $routine = SubjectHasRoutine::find($id);
    if (!$routine) {
      return back()->with('error', 'Invalid routine selected.');
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));

    $course_id = $record->course_id;
    $campusId = $record->subject->campus_id;
    $effectiveBatchId = !empty($batchId) ? $batchId : (int) ($record->batch_id ?? 0);

    if (empty($effectiveBatchId)) {
      return back()->with('error', 'Invalid batch selected.');
    }

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->select(
        'sm.id',
        'sm.roll_no',
        'sm.first_name',
        'sm.last_name',
        'sci.id as course_info_id',
        'sp.shift as program_shift'
      )
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $effectiveBatchId)
      ->where('sci.course_id', $course_id)
      ->where('sci.semester', $semesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    // 1) Prefer strict shift match so day/morning student sets stay isolated.
    $students = (clone $baseQuery)
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->get();

    // 2) Backward-compatible fallback for legacy program records without valid shift mapping.
    if ($students->isEmpty()) {
      $students = (clone $baseQuery)->get();
    }

    $syllabusAssignment = SubjectHasSyllabus::with([
      'courseLink.courseMaster.coursetypemaster',
      'semestermaster:id,title',
      'batchmaster'
    ])->find($syllabus_id);

    // Get existing attendance for this date/hour/routine
    $existingAttendance = StudentAttendance::where('routine_id', $id)
      ->where('attendance_date', $attendanceDate)
      ->where('hour_id', $hourId)
      ->get()
      ->keyBy('student_id');

    if ($request->attendance_type == 'regular') {

      return view('faculty.attendance.take', [
        'students' => $students,
        'recordId' => $id,
        'routineShift' => ucfirst($routineShift),
        'syllabusId' => $syllabus_id,
        'hourId' => $hourId,
        'attendanceDate' => $attendanceDate,
        'batchId' => $effectiveBatchId,
        'syllabusAssignment' => $syllabusAssignment,
        'course_id' => $course_id,
        'semesterId' => $semesterId,
        'existingAttendance' => $existingAttendance,
      ]);
    } else {
      return view('faculty.attendance.extra.take', [
        'students' => $students,
        'recordId' => $id,
        'routineShift' => ucfirst($routineShift),
        'syllabusId' => $syllabus_id,
        'hourId' => $hourId,
        'attendanceDate' => $attendanceDate,
        'batchId' => $effectiveBatchId,
        'syllabusAssignment' => $syllabusAssignment,
        'course_id' => $course_id,
        'semesterId' => $semesterId,
        'existingAttendance' => $existingAttendance,
      ]);
    }
  }

  function updateAttendance(Request $request, $id)
  {

    $request->validate([
      'attendance_date' => 'required',
      'extra.*' => 'in:late,excused',
      'status' => 'required|in:present,absent',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      if (!empty($request->extra)) {
        $extra = $request->extra;
      } else {
        $extra = null;
      }
      StudentAttendance::updateOrCreate(
        [
          'id' => $id,
        ],
        [
          'status' => $request->status,
          'attendance_method' => 'manual',
          'extra' => $extra,
        ]
      );


      DB::commit();
      return redirect()
        ->back()
        ->with('success', 'Attendance updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Failed to update attendance. Please try again.');
    }
  }


  function extraClasses()
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
      ->get()
      ->unique('syllabus_id');

    return view('faculty.attendance.extra.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }

  /**
   * Store remedial class attendance records
   */
  public function storeRemedialAttendance(Request $request)
  {
    $request->validate([
      'routine_id' => 'required|exists:subject_has_routines,id',
      'attendance_date' => 'required|date',
      'attendance' => 'required|array',
      'attendance.*' => 'in:present,absent',
      'course_id' => 'required',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      $userId = Auth::user()->id;
      $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

      // Ensure all required fields are present and properly typed
      if (!$facultyId) {
        throw new \Exception('Faculty ID not found for current user');
      }

      foreach ($request->attendance as $studentId => $status) {
        if ($status !== 'present') {
          continue;
        }
        ExtraClassAttendance::updateOrCreate(
          [
            'routine_id' => (int) $request->routine_id,
            'student_id' => (int) $studentId,
            'attendance_date' => $request->attendance_date,
            'course_id' => (int) $request->course_id,
            'hour_id' => $request->hour_id ? (int) $request->hour_id : null,
            'faculty_id' => (int) $facultyId,
            'semester_id' => (int) $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
          ]
        );
      }

      DB::commit();
      return redirect()
        ->route('faculty.attendance.view.remedial-class')
        ->with('success', 'Remedial Class Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Extra Class Attendance Error: ' . $e->getMessage());
      return back()->with('error', 'Failed to record attendance: ' . $e->getMessage());
    }
  }


  /**
   * View attendance records
   */
  public function viewExtraClassAttendance(Request $request)
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
      ->get()
      ->unique('syllabus_id');


    $query = ExtraClassAttendance::orderBy('attendance_date', 'desc')
      ->orderBy('hour_id', 'asc')
      ->where('faculty_id', $facultyId);

    if (!empty($request->attendance_date)) {
      $query->where('attendance_date', $request->attendance_date);
    }
    if (!empty($request->course_filter)) {
      $query->where('course_id', $request->course_filter);
    }

    $data = $query->get();


    return view('faculty.attendance.extra.view', [
      'syllabusAssignments' => $syllabusAssignments,
      'attendanceRecords' => $data,

    ]);
  }
}

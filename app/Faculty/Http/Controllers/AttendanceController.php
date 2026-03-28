<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use Carbon\Carbon;
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
      ->get()
      ->unique('syllabus_id');

    return view('faculty.attendance.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }

  /**
   * Show attendance form for a specific class
   */
  public function takeAttendance(Request $request, $routineId)
  {
    dd('testing');

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
    $batchId = $request->input('batch_id');

    $record =  SubjectHasSyllabus::find($syllabus_id); // Validate syllabus_id
    if (!$record) {
      return back()->with('error', 'Invalid syllabus selected.');
    }

    $course_id = $record->course_id;
    $campusId = $record->subject->campus_id;

    // return response()->json([
    //   'message' => 'Data received successfully',
    //   'data' => [
    //     'rec_id' => $id,
    //     'syllabus_id' => $syllabus_id,
    //     'hour_id' => $hourId,
    //     'attendance_date' => $attendanceDate,
    //     'semester_id' => $semesterId,
    //     'batch_id' => $batchId,
    //     'course_id' => $course_id,
    //     'campus_id' => $campusId
    //   ]
    // ]);

    // Fetch students from student_course_infos as per course_id, semester_id, campus_id
    $studentIds = StudentCourseInfo::where('course_id', $course_id)
      ->when($semesterId, function ($q) use ($semesterId) {
        $q->where('semester', $semesterId);
      })
      ->when($campusId, function ($q) use ($campusId) {
        $q->where('campus_id', $campusId);
      })

      ->pluck('student_id');

    $students = StudentMaster::whereIn('id', $studentIds)
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('first_name')
      ->orderBy('last_name')
      ->get(['id', 'first_name', 'last_name', 'roll_no']);

    $syllabusAssignment = SubjectHasSyllabus::with([
      'courseLink.courseMaster.coursetypemaster',
      'semestermaster:id,title',
      'batchmaster'
    ])->find($syllabus_id);


    return view('faculty.attendance.take', [
      'students' => $students,
      'recordId' => $id,
      'syllabusId' => $syllabus_id,
      'hourId' => $hourId,
      'attendanceDate' => $attendanceDate,
      'batchId' => $batchId,
      'syllabusAssignment' => $syllabusAssignment,
      'course_id' => $course_id,
      'semesterId' => $semesterId,
    ]);
  }
}

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
      ->get()
      ->unique('syllabus_id');

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
    $batch = $request->input('batch_id');

    $record =  SubjectHasSyllabus::find($syllabus_id); // Validate syllabus_id
    if (!$record) {
      return back()->with('error', 'Invalid syllabus selected.');
    }

    $course_id = $record->course_id;
    $campusId = $record->subject->campus_id;


    $students = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->select(
        'sm.id',
        'sm.roll_no',
        'sm.first_name',
        'sm.last_name',
        'sci.id as course_info_id'
      )
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sci.course_id', $course_id)
      ->where('sci.academic_year', $batch)
      ->where('sci.semester', $semesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct()
      ->get();

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

    return view('faculty.attendance.take', [
      'students' => $students,
      'recordId' => $id,
      'syllabusId' => $syllabus_id,
      'hourId' => $hourId,
      'attendanceDate' => $attendanceDate,
      'batchId' => $batch,
      'syllabusAssignment' => $syllabusAssignment,
      'course_id' => $course_id,
      'semesterId' => $semesterId,
      'existingAttendance' => $existingAttendance,
    ]);
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

  public function getStudentListExtraClass(Request $request)
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

    // Get academic year from batch_id (batch_name like "2024", "2025", etc.)
    $academicYear = $batchId;

    // Fetch DISTINCT students from student_course_infos as per course_id, semester_id, campus_id, academic_year
    $studentIds = StudentCourseInfo::where('course_id', $course_id)
      ->when($semesterId, function ($q) use ($semesterId) {
        $q->where('semester', $semesterId);
      })
      ->when($campusId, function ($q) use ($campusId) {
        $q->where('campus_id', $campusId);
      })
      ->when($academicYear, function ($q) use ($academicYear) {
        $q->where('academic_year', $academicYear);
      })
      ->distinct()
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

    // Get existing attendance for this date/hour/routine
    $existingAttendance = ExtraClassAttendance::where('routine_id', $id)
      ->where('attendance_date', $attendanceDate)
      ->where('hour_id', $hourId)
      ->get()
      ->keyBy('student_id');

    return view('faculty.attendance.extra.take', [
      'students' => $students,
      'recordId' => $id,
      'syllabusId' => $syllabus_id,
      'hourId' => $hourId,
      'attendanceDate' => $attendanceDate,
      'batchId' => $batchId,
      'syllabusAssignment' => $syllabusAssignment,
      'course_id' => $course_id,
      'semesterId' => $semesterId,
      'existingAttendance' => $existingAttendance,
    ]);
  }

  /**
   * Store remedial class attendance records
   */
  public function storeExtraAttendance(Request $request)
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
        if ($status === 'present') {
          ExtraClassAttendance::updateOrCreate(
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
      }

      DB::commit();
      return redirect()
        ->route('faculty.extra.classes')
        ->with('success', 'Remedial Class Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Failed to record attendance. Please try again.');
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

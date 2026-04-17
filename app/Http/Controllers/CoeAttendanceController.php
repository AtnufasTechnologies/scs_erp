<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\StudentMaster;
use App\Models\Subject;
use App\Models\AnnualSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoeAttendanceController extends Controller
{
  /**
   * Display attendance index page with exam selector
   */
  public function index()
  {
    // Get ongoing or upcoming exams
    $ongoingExams = Exam::where('status', 'ongoing')
      ->orWhere(function ($query) {
        $query->where('exam_date', '>=', today())
          ->where('exam_date', '<=', today()->addDays(7));
      })
      ->orderBy('exam_date', 'asc')
      ->get();

    // Get all sessions for dropdown
    $sessions = AnnualSession::where('status', 1)->get();

    // Get all subjects
    $subjects = Subject::latest()->get();

    // Get today's statistics
    $todayExams = Exam::whereDate('exam_date', today())->count();

    // Get attendance for today's exams
    $todayExamIds = Exam::whereDate('exam_date', today())->pluck('id');
    $todayPresent = ExamAttendance::whereIn('exam_id', $todayExamIds)
      ->where('status', 'present')->count();
    $todayAbsent = ExamAttendance::whereIn('exam_id', $todayExamIds)
      ->where('status', 'absent')->count();

    $totalToday = $todayPresent + $todayAbsent;
    $attendancePercent = $totalToday > 0 ? round(($todayPresent / $totalToday) * 100, 2) : 0;

    return view('coe.attendance.index', compact(
      'ongoingExams',
      'sessions',
      'subjects',
      'todayExams',
      'todayPresent',
      'todayAbsent',
      'attendancePercent'
    ));
  }

  /**
   * Show attendance marking page for selected exam
   */
  public function take(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'exam_id' => 'required|exists:exams,id',
      'session_id' => 'required',
      'subject_id' => 'required|exists:subjects,id',
      'exam_date' => 'required|date'
    ]);

    if ($validator->fails()) {
      return redirect()->route('coe.attendance.index')
        ->with('error', 'Invalid exam selection. Please try again.');
    }

    $examId = $request->exam_id;
    $sessionId = $request->session_id;
    $subjectId = $request->subject_id;
    $examDate = $request->exam_date;

    // Get exam details
    $examDetails = Exam::find($examId);
    $sessionDetails = AnnualSession::find($sessionId);
    $subjectDetails = Subject::find($subjectId);

    // Get enrolled students for this exam and subject
    // Typically COE attendance tracks all active students in the given session
    $students = StudentMaster::with(['programgroup'])
      ->where('batch', $sessionId)
      ->where('is_active', 1)
      ->whereNotNull('roll_no')
      ->orderBy('roll_no')
      ->get();

    // Check for existing attendance
    $existingAttendance = ExamAttendance::where('exam_id', $examId)
      ->where('subject_id', $subjectId)
      ->get()
      ->keyBy('student_id');

    return view('coe.attendance.take', compact(
      'examId',
      'sessionId',
      'subjectId',
      'examDate',
      'examDetails',
      'sessionDetails',
      'subjectDetails',
      'students',
      'existingAttendance'
    ));
  }

  /**
   * Store attendance records
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'exam_id' => 'required|exists:exams,id',
      'session_id' => 'required',
      'subject_id' => 'required|exists:subjects,id',
      'exam_date' => 'required|date',
      'attendance' => 'required|array',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->with('error', 'Invalid attendance data.');
    }

    DB::beginTransaction();
    try {
      $examId = $request->exam_id;
      $subjectId = $request->subject_id;
      $attendanceData = $request->attendance;
      $remarksData = $request->remarks ?? [];

      // Delete existing attendance for this exam/subject
      ExamAttendance::where('exam_id', $examId)
        ->where('subject_id', $subjectId)
        ->delete();

      // Insert new attendance records
      $recordsToInsert = [];
      foreach ($attendanceData as $studentId => $status) {
        $recordsToInsert[] = [
          'exam_id' => $examId,
          'subject_id' => $subjectId,
          'student_id' => $studentId,
          'status' => $status,
          'remarks' => $remarksData[$studentId] ?? null,
          'marked_by' => auth()->id(),
          'marked_at' => now(),
          'created_at' => now(),
          'updated_at' => now(),
        ];
      }

      ExamAttendance::insert($recordsToInsert);

      DB::commit();

      return redirect()->route('coe.attendance.view')
        ->with('success', 'Attendance marked successfully for ' . count($attendanceData) . ' students.');
    } catch (\Exception $e) {
      DB::rollback();
      return redirect()->back()
        ->with('error', 'Failed to save attendance: ' . $e->getMessage());
    }
  }

  /**
   * View attendance records with filters
   */
  public function view(Request $request)
  {
    // Build query with filters
    $query = ExamAttendance::with(['exam', 'subject', 'student' => function ($q) {
      $q->with(['programgroup', 'batchmaster']);
    }]);

    // Filter by exam date through relationship
    if ($request->filled('attendance_date')) {
      $query->whereHas('exam', function ($q) use ($request) {
        $q->whereDate('exam_date', $request->attendance_date);
      });
    }

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('subject_id')) {
      $query->where('subject_id', $request->subject_id);
    }

    $attendanceRecords = $query->orderBy('created_at', 'desc')
      ->paginate(50);

    // Get filter options
    $exams = Exam::orderBy('exam_date', 'desc')->get();
    $subjects = Subject::latest()->get();

    return view('coe.attendance.view', compact(
      'attendanceRecords',
      'exams',
      'subjects'
    ));
  }

  /**
   * Delete an attendance record
   */
  public function delete($id)
  {
    try {
      $record = ExamAttendance::findOrFail($id);
      $record->delete();

      return redirect()->back()
        ->with('success', 'Attendance record deleted successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to delete record: ' . $e->getMessage());
    }
  }

  /**
   * Show room-wise attendance marking interface
   */
  public function roomWise($examId)
  {
    $exam = Exam::findOrFail($examId);

    // Get all rooms with seating allocations for this exam
    // Join through exam_schedules to get rooms for this exam
    $rooms = \App\Models\RoomMaster::with([
      'seatingAllocations' => function ($query) use ($examId) {
        $query->whereHas('examSchedule', function ($q) use ($examId) {
          $q->where('exam_id', $examId);
        })
          ->with([
            'examStudent.student',
            'examSchedule'
          ])
          ->orderBy('seat_no');
      },
      'block'
    ])
      ->whereHas('seatingAllocations', function ($query) use ($examId) {
        $query->whereHas('examSchedule', function ($q) use ($examId) {
          $q->where('exam_id', $examId);
        });
      })
      ->orderBy('room_name')
      ->get();

    // Transform data for easier view access
    foreach ($rooms as $room) {
      $room->students = $room->seatingAllocations->map(function ($allocation) use ($examId) {
        $allocation->exam_student_id = $allocation->exam_student_id;
        $allocation->seat_number = $allocation->seat_no;
        $allocation->student = $allocation->examStudent->student ?? null;

        // Get dummy number for this student and exam
        $allocation->dummyNumber = DB::table('dummy_numbers')
          ->where('exam_id', $examId)
          ->where('exam_student_id', $allocation->exam_student_id)
          ->first();

        return $allocation;
      });
    }

    // Get existing attendance data
    $attendanceData = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('exam_attendances')) {
      $attendanceData = DB::table('exam_attendances')
        ->where('exam_id', $examId)
        ->get();
    }

    // Count total students
    $totalStudents = $rooms->sum(function ($room) {
      return $room->students->count();
    });

    return view('coe.attendance.room-wise', compact(
      'exam',
      'rooms',
      'attendanceData',
      'totalStudents'
    ));
  }

  /**
   * Update attendance status via AJAX
   */
  public function updateStatus(Request $request)
  {
    try {
      $validated = $request->validate([
        'exam_id' => 'required|integer',
        'exam_student_id' => 'required|integer',
        'status' => 'required|in:present,absent,malpractice',
      ]);

      // Check if attendance record exists
      $attendance = DB::table('exam_attendances')
        ->where('exam_id', $validated['exam_id'])
        ->where('exam_student_id', $validated['exam_student_id'])
        ->first();

      if ($attendance) {
        // Update existing record
        DB::table('exam_attendances')
          ->where('id', $attendance->id)
          ->update([
            'status' => $validated['status'],
            'marked_by' => auth()->id(),
            'marked_at' => now(),
            'updated_at' => now(),
          ]);
      } else {
        // Create new record
        DB::table('exam_attendances')->insert([
          'exam_id' => $validated['exam_id'],
          'exam_student_id' => $validated['exam_student_id'],
          'status' => $validated['status'],
          'marked_by' => auth()->id(),
          'marked_at' => now(),
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Attendance updated successfully',
        'status' => $validated['status']
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }
}

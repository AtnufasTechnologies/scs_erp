<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\Room;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\AttendanceSession;
use App\Services\ExamSystem\AttendanceService;

class AttendanceController extends Controller
{
  // View room-wise student list with filters
  public function index(Request $request)
  {
    $examId = $request->input('exam_id');
    $roomId = $request->input('room_id');
    $session = $request->input('session');

    $query = ExamAttendance::query();
    if ($examId) $query->where('exam_id', $examId);
    if ($roomId) $query->where('room_id', $roomId);
    if ($session) {
      $attendanceSession = AttendanceSession::where([
        'exam_id' => $examId,
        'room_id' => $roomId,
        'session' => $session,
      ])->first();
      if ($attendanceSession) {
        // Optionally filter by session date/time if needed
      }
    }
    $students = $query->with(['student'])->orderBy('seat_no')->get();
    return response()->json($students);
  }

  // Mark attendance (present/absent/malpractice)
  public function mark(Request $request, AttendanceService $service)
  {
    $validated = $request->validate([
      'student_id' => 'required|integer',
      'exam_id' => 'required|integer',
      'room_id' => 'required|integer',
      'session' => 'required|string',
      'status' => 'required|in:present,absent,malpractice',
      'remarks' => 'nullable|string',
    ]);
    $facultyId = $request->user()->id;
    if ($validated['status'] === 'present') {
      $attendance = $service->markPresent($validated['student_id'], $validated['exam_id'], $facultyId, $validated['room_id'], $validated['session']);
    } elseif ($validated['status'] === 'malpractice') {
      $attendance = $service->markMalpractice($validated['student_id'], $validated['exam_id'], $facultyId, $validated['room_id'], $validated['session']);
      $attendance->remarks = $validated['remarks'];
      $attendance->save();
    } else {
      $attendance = $service->markAbsent($validated['student_id'], $validated['exam_id'], $facultyId, $validated['room_id'], $validated['session']);
    }
    return response()->json(['success' => true, 'attendance' => $attendance]);
  }
}

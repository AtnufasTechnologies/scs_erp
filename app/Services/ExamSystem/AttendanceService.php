<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\AttendanceLog;
use App\Models\ExamSystem\AttendanceSession;
use App\Models\ExamSystem\MalpracticeCase;
use App\Models\ExamSystem\Student;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceService
{
  /**
   * Mark student as present for an exam.
   */
  public function markPresent($studentId, $examId, $facultyId, $roomId, $session)
  {
    return $this->markStatus($studentId, $examId, $facultyId, $roomId, $session, 'present');
  }

  /**
   * Mark student as absent for an exam.
   */
  public function markAbsent($studentId, $examId, $facultyId, $roomId, $session)
  {
    return $this->markStatus($studentId, $examId, $facultyId, $roomId, $session, 'absent');
  }

  /**
   * Mark student as malpractice for an exam.
   */
  public function markMalpractice($studentId, $examId, $facultyId, $roomId, $session)
  {
    return $this->markStatus($studentId, $examId, $facultyId, $roomId, $session, 'malpractice');
  }

  /**
   * Core logic for marking attendance with rules and logging.
   */
  protected function markStatus($studentId, $examId, $facultyId, $roomId, $session, $status)
  {
    return DB::transaction(function () use ($studentId, $examId, $facultyId, $roomId, $session, $status) {
      // Find the attendance session (must be open and assigned to this invigilator)
      $attendanceSession = AttendanceSession::where([
        'exam_id' => $examId,
        'room_id' => $roomId,
        'faculty_id' => $facultyId,
        'session' => $session,
        'status' => 'open',
      ])->first();
      if (!$attendanceSession) {
        throw new \Exception('You are not authorized to mark attendance for this session/room, or session is closed.');
      }

      // Optional: GPS/IP/time window validation hooks
      // $this->validateGpsOrIp($facultyId, $roomId);
      // $this->validateTimeWindow($attendanceSession);

      // Find the attendance record
      $attendance = ExamAttendance::where([
        'exam_id' => $examId,
        'student_id' => $studentId,
        'room_id' => $roomId,
      ])->first();
      if (!$attendance) {
        throw new \Exception('Attendance record not found for student in this room.');
      }

      // Prevent marking if attendance is locked (session closed)
      if ($attendanceSession->status !== 'open') {
        throw new \Exception('Attendance session is locked.');
      }

      $attendance->status = $status;
      $attendance->marked_by = $facultyId;
      $attendance->marked_at = Carbon::now();
      $attendance->save();

      // If marked as malpractice, flag student, block result, create case, notify admin
      if ($status === 'malpractice') {
        // Flag student (set status or add flag column if needed)
        $student = Student::find($studentId);
        if ($student) {
          $student->status = 'malpractice'; // or add a flag column if preferred
          $student->save();
        }
        // Block result processing (handled in result logic by checking for malpractice case)
        MalpracticeCase::create([
          'exam_id' => $examId,
          'student_id' => $studentId,
          'subject_id' => $attendance->subject_id,
          'room_id' => $roomId,
          'remarks' => $attendance->remarks,
          'status' => 'pending',
          'reported_by' => $facultyId,
          'reported_at' => Carbon::now(),
        ]);
        // Notify admin (stub, implement notification as needed)
        // Notification::route('mail', 'admin@example.com')->notify(new \App\Notifications\MalpracticeReported($attendance));
      }

      // Log the action
      AttendanceLog::create([
        'attendance_id' => $attendance->id,
        'action' => 'marked',
        'performed_by' => $facultyId,
        'timestamp' => Carbon::now(),
      ]);

      // If all students in this room are marked, lock the session
      $total = ExamAttendance::where([
        'exam_id' => $examId,
        'room_id' => $roomId,
      ])->count();
      $marked = ExamAttendance::where([
        'exam_id' => $examId,
        'room_id' => $roomId,
      ])->whereNotNull('marked_at')->count();
      if ($total > 0 && $marked === $total) {
        $attendanceSession->status = 'closed';
        $attendanceSession->save();
      }

      return $attendance;
    });
  }

  // Optional: Implement GPS/IP/time window validation as needed
  // protected function validateGpsOrIp($facultyId, $roomId) { /* ... */ }
  // protected function validateTimeWindow($attendanceSession) { /* ... */ }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendanceQrMaster;
use App\Models\ExtraClassAttendance;
use App\Models\StudentAttendance;
use App\Models\StudentMaster;
use App\Models\SubjectHasRoutine;
use App\Services\AttendanceEligibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentAttendanceScanController extends Controller
{
  private AttendanceEligibilityService $attendanceEligibilityService;

  public function __construct(AttendanceEligibilityService $attendanceEligibilityService)
  {
    $this->attendanceEligibilityService = $attendanceEligibilityService;
  }

  private function jsonResponse(bool $success, string $message, int $status = 200, array $data = [])
  {
    return response()->json([
      'success' => $success,
      'message' => $message,
      'data' => $data,
    ], $status);
  }

  private function isEligibleStudent(SubjectHasRoutine $routine, StudentMaster $student, int $courseId, int $semesterId, int $batchId): bool
  {
    $campusId = (int) ($routine->syllabus->subject->campus_id ?? 0);
    return $this->attendanceEligibilityService->isStudentEligible(
      $routine,
      (int) $student->id,
      $courseId,
      $semesterId,
      $batchId,
      $campusId,
      (int) ($routine->syllabus->subject_id ?? 0)
    );
  }

  public function mark(Request $request)
  {
    $ignoreKeys = ['student_id', 'studentId', 'student'];
    $isSignatureValid = false;

    if (method_exists($request, 'hasValidSignatureWhileIgnoring')) {
      $isSignatureValid = $request->hasValidSignatureWhileIgnoring($ignoreKeys);
    } else {
      // Backward-compatible behavior for older Laravel versions:
      // remove runtime student keys from query before signature validation.
      $originalQuery = $request->query();
      foreach ($ignoreKeys as $key) {
        $request->query->remove($key);
      }

      $isSignatureValid = $request->hasValidSignature();

      $request->query->replace($originalQuery);
    }

    if (!$isSignatureValid) {
      return $this->jsonResponse(false, 'Invalid or expired QR code.', 401);
    }

    // Strict payload mode for body: accept only student_id (or supported aliases).
    $bodyInput = $request->isJson()
      ? (array) $request->json()->all()
      : (array) $request->request->all();

    $allowedBodyKeys = ['student_id', 'studentId', 'student'];
    $extraBodyKeys = array_values(array_diff(array_keys($bodyInput), $allowedBodyKeys));
    if (!empty($extraBodyKeys)) {
      return $this->jsonResponse(false, 'Only student_id is allowed in request payload.', 422, [
        'extra_fields' => $extraBodyKeys,
      ]);
    }

    $hasStudentInQuery = $request->query('student_id') !== null
      || $request->query('studentId') !== null
      || $request->query('student') !== null;
    $hasStudentInHeader = $request->header('X-Student-Id') !== null || $request->header('x-student-id') !== null;

    if ($hasStudentInQuery || $hasStudentInHeader) {
      return $this->jsonResponse(false, 'Pass student_id only in request body.', 422);
    }

    $rawStudentId = $bodyInput['student_id'] ?? $bodyInput['studentId'] ?? $bodyInput['student'];

    if ($rawStudentId === null || $rawStudentId === '') {
      return $this->jsonResponse(false, 'student_id is required in request body.', 422, [
        'received_body_keys' => array_keys($bodyInput),
      ]);
    }

    $validated = validator([
      'student_id' => $rawStudentId,
    ], [
      'student_id' => 'required|integer|min:1',
    ])->validate();

    $studentId = (int) $validated['student_id'];

    $scanCode = (string) $request->query('q', '');
    if ($scanCode === '') {
      return $this->jsonResponse(false, 'Invalid QR payload. Missing scan token.', 422);
    }

    $qrRecord = AttendanceQrMaster::query()
      ->where('code', $scanCode)
      ->where('status', 1)
      ->first();

    if (!$qrRecord) {
      return $this->jsonResponse(false, 'QR session is invalid or inactive.', 422);
    }

    $payload = [
      'routine_id' => (int) ($qrRecord->routine_id ?? 0),
      'syllabus_id' => 0,
      'course_id' => (int) ($qrRecord->course_id ?? 0),
      'hour_id' => (int) ($qrRecord->hour_id ?? 0),
      'semester_id' => (int) ($qrRecord->semester_id ?? 0),
      'batch_id' => (int) ($qrRecord->batch_id ?? 0),
      'faculty_id' => (int) ($qrRecord->faculty_id ?? 0),
      'attendance_date' => (string) ($qrRecord->attendance_date ? $qrRecord->attendance_date->format('Y-m-d') : ''),
      'attendance_type' => (string) ($qrRecord->attendance_type ?? 'regular'),
    ];

    if (
      $payload['routine_id'] <= 0
      || $payload['course_id'] <= 0
      || $payload['hour_id'] <= 0
      || $payload['semester_id'] <= 0
      || $payload['batch_id'] <= 0
      || $payload['faculty_id'] <= 0
      || $payload['attendance_date'] === ''
    ) {
      return $this->jsonResponse(false, 'QR session data is incomplete.', 422);
    }

    try {
      $attendanceDate = Carbon::createFromFormat('Y-m-d', $payload['attendance_date']);
    } catch (\Throwable $e) {
      return $this->jsonResponse(false, 'Invalid attendance date in QR payload.', 422);
    }

    if ($attendanceDate->isSunday()) {
      return $this->jsonResponse(false, 'Attendance cannot be marked on Sunday.', 422);
    }

    $routine = SubjectHasRoutine::with([
      'syllabus:id,subject_id,course_id,batch_id,semester_id',
      'syllabus.subject:id,campus_id',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.courseLink.courseMaster:id,course_title,course_code',
      'teachingAssignment:id,allocation_group,delivery_type',
      'teachingAllocation:id,allocation_group,delivery_type',
    ])
      ->where('id', $payload['routine_id'])
      ->where('faculty_id', $payload['faculty_id'])
      ->first();

    if (!$routine || !$routine->syllabus) {
      return $this->jsonResponse(false, 'Invalid routine/faculty mapping in QR code.', 422);
    }

    if (
      (int) $routine->syllabus->course_id !== $payload['course_id']
      || (int) ($routine->syllabus->semester_id ?? 0) !== $payload['semester_id']
      || (int) ($routine->syllabus->batch_id ?? 0) !== $payload['batch_id']
    ) {
      return $this->jsonResponse(false, 'QR metadata does not match class details.', 422);
    }

    $student = StudentMaster::where('id', $studentId)
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->first();

    if (!$student) {
      return $this->jsonResponse(false, 'Student profile not found.', 404);
    }

    $isEligible = $this->isEligibleStudent(
      $routine,
      $student,
      (int) $payload['course_id'],
      (int) $payload['semester_id'],
      (int) $payload['batch_id']
    );

    if (!$isEligible) {
      return $this->jsonResponse(false, 'You are not eligible for this attendance slot.', 403);
    }

    $batchName = (string) ($routine->syllabus->batchmaster->batch_name ?? 'N/A');

    if ($payload['attendance_type'] === 'remedial') {
      $existing = ExtraClassAttendance::where([
        'routine_id' => $payload['routine_id'],
        'student_id' => $studentId,
        'attendance_date' => $payload['attendance_date'],
        'course_id' => $payload['course_id'],
        'hour_id' => $payload['hour_id'],
        'faculty_id' => $payload['faculty_id'],
      ])->first();

      if ($existing) {
        if ((string) $existing->status === 'present') {
          return $this->jsonResponse(true, 'Attendance already marked as PRESENT.', 200);
        }

        return $this->jsonResponse(false, 'Attendance already recorded and cannot be changed via QR.', 409);
      }

      ExtraClassAttendance::create([
        'routine_id' => $payload['routine_id'],
        'faculty_id' => $payload['faculty_id'],
        'student_id' => $studentId,
        'course_id' => $payload['course_id'],
        'semester_id' => $payload['semester_id'],
        'hour_id' => $payload['hour_id'],
        'batch' => $batchName,
        'attendance_date' => $payload['attendance_date'],
        'status' => 'present',
        'attendance_method' => 'qr',
      ]);
    } else {
      $existing = StudentAttendance::where([
        'routine_id' => $payload['routine_id'],
        'student_id' => $studentId,
        'attendance_date' => $payload['attendance_date'],
        'course_id' => $payload['course_id'],
        'hour_id' => $payload['hour_id'],
        'faculty_id' => $payload['faculty_id'],
      ])->first();

      if ($existing) {
        if ((string) $existing->status === 'present') {
          return $this->jsonResponse(true, 'Attendance already marked as PRESENT.', 200);
        }

        return $this->jsonResponse(false, 'Attendance already recorded and cannot be changed via QR.', 409);
      }

      StudentAttendance::create([
        'routine_id' => $payload['routine_id'],
        'course_id' => $payload['course_id'],
        'faculty_id' => $payload['faculty_id'],
        'student_id' => $studentId,
        'attendance_date' => $payload['attendance_date'],
        'hour_id' => $payload['hour_id'],
        'semester_id' => $payload['semester_id'],
        'batch' => $batchName,
        'qr_url' => $request->fullUrl(),
        'attendance_method' => 'qr',
        'status' => 'present',
      ]);
    }

    return $this->jsonResponse(true, 'Attendance marked as PRESENT.', 200);
  }
}

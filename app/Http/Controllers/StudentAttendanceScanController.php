<?php

namespace App\Http\Controllers;

use App\Models\AttendanceQrMaster;
use App\Models\ExtraClassAttendance;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\StudentAttendance;
use App\Models\StudentMaster;
use App\Models\SubjectHasRoutine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentAttendanceScanController extends Controller
{
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
    if ($campusId <= 0) {
      return false;
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));
    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($routine, $batchId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', (int) ($routine->syllabus->subject_id ?? 0))
          ->where('shp.batch_id', '=', $batchId);
      })
      ->select('sm.id', 'shp.id as program_combo_id')
      ->where('sm.id', (int) $student->id)
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $batchId)
      ->where('sci.course_id', $courseId)
      ->where('sci.semester', $semesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct()
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift]);

    if ($routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    $students = $baseQuery->get();
    if ($students->isEmpty()) {
      return false;
    }

    // Apply specialization-aware filtering to match manual attendance listing.
    if (Schema::hasTable('student_specializations')) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $comboIds = $students->pluck('program_combo_id')->filter(fn($v) => (int) $v > 0)->map(fn($v) => (int) $v)->unique()->values();

      if ($comboIds->isNotEmpty() && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        $curriculumQuery = DB::table($curriculumTable)
          ->whereIn('program_combo_refid', $comboIds->all())
          ->where('course_id', (int) $courseId)
          ->where('semester', (int) $semesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $curriculumQuery->where('batch', (int) $batchId);
        }

        $curriculumRows = $curriculumQuery->get([
          'program_combo_refid',
          'specialization_mode',
          'specialization_master_id',
          'specialization_master_ids',
        ]);

        $requiredSpecIdsByCombo = [];
        foreach ($curriculumRows as $row) {
          $comboId = (int) ($row->program_combo_refid ?? 0);
          if ($comboId <= 0) {
            continue;
          }

          $mode = strtoupper(trim((string) ($row->specialization_mode ?? 'COMMON')));
          $isCommon = in_array($mode, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAM_COMMON', 'ALL'], true);
          if ($isCommon) {
            continue;
          }

          $specIds = [];
          $singleSpecId = (int) ($row->specialization_master_id ?? 0);
          if ($singleSpecId > 0) {
            $specIds[] = $singleSpecId;
          }

          $rawIds = $row->specialization_master_ids;
          if (is_string($rawIds) && trim($rawIds) !== '') {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
              $rawIds = $decoded;
            }
          }

          if (is_array($rawIds)) {
            foreach ($rawIds as $rawId) {
              $sid = (int) $rawId;
              if ($sid > 0) {
                $specIds[] = $sid;
              }
            }
          }

          if (!empty($specIds)) {
            $existing = $requiredSpecIdsByCombo[$comboId] ?? [];
            $requiredSpecIdsByCombo[$comboId] = array_values(array_unique(array_merge($existing, $specIds)));
          }
        }

        if (!empty($requiredSpecIdsByCombo)) {
          $studentIds = $students->pluck('id')->map(fn($v) => (int) $v)->unique()->values();

          $studentSpecQuery = DB::table('student_specializations')
            ->whereIn('student_id', $studentIds->all())
            ->whereIn('subject_has_student_program_id', array_keys($requiredSpecIdsByCombo));

          if (Schema::hasColumn('student_specializations', 'is_active')) {
            $studentSpecQuery->where('is_active', 1);
          }

          if (Schema::hasColumn('student_specializations', 'deleted_at')) {
            $studentSpecQuery->whereNull('deleted_at');
          }

          if (Schema::hasColumn('student_specializations', 'semester_id')) {
            $studentSpecQuery->where(function ($query) use ($semesterId) {
              $query->whereNull('semester_id')->orWhere('semester_id', $semesterId);
            });
          }

          $studentSpecRows = $studentSpecQuery
            ->select('student_id', 'subject_has_student_program_id', 'specialization_id')
            ->orderByDesc('id')
            ->get();

          $studentSpecLookup = [];
          foreach ($studentSpecRows as $specRow) {
            $sId = (int) ($specRow->student_id ?? 0);
            $comboId = (int) ($specRow->subject_has_student_program_id ?? 0);
            $specId = (int) ($specRow->specialization_id ?? 0);

            if ($sId <= 0 || $comboId <= 0 || $specId <= 0) {
              continue;
            }

            $studentSpecLookup[$sId] = $studentSpecLookup[$sId] ?? [];
            $studentSpecLookup[$sId][$comboId] = $studentSpecLookup[$sId][$comboId] ?? [];
            $studentSpecLookup[$sId][$comboId][] = $specId;
          }

          $students = $students->filter(function ($row) use ($requiredSpecIdsByCombo, $studentSpecLookup) {
            $comboId = (int) ($row->program_combo_id ?? 0);
            if ($comboId <= 0 || !isset($requiredSpecIdsByCombo[$comboId])) {
              return true;
            }

            $required = $requiredSpecIdsByCombo[$comboId] ?? [];
            if (empty($required)) {
              return true;
            }

            $sId = (int) ($row->id ?? 0);
            $studentSpecs = $studentSpecLookup[$sId][$comboId] ?? [];
            if (empty($studentSpecs)) {
              return false;
            }

            return !empty(array_intersect($required, array_map(fn($v) => (int) $v, $studentSpecs)));
          })->values();
        }
      }
    }

    return $students->pluck('id')->map(fn($v) => (int) $v)->contains((int) $student->id);
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
      'teachingAssignment:id,allocation_group',
      'teachingAllocation:id,allocation_group',
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

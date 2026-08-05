<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\AttendanceQrMaster;
use App\Models\ExtraClassAttendance;
use App\Models\HourMaster;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\ProgramCourseMaster;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\ShiftMaster;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
  private function generateUniqueQrCode(): string
  {
    do {
      $code = Str::upper(Str::random(12));
      $exists = AttendanceQrMaster::where('code', $code)->exists();
    } while ($exists);

    return $code;
  }

  private function getCurrentFacultyId(): int
  {
    $userId = Auth::user()->id;
    return (int) SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
  }

  private function applyFacultyRoutineAccess($query, int $facultyId)
  {
    return $query->where(function ($nested) use ($facultyId) {
      $nested->where('faculty_id', $facultyId)
        ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
          $assignmentQuery->where('faculty_id', $facultyId)
            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
              $coFacultyQuery->where('faculties.id', $facultyId);
            });
        });
    });
  }

  private function getAccessibleRoutineIds(int $facultyId)
  {
    return $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();
  }

  private function getRoutineAllocationGroupId(?SubjectHasRoutine $routine): int
  {
    if (!$routine) {
      return 0;
    }

    return (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );
  }

  private function getShiftTeachingHours(string $shiftSlug)
  {
    $shiftSlug = strtolower(trim($shiftSlug));
    if ($shiftSlug === '') {
      return collect();
    }

    $shiftId = (int) ShiftMaster::where('slug', $shiftSlug)->value('id');
    if ($shiftId <= 0) {
      return collect();
    }

    $hourTable = (new HourMaster())->getTable();

    $hourQuery = HourMaster::query()
      ->where('shift_id', $shiftId);

    if (Schema::hasColumn($hourTable, 'status')) {
      $hourQuery->where('status', 1);
    }

    if (Schema::hasColumn($hourTable, 'is_teaching')) {
      $hourQuery->where('is_teaching', 1);
    }

    if (Schema::hasColumn($hourTable, 'hour_no')) {
      $hourQuery->orderBy('hour_no');
    } else {
      $hourQuery->orderBy('id');
    }

    $selectColumns = ['id'];
    foreach (['hour_no', 'name', 'title', 'start_time', 'end_time'] as $column) {
      if (Schema::hasColumn($hourTable, $column)) {
        $selectColumns[] = $column;
      }
    }

    return $hourQuery
      ->get($selectColumns)
      ->map(function ($hour) {
        $label = (string) ($hour->title ?? $hour->name ?? '');
        if ($label === '') {
          $label = 'Hour ' . (int) ($hour->hour_no ?? $hour->id ?? 0);
        }

        if (!empty($hour->start_time) && !empty($hour->end_time)) {
          $label .= ' (' . $hour->start_time . ' - ' . $hour->end_time . ')';
        }

        return (object) [
          'id' => (int) $hour->id,
          'label' => $label,
        ];
      })
      ->values();
  }

  public function getHoursByShift(Request $request)
  {
    try {
      $recId = (int) $request->get('rec_id', 0);
      $shiftSlug = '';

      if ($recId > 0) {
        $routineShift = SubjectHasRoutine::query()
          ->where('id', $recId)
          ->value('shift');

        $shiftSlug = strtolower(trim((string) ($routineShift ?? '')));
      }

      if ($shiftSlug === '') {
        $shiftSlug = strtolower(trim((string) $request->get('shift', '')));
      }

      if ($shiftSlug === '') {
        return response()->json([
          'success' => false,
          'message' => 'Shift is required.'
        ], 422);
      }

      $hours = $this->getShiftTeachingHours($shiftSlug);
      if ($hours->isEmpty()) {
        return response()->json([
          'success' => false,
          'message' => 'No teaching hours available for selected shift.'
        ], 422);
      }

      $hoursPayload = $hours->map(fn($hour) => [
        'id' => (int) $hour->id,
        'label' => (string) $hour->label,
      ])->values();

      return response()->json([
        'success' => true,
        'data' => $hoursPayload,
      ]);
    } catch (\Throwable $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch hours: ' . $e->getMessage(),
      ], 500);
    }
  }

  public function generateStudentAttendanceQr(Request $request)
  {
    $validated = $request->validate([
      'routine_id' => 'required|integer|exists:subject_has_routines,id',
      'syllabus_id' => 'required|integer|exists:subject_has_syllabi,id',
      'course_id' => 'required|integer',
      'hour_id' => 'required|integer|exists:hour_masters,id',
      'semester_id' => 'required|integer',
      'batch_id' => 'required|integer',
      'attendance_date' => 'required|date_format:Y-m-d',
      'attendance_type' => 'nullable|in:regular,remedial',
      'expiry_minutes' => 'nullable|integer|min:1|max:60',
    ]);

    $attendanceDate = Carbon::createFromFormat('Y-m-d', $validated['attendance_date']);
    if ($attendanceDate->isSunday()) {
      return response()->json([
        'success' => false,
        'message' => 'Sunday is a holiday. Attendance QR cannot be generated.',
      ], 422);
    }

    $facultyId = $this->getCurrentFacultyId();
    if ($facultyId <= 0) {
      return response()->json([
        'success' => false,
        'message' => 'Faculty profile not found for this account.',
      ], 422);
    }

    $routineQuery = SubjectHasRoutine::with([
      'syllabus:id,subject_id,course_id,batch_id,semester_id',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.courseLink.courseMaster:id,course_title,course_code',
    ])
      ->where('id', (int) $validated['routine_id']);

    $routine = $this->applyFacultyRoutineAccess($routineQuery, $facultyId)->first();

    if (!$routine || !$routine->syllabus) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid routine selected for this faculty.',
      ], 422);
    }

    if ((int) $routine->syllabus_id !== (int) $validated['syllabus_id']) {
      return response()->json([
        'success' => false,
        'message' => 'Syllabus mismatch for selected routine.',
      ], 422);
    }

    if ((int) $routine->syllabus->course_id !== (int) $validated['course_id']) {
      return response()->json([
        'success' => false,
        'message' => 'Course mismatch for selected routine.',
      ], 422);
    }

    $effectiveSemesterId = (int) ($routine->syllabus->semester_id ?? 0);
    $effectiveBatchId = (int) ($routine->syllabus->batch_id ?? 0);

    if ($effectiveSemesterId !== (int) $validated['semester_id'] || $effectiveBatchId !== (int) $validated['batch_id']) {
      return response()->json([
        'success' => false,
        'message' => 'Batch or semester mismatch for selected routine.',
      ], 422);
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));
    $routineAllocationGroup = $this->getRoutineAllocationGroupId($routine);
    $availableHourIds = $this->getShiftTeachingHours($routineShift)->pluck('id')->map(fn($v) => (int) $v)->all();
    if (!in_array((int) $validated['hour_id'], $availableHourIds, true)) {
      return response()->json([
        'success' => false,
        'message' => 'Selected hour is not valid for this shift.',
      ], 422);
    }

    if (Schema::hasTable('attendance_qr_masters')) {
      $existingCandidates = AttendanceQrMaster::query()
        ->where('routine_id', (int) $validated['routine_id'])
        ->where('course_id', (int) $validated['course_id'])
        ->where('semester_id', $effectiveSemesterId)
        ->where('batch_id', $effectiveBatchId)
        ->where('hour_id', (int) $validated['hour_id'])
        ->whereDate('attendance_date', $validated['attendance_date'])
        ->with([
          'routine',
          'routine.teachingAssignment:id,allocation_group',
          'routine.teachingAllocation:id,allocation_group',
        ])
        ->get();

      $duplicateSlot = $existingCandidates->first(function ($candidate) use ($routineShift, $routineAllocationGroup) {
        $candidateRoutine = $candidate->routine;
        if (!$candidateRoutine) {
          return false;
        }

        $candidateShift = strtolower(trim((string) ($candidateRoutine->shift ?? 'common')));
        $candidateGroup = $this->getRoutineAllocationGroupId($candidateRoutine);

        return $candidateShift === $routineShift && $candidateGroup === $routineAllocationGroup;
      });

      if ($duplicateSlot) {
        return response()->json([
          'success' => false,
          'message' => 'QR already exists for the same course/batch/semester/hour/day/shift/allocation group. Delete the existing QR to regenerate.',
          'data' => [
            'existing_record_id' => (int) $duplicateSlot->id,
          ],
        ], 422);
      }
    }

    $syllabusFacultyId = 0;
    if (Schema::hasTable('syllabus_has_faculties')) {
      $syllabusFacultyId = (int) SyllabusHasFaculty::where('faculty_id', $facultyId)
        ->where('syllabus_id', (int) $validated['syllabus_id'])
        ->value('id');
    } elseif (Schema::hasTable('subject_faculty_masters')) {
      // Fallback for deployments where syllabus_has_faculties is not present.
      $subjectId = (int) ($routine->syllabus->subject_id ?? 0);
      if ($subjectId > 0) {
        $syllabusFacultyId = (int) SubjectFacultyMaster::where('faculty_id', $facultyId)
          ->where('subject_id', $subjectId)
          ->value('id');
      }
    }

    if (!Schema::hasTable('attendance_qr_masters')) {
      return response()->json([
        'success' => false,
        'message' => 'QR storage table is missing. Please run migrations first.',
      ], 500);
    }

    $expiryMinutes = (int) ($validated['expiry_minutes'] ?? 5);
    if ($expiryMinutes <= 0) {
      $expiryMinutes = 5;
    }

    $expiresAt = now()->addMinutes($expiryMinutes);
    $attendanceType = $validated['attendance_type'] ?? 'regular';

    $qrMaster = null;
    try {
      $recordPayload = [
        'routine_id' => (int) $validated['routine_id'],
        'faculty_id' => $facultyId,
        'course_id' => (int) $validated['course_id'],
        'semester_id' => $effectiveSemesterId,
        'batch_id' => $effectiveBatchId,
        'hour_id' => (int) $validated['hour_id'],
        'attendance_date' => $validated['attendance_date'],
        'attendance_type' => $attendanceType,
        'code' => $this->generateUniqueQrCode(),
        'expires_at' => $expiresAt,
        'status' => 1,
      ];

      if (Schema::hasColumn('attendance_qr_masters', 'syllabus_faculty_id') && $syllabusFacultyId > 0) {
        $recordPayload['syllabus_faculty_id'] = $syllabusFacultyId;
      }

      $qrMaster = AttendanceQrMaster::create($recordPayload);
      $scanUrl = URL::temporarySignedRoute('student.attendance.scan', $expiresAt, [
        'q' => $qrMaster->code,
      ]);

      $qrMaster->scan_url = $scanUrl;
      $qrMaster->save();
    } catch (\Throwable $e) {
      Log::warning('QR record could not be saved', [
        'message' => $e->getMessage(),
        'faculty_id' => $facultyId,
        'routine_id' => (int) $validated['routine_id'],
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Failed to generate QR record.',
      ], 500);
    }

    $courseTitle = (string) ($routine->syllabus->courseLink->courseMaster->course_title ?? 'N/A');
    $courseCode = (string) ($routine->syllabus->courseLink->courseMaster->course_code ?? 'N/A');
    $batchName = (string) ($routine->syllabus->batchmaster->batch_name ?? 'N/A');

    return response()->json([
      'success' => true,
      'message' => 'Student attendance QR generated successfully.',
      'data' => [
        'record_id' => (int) ($qrMaster->id ?? 0),
        'scan_url' => (string) ($qrMaster->scan_url ?? ''),
        'expires_at' => $expiresAt->format('d M Y h:i A'),
        'expires_at_iso' => $expiresAt->toIso8601String(),
        'expiry_minutes' => $expiryMinutes,
        'course_label' => $courseTitle . ' (' . $courseCode . ')',
        'batch_label' => $batchName,
        'attendance_type' => $attendanceType,
      ],
    ]);
  }

  private function getEligibleStudentsForRoutine(
    SubjectHasSyllabus $record,
    SubjectHasRoutine $routine,
    int $effectiveBatchId,
    int $effectiveSemesterId,
    int $courseId
  ) {
    $campusId = (int) ($record->subject->campus_id ?? 0);
    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));
    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($record, $effectiveBatchId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', (int) ($record->subject_id ?? 0))
          ->where('shp.batch_id', '=', $effectiveBatchId);
      })
      ->select(
        'sm.id',
        'shp.id as program_combo_id'
      )
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $effectiveBatchId)
      ->where('sci.course_id', $courseId)
      ->where('sci.semester', $effectiveSemesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    if ($routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    $students = (clone $baseQuery)
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->get();

    if ($students->isNotEmpty() && Schema::hasTable('student_specializations')) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $comboIds = $students->pluck('program_combo_id')->filter(fn($v) => (int) $v > 0)->map(fn($v) => (int) $v)->unique()->values();

      if ($comboIds->isNotEmpty() && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        $curriculumQuery = DB::table($curriculumTable)
          ->whereIn('program_combo_refid', $comboIds->all())
          ->where('course_id', (int) $courseId)
          ->where('semester', (int) $effectiveSemesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $curriculumQuery->where('batch', (int) $effectiveBatchId);
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
            $studentSpecQuery->where(function ($query) use ($effectiveSemesterId) {
              $query->whereNull('semester_id')->orWhere('semester_id', $effectiveSemesterId);
            });
          }

          $studentSpecRows = $studentSpecQuery
            ->select('student_id', 'subject_has_student_program_id', 'specialization_id')
            ->orderByDesc('id')
            ->get();

          $studentSpecLookup = [];
          foreach ($studentSpecRows as $specRow) {
            $studentId = (int) ($specRow->student_id ?? 0);
            $comboId = (int) ($specRow->subject_has_student_program_id ?? 0);
            $specId = (int) ($specRow->specialization_id ?? 0);

            if ($studentId <= 0 || $comboId <= 0 || $specId <= 0) {
              continue;
            }

            if (!isset($studentSpecLookup[$studentId])) {
              $studentSpecLookup[$studentId] = [];
            }
            if (!isset($studentSpecLookup[$studentId][$comboId])) {
              $studentSpecLookup[$studentId][$comboId] = [];
            }

            $studentSpecLookup[$studentId][$comboId][] = $specId;
          }

          $students = $students->filter(function ($student) use ($requiredSpecIdsByCombo, $studentSpecLookup) {
            $comboId = (int) ($student->program_combo_id ?? 0);

            if ($comboId <= 0 || !isset($requiredSpecIdsByCombo[$comboId])) {
              return true;
            }

            $required = $requiredSpecIdsByCombo[$comboId] ?? [];
            if (empty($required)) {
              return true;
            }

            $studentId = (int) ($student->id ?? 0);
            $studentSpecs = $studentSpecLookup[$studentId][$comboId] ?? [];

            if (empty($studentSpecs)) {
              return false;
            }

            return !empty(array_intersect($required, array_map(fn($v) => (int) $v, $studentSpecs)));
          })->values();
        }
      }
    }

    return $students;
  }

  public function finalizeQrAttendance(Request $request)
  {
    $validated = $request->validate([
      'record_id' => 'required|integer|exists:attendance_qr_masters,id',
    ]);

    $facultyId = $this->getCurrentFacultyId();
    if ($facultyId <= 0) {
      return response()->json([
        'success' => false,
        'message' => 'Faculty profile not found for this account.',
      ], 422);
    }

    $qrRecord = AttendanceQrMaster::with([
      'routine',
      'routine.teachingAssignment:id,allocation_group',
      'routine.teachingAllocation:id,allocation_group',
      'routine.syllabus:id,subject_id,course_id,batch_id,semester_id',
      'routine.syllabus.subject:id,campus_id',
      'routine.syllabus.batchmaster:id,batch_name',
    ])
      ->where('id', (int) $validated['record_id'])
      ->first();

    if ($qrRecord && !$this->getAccessibleRoutineIds($facultyId)->contains((int) ($qrRecord->routine_id ?? 0))) {
      $qrRecord = null;
    }

    if (!$qrRecord || !$qrRecord->routine || !$qrRecord->routine->syllabus) {
      return response()->json([
        'success' => false,
        'message' => 'QR record or routine data not found.',
      ], 404);
    }

    if (!empty($qrRecord->status) && (int) $qrRecord->status === 2) {
      return response()->json([
        'success' => true,
        'message' => 'QR session already finalized.',
      ]);
    }

    if (!empty($qrRecord->expires_at) && now()->lt($qrRecord->expires_at)) {
      return response()->json([
        'success' => false,
        'message' => 'QR is still active and not yet expired.',
      ], 422);
    }

    $routine = $qrRecord->routine;
    $record = $routine->syllabus;

    $effectiveBatchId = (int) ($qrRecord->batch_id ?: ($record->batch_id ?? 0));
    $effectiveSemesterId = (int) ($qrRecord->semester_id ?: ($record->semester_id ?? 0));
    $courseId = (int) ($qrRecord->course_id ?: ($record->course_id ?? 0));

    if ($effectiveBatchId <= 0 || $effectiveSemesterId <= 0 || $courseId <= 0) {
      return response()->json([
        'success' => false,
        'message' => 'QR context is incomplete for finalization.',
      ], 422);
    }

    $eligibleStudents = $this->getEligibleStudentsForRoutine(
      $record,
      $routine,
      $effectiveBatchId,
      $effectiveSemesterId,
      $courseId
    );

    $eligibleStudentIds = $eligibleStudents->pluck('id')->map(fn($v) => (int) $v)->unique()->values();
    $totalStudents = $eligibleStudentIds->count();
    $presentStudentIds = collect();

    if (($qrRecord->attendance_type ?? 'regular') === 'remedial') {
      $presentStudentIds = ExtraClassAttendance::query()
        ->where('routine_id', (int) $qrRecord->routine_id)
        ->where('attendance_date', $qrRecord->attendance_date)
        ->where('hour_id', (int) $qrRecord->hour_id)
        ->where('course_id', $courseId)
        ->where('faculty_id', $facultyId)
        ->where('status', 'present')
        ->pluck('student_id')
        ->map(fn($v) => (int) $v)
        ->unique()
        ->values();

      if ($presentStudentIds->count() === 0) {
        $qrRecord->status = 3;
        $qrRecord->save();

        return response()->json([
          'success' => true,
          'message' => 'No student marked present. Treated as fake test; no attendance was recorded.',
          'data' => [
            'total_students' => $totalStudents,
            'present_students' => 0,
            'absent_marked' => 0,
          ],
        ]);
      }

      $batchName = (string) ($record->batchmaster->batch_name ?? $effectiveBatchId);
      $absentIds = $eligibleStudentIds->diff($presentStudentIds)->values();

      foreach ($absentIds as $studentId) {
        ExtraClassAttendance::updateOrCreate(
          [
            'routine_id' => (int) $qrRecord->routine_id,
            'student_id' => (int) $studentId,
            'attendance_date' => $qrRecord->attendance_date,
            'course_id' => $courseId,
            'hour_id' => (int) $qrRecord->hour_id,
            'faculty_id' => $facultyId,
            'semester_id' => $effectiveSemesterId,
            'batch' => $batchName,
          ],
          [
            'status' => 'absent',
            'attendance_method' => 'manual',
          ]
        );
      }
    } else {
      $presentStudentIds = StudentAttendance::query()
        ->where('routine_id', (int) $qrRecord->routine_id)
        ->where('attendance_date', $qrRecord->attendance_date)
        ->where('hour_id', (int) $qrRecord->hour_id)
        ->where('course_id', $courseId)
        ->where('faculty_id', $facultyId)
        ->where('status', 'present')
        ->pluck('student_id')
        ->map(fn($v) => (int) $v)
        ->unique()
        ->values();

      if ($presentStudentIds->count() === 0) {
        $qrRecord->status = 3;
        $qrRecord->save();

        return response()->json([
          'success' => true,
          'message' => 'No student marked present. Treated as fake test; no attendance was recorded.',
          'data' => [
            'total_students' => $totalStudents,
            'present_students' => 0,
            'absent_marked' => 0,
          ],
        ]);
      }

      $batchName = (string) ($record->batchmaster->batch_name ?? $effectiveBatchId);
      $absentIds = $eligibleStudentIds->diff($presentStudentIds)->values();

      foreach ($absentIds as $studentId) {
        StudentAttendance::updateOrCreate(
          [
            'routine_id' => (int) $qrRecord->routine_id,
            'student_id' => (int) $studentId,
            'attendance_date' => $qrRecord->attendance_date,
            'course_id' => $courseId,
            'hour_id' => (int) $qrRecord->hour_id,
            'faculty_id' => $facultyId,
            'semester_id' => $effectiveSemesterId,
            'batch' => $batchName,
          ],
          [
            'status' => 'absent',
            'attendance_method' => 'manual',
          ]
        );
      }
    }

    $absentMarked = max(0, $totalStudents - $presentStudentIds->count());
    $qrRecord->status = 2;
    $qrRecord->save();

    return response()->json([
      'success' => true,
      'message' => 'Attendance finalized successfully for expired QR.',
      'data' => [
        'total_students' => $totalStudents,
        'present_students' => $presentStudentIds->count(),
        'absent_marked' => $absentMarked,
      ],
    ]);
  }

  public function deleteQrRecord(Request $request)
  {
    $validated = $request->validate([
      'record_id' => 'required|integer|exists:attendance_qr_masters,id',
    ]);

    $facultyId = $this->getCurrentFacultyId();
    if ($facultyId <= 0) {
      return response()->json([
        'success' => false,
        'message' => 'Faculty profile not found for this account.',
      ], 422);
    }

    $qrRecord = AttendanceQrMaster::query()
      ->where('id', (int) $validated['record_id'])
      ->first();

    if ($qrRecord && !$this->getAccessibleRoutineIds($facultyId)->contains((int) ($qrRecord->routine_id ?? 0))) {
      $qrRecord = null;
    }

    if (!$qrRecord) {
      return response()->json([
        'success' => false,
        'message' => 'QR record not found.',
      ], 404);
    }

    $qrRecord->delete();

    return response()->json([
      'success' => true,
      'message' => 'QR record deleted successfully. You can generate a new QR for this slot.',
    ]);
  }

  public function qrRecords(Request $request)
  {
    $facultyId = $this->getCurrentFacultyId();

    $accessibleRoutineIds = $this->getAccessibleRoutineIds($facultyId);

    $query = AttendanceQrMaster::query()
      ->whereIn('routine_id', $accessibleRoutineIds->all())
      ->with([
        'routine:id,syllabus_id,shift',
        'routine.syllabus:id,course_id,batch_id,semester_id',
        'routine.syllabus.courseLink.courseMaster:id,course_title,course_code',
        'routine.syllabus.semestermaster:id,title',
        'routine.syllabus.batchmaster:id,batch_name',
        'hourmaster'
      ])
      ->orderByDesc('id');

    if (!empty($request->attendance_date)) {
      $query->whereDate('attendance_date', $request->attendance_date);
    }

    if (!empty($request->course_id)) {
      $query->where('course_id', (int) $request->course_id);
    }

    $records = $query->paginate(20)->withQueryString();

    $records->getCollection()->transform(function ($record) use ($facultyId) {
      $scanCount = 0;
      $scannedStudents = collect();

      if (($record->attendance_type ?? 'regular') === 'remedial') {
        $scannedStudents = ExtraClassAttendance::query()
          ->where('routine_id', (int) $record->routine_id)
          ->where('attendance_date', $record->attendance_date)
          ->where('hour_id', (int) $record->hour_id)
          ->where('course_id', (int) $record->course_id)
          ->where('attendance_method', 'qr')
          ->with('student:id,roll_no,first_name,last_name')
          ->get()
          ->pluck('student')
          ->filter()
          ->unique('id')
          ->values();
      } else {
        $scannedStudents = StudentAttendance::query()
          ->where('routine_id', (int) $record->routine_id)
          ->where('attendance_date', $record->attendance_date)
          ->where('hour_id', (int) $record->hour_id)
          ->where('course_id', (int) $record->course_id)
          ->where('attendance_method', 'qr')
          ->with('student:id,roll_no,first_name,last_name')
          ->get()
          ->pluck('student')
          ->filter()
          ->unique('id')
          ->values();
      }

      $scanCount = (int) $scannedStudents->count();
      $record->scan_count = (int) $scanCount;
      $record->scanned_students = $scannedStudents->map(function ($student) {
        return [
          'id' => (int) ($student->id ?? 0),
          'roll_no' => (string) ($student->roll_no ?? 'N/A'),
          'name' => trim((string) (($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''))),
        ];
      })->values();

      return $record;
    });

    $courseFilterIds = AttendanceQrMaster::query()
      ->whereIn('routine_id', $accessibleRoutineIds->all())
      ->distinct()
      ->pluck('course_id')
      ->filter(fn($id) => (int) $id > 0)
      ->map(fn($id) => (int) $id)
      ->values();

    $courseFilters = ProgramCourseMaster::query()
      ->whereIn('id', $courseFilterIds->all())
      ->orderBy('course_code')
      ->get(['id', 'course_code', 'course_title']);

    return view('faculty.attendance.qr-records', [
      'records' => $records,
      'courseFilters' => $courseFilters,
    ]);
  }

  /**
   * Display the attendance interface
   */
  public function index()
  {
    $facultyId = $this->getCurrentFacultyId();

    // Get all subjects assigned to this faculty
    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
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
      $facultyId = $this->getCurrentFacultyId();

      if (!$this->getAccessibleRoutineIds($facultyId)->contains((int) $request->routine_id)) {
        throw new \Exception('You are not authorized to mark attendance for this class.');
      }

      foreach ($request->attendance as $studentId => $status) {
        StudentAttendance::updateOrCreate(
          [
            'routine_id' => $request->routine_id,
            'student_id' => $studentId,
            'attendance_date' => $request->attendance_date,
            'course_id' => $request->course_id,
            'hour_id' => $request->hour_id,
            'semester_id' => $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
            'faculty_id' => $facultyId,
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

    $facultyId = $this->getCurrentFacultyId();
    // Get all subjects assigned to this faculty
    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
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

    $accessibleRoutineIds = $this->getAccessibleRoutineIds($facultyId);

    $query = StudentAttendance::orderBy('attendance_date', 'desc')
      ->latest()
      ->whereIn('routine_id', $accessibleRoutineIds->all())
      ->with('hourmaster');


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
      $facultyId = $this->getCurrentFacultyId();
      if (!$this->getAccessibleRoutineIds($facultyId)->contains((int) ($attendance->routine_id ?? 0))) {
        return back()->with('error', 'You are not authorized to delete this attendance record.');
      }

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
    $semesterId = (int) $request->input('semester_id');
    $batchId = (int) $request->input('batch_id');

    $record =  SubjectHasSyllabus::find($syllabus_id); // Validate syllabus_id
    if (!$record) {
      return back()->with('error', 'Invalid syllabus selected.');
    }

    $routine = SubjectHasRoutine::with(['teachingAssignment:id,allocation_group', 'teachingAllocation:id,allocation_group'])->find($id);
    if (!$routine) {
      return back()->with('error', 'Invalid routine selected.');
    }

    $facultyId = $this->getCurrentFacultyId();
    if (!$this->getAccessibleRoutineIds($facultyId)->contains((int) $id)) {
      return back()->with('error', 'You are not authorized to access this class attendance.');
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));

    $course_id = $record->course_id;
    $campusId = $record->subject->campus_id;
    $effectiveBatchId = !empty($batchId) ? $batchId : (int) ($record->batch_id ?? 0);
    $effectiveSemesterId = $semesterId > 0 ? $semesterId : (int) ($record->semester_id ?? 0);

    if (empty($effectiveBatchId) || empty($effectiveSemesterId)) {
      return back()->with('error', 'Invalid batch/semester selected.');
    }

    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($record, $effectiveBatchId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', (int) ($record->subject_id ?? 0))
          ->where('shp.batch_id', '=', $effectiveBatchId);
      })
      ->select(
        'sm.id',
        'sm.roll_no',
        'sm.first_name',
        'sm.last_name',
        'sci.id as course_info_id',
        'sp.shift as program_shift',
        'sm.new_program_id',
        'sci.allocation_group_id',
        'shp.id as program_combo_id'
      )
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $effectiveBatchId)
      ->where('sci.course_id', $course_id)
      ->where('sci.semester', $effectiveSemesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    if ($routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    // Strict shift matching by enrolled program shift.
    $students = (clone $baseQuery)
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->get();

    // Specialization-aware filtering: when the course is specialization-linked,
    // show only students assigned to one of the required specializations.
    if ($students->isNotEmpty() && Schema::hasTable('student_specializations')) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $comboIds = $students->pluck('program_combo_id')->filter(fn($v) => (int) $v > 0)->map(fn($v) => (int) $v)->unique()->values();

      if ($comboIds->isNotEmpty() && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        $curriculumQuery = DB::table($curriculumTable)
          ->whereIn('program_combo_refid', $comboIds->all())
          ->where('course_id', (int) $course_id)
          ->where('semester', (int) $effectiveSemesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $curriculumQuery->where('batch', (int) $effectiveBatchId);
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
            $studentSpecQuery->where(function ($query) use ($effectiveSemesterId) {
              $query->whereNull('semester_id')->orWhere('semester_id', $effectiveSemesterId);
            });
          }

          $studentSpecRows = $studentSpecQuery
            ->select('student_id', 'subject_has_student_program_id', 'specialization_id')
            ->orderByDesc('id')
            ->get();

          $studentSpecLookup = [];
          foreach ($studentSpecRows as $specRow) {
            $studentId = (int) ($specRow->student_id ?? 0);
            $comboId = (int) ($specRow->subject_has_student_program_id ?? 0);
            $specId = (int) ($specRow->specialization_id ?? 0);

            if ($studentId <= 0 || $comboId <= 0 || $specId <= 0) {
              continue;
            }

            if (!isset($studentSpecLookup[$studentId])) {
              $studentSpecLookup[$studentId] = [];
            }
            if (!isset($studentSpecLookup[$studentId][$comboId])) {
              $studentSpecLookup[$studentId][$comboId] = [];
            }

            $studentSpecLookup[$studentId][$comboId][] = $specId;
          }

          $students = $students->filter(function ($student) use ($requiredSpecIdsByCombo, $studentSpecLookup) {
            $comboId = (int) ($student->program_combo_id ?? 0);

            // If no specialization is required for this combo, keep student.
            if ($comboId <= 0 || !isset($requiredSpecIdsByCombo[$comboId])) {
              return true;
            }

            $required = $requiredSpecIdsByCombo[$comboId] ?? [];
            if (empty($required)) {
              return true;
            }

            $studentId = (int) ($student->id ?? 0);
            $studentSpecs = $studentSpecLookup[$studentId][$comboId] ?? [];

            if (empty($studentSpecs)) {
              return false;
            }

            return !empty(array_intersect($required, array_map(fn($v) => (int) $v, $studentSpecs)));
          })->values();
        }
      }
    }

    $syllabusAssignment = SubjectHasSyllabus::with([
      'courseLink.courseMaster.coursetypemaster',
      'semestermaster:id,title',
      'batchmaster'
    ])->find($syllabus_id);

    $availableHours = $this->getShiftTeachingHours($routineShift);

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
        'semesterId' => $effectiveSemesterId,
        'existingAttendance' => $existingAttendance,
        'availableHours' => $availableHours,
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
        'semesterId' => $effectiveSemesterId,
        'existingAttendance' => $existingAttendance,
        'availableHours' => $availableHours,
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
          'faculty_id' => $this->getCurrentFacultyId(),
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
    $facultyId = $this->getCurrentFacultyId();

    // Get all subjects assigned to this faculty
    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
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
      $facultyId = $this->getCurrentFacultyId();

      if (!$this->getAccessibleRoutineIds($facultyId)->contains((int) $request->routine_id)) {
        throw new \Exception('You are not authorized to mark remedial attendance for this class.');
      }

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
            'semester_id' => (int) $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
            'faculty_id' => (int) $facultyId,
          ]
        );
      }

      DB::commit();
      return redirect()
        ->route('faculty.attendance.view.remedial-class')
        ->with('success', 'Remedial Class Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Extra Class Attendance Error: ' . $e->getMessage());
      return back()->with('error', 'Failed to record attendance: ' . $e->getMessage());
    }
  }


  /**
   * View attendance records
   */
  public function viewExtraClassAttendance(Request $request)
  {

    $facultyId = $this->getCurrentFacultyId();
    // Get all subjects assigned to this faculty
    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->get()
      ->unique('syllabus_id');


    $accessibleRoutineIds = $this->getAccessibleRoutineIds($facultyId);

    $query = ExtraClassAttendance::orderBy('attendance_date', 'desc')
      ->orderBy('hour_id', 'asc')
      ->whereIn('routine_id', $accessibleRoutineIds->all());

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

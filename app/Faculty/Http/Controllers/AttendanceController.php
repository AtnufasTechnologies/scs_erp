<?php

namespace App\Faculty\Http\Controllers;

use App\Exports\CourseRosterExport;
use App\Http\Controllers\StaticController;
use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\AttendanceQrMaster;
use App\Models\ExtraClassAttendance;
use App\Models\HourMaster;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\StudentCourseRoster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\TeachingAssignment;
use App\Models\ShiftMaster;
use App\Services\AttendanceEligibilityService;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
  private AttendanceEligibilityService $attendanceEligibilityService;

  public function __construct(AttendanceEligibilityService $attendanceEligibilityService)
  {
    $this->attendanceEligibilityService = $attendanceEligibilityService;
  }

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
    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    return $query->where(function ($nested) use ($facultyId) {
      $nested->where('faculty_id', $facultyId)
        ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
          $assignmentQuery->where('faculty_id', $facultyId)
            ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
              $facultyAssignmentQuery->where('faculty_id', $facultyId);
            })
            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
              $coFacultyQuery->where('faculties.id', $facultyId);
            });
        });
    })->when($hasTeachingAllocationLink, function ($builder) use ($facultyId) {
      $builder->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyId) {
        $assignmentQuery->where('faculty_id', $facultyId)
          ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
            $facultyAssignmentQuery->where('faculty_id', $facultyId);
          })
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

  private function getRoutineDeliveryType(?SubjectHasRoutine $routine): string
  {
    if (!$routine) {
      return '';
    }

    return strtoupper(trim((string) (
      $routine->teachingAssignment->delivery_type
      ?? $routine->teachingAllocation->delivery_type
      ?? ''
    )));
  }

  private function normalizeProgramTypeLabel($programType): string
  {
    $value = strtoupper(trim((string) $programType));
    if (str_starts_with($value, 'PG')) {
      return 'PG';
    }
    if (str_starts_with($value, 'UG')) {
      return 'UG';
    }

    return $value;
  }

  private function buildRoutineIdentityKey(?SubjectHasRoutine $routine): string
  {
    $syllabusId = (int) ($routine->syllabus_id ?? 0);
    $shift = strtolower(trim((string) ($routine->shift ?? 'common')));
    $deliveryType = $this->getRoutineDeliveryType($routine);
    $programType = $this->normalizeProgramTypeLabel($routine->program_type ?? $routine->syllabus->program_type ?? 'UG');

    return $syllabusId . '_' . $programType . '_' . $shift . '_' . $deliveryType;
  }

  private function hasStudentRosterRoutineScope(): bool
  {
    static $hasRoutineColumn = null;
    if ($hasRoutineColumn === null) {
      $hasRoutineColumn = Schema::hasTable('student_course_rosters')
        && Schema::hasColumn('student_course_rosters', 'routine_id');
    }

    return (bool) $hasRoutineColumn;
  }

  private function scopeStudentRosterQuery($query, int $assignmentId, int $courseId, int $routineId, bool $includeLegacyNullRoutine = false)
  {
    $query->where('ta_id', $assignmentId)
      ->where('course_id', $courseId);

    if ($this->hasStudentRosterRoutineScope()) {
      $query->where(function ($routineScope) use ($routineId, $includeLegacyNullRoutine) {
        $routineScope->where('routine_id', $routineId);

        if ($includeLegacyNullRoutine) {
          $routineScope->orWhereNull('routine_id');
        }
      });
    }

    return $query;
  }

  private function buildStudentRosterPayload(int $assignmentId, int $courseId, int $studentId, int $routineId): array
  {
    $payload = [
      'ta_id' => $assignmentId,
      'course_id' => $courseId,
      'student_id' => $studentId,
    ];

    if ($this->hasStudentRosterRoutineScope()) {
      $payload['routine_id'] = $routineId;
    }

    return $payload;
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

  public function getResolvedStudentCount(Request $request)
  {
    try {
      $recId = (int) $request->get('rec_id', 0);
      $syllabusId = (int) $request->get('syllabus_id', 0);
      $batchId = (int) $request->get('batch_id', 0);
      $semesterId = (int) $request->get('semester_id', 0);

      if ($recId <= 0 || $syllabusId <= 0) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid attendance context.',
        ], 422);
      }

      $facultyId = $this->getCurrentFacultyId();
      if (!$this->getAccessibleRoutineIds($facultyId)->contains($recId)) {
        return response()->json([
          'success' => false,
          'message' => 'Unauthorized routine access.',
        ], 403);
      }

      $record = SubjectHasSyllabus::with('subject')->find($syllabusId);
      if (!$record) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid syllabus selected.',
        ], 404);
      }

      $routine = SubjectHasRoutine::with([
        'teachingAssignment:id,allocation_group,delivery_type',
        'teachingAllocation:id,allocation_group,delivery_type'
      ])->find($recId);

      if (!$routine) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid routine selected.',
        ], 404);
      }

      $effectiveBatchId = $batchId > 0 ? $batchId : (int) ($record->batch_id ?? 0);
      $effectiveSemesterId = $semesterId > 0 ? $semesterId : (int) ($record->semester_id ?? 0);
      $courseId = (int) ($record->course_id ?? 0);

      if ($effectiveBatchId <= 0 || $effectiveSemesterId <= 0 || $courseId <= 0) {
        return response()->json([
          'success' => false,
          'message' => 'Missing batch, semester, or course context.',
        ], 422);
      }

      $students = $this->getEligibleStudentsForRoutine(
        $record,
        $routine,
        $effectiveBatchId,
        $effectiveSemesterId,
        $courseId,
        false
      );

      return response()->json([
        'success' => true,
        'data' => [
          'count' => (int) $students->count(),
          'batch_id' => $effectiveBatchId,
          'semester_id' => $effectiveSemesterId,
          'course_id' => $courseId,
        ],
      ]);
    } catch (\Throwable $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to resolve student count: ' . $e->getMessage(),
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
    int $courseId,
    bool $withProfile = false
  ) {
    $rosterResolved = $this->resolveStudentsViaCourseRoster($routine, $courseId, $withProfile);
    if ($rosterResolved->isNotEmpty()) {
      return $rosterResolved;
    }

    $resolved = $this->resolveStudentsViaStaticController(
      $record,
      $effectiveBatchId,
      $effectiveSemesterId,
      $withProfile
    );

    if ($resolved->isNotEmpty()) {
      return $resolved;
    }

    $campusId = (int) ($record->subject->campus_id ?? 0);

    return $this->attendanceEligibilityService->getEligibleStudents(
      $routine,
      $courseId,
      $effectiveSemesterId,
      $effectiveBatchId,
      $campusId,
      (int) ($record->subject_id ?? 0),
      $withProfile
    );
  }

  private function resolveStudentsViaStaticController(
    SubjectHasSyllabus $record,
    int $batchId,
    int $semesterId,
    bool $withProfile = false
  ) {
    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    if (!Schema::hasTable($curriculumTable)) {
      return collect();
    }

    $courseId = (int) ($record->course_id ?? 0);
    $subjectId = (int) ($record->subject_id ?? 0);
    if ($courseId <= 0 || $batchId <= 0 || $semesterId <= 0 || $subjectId <= 0) {
      return collect();
    }

    $joinedProgramCombo = false;
    $curriculumQuery = DB::table($curriculumTable . ' as ce')
      ->where('ce.course_id', $courseId)
      ->where('ce.batch', $batchId)
      ->where('ce.semester', $semesterId);

    if (Schema::hasColumn($curriculumTable, 'program_combo_refid') && Schema::hasTable('subject_has_student_progams')) {
      $joinedProgramCombo = true;
      $curriculumQuery->join('subject_has_student_progams as shp', 'shp.id', '=', 'ce.program_combo_refid')
        ->where('shp.subject_id', $subjectId);

      if (Schema::hasColumn('subject_has_student_progams', 'batch_id')) {
        $curriculumQuery->where('shp.batch_id', $batchId);
      }
    }

    $curriculumSelect = [
      'ce.id',
      'ce.program_combo_refid',
    ];

    if ($joinedProgramCombo && Schema::hasColumn('subject_has_student_progams', 'student_program_id')) {
      $curriculumSelect[] = 'shp.student_program_id';
    }

    $curriculumRows = $curriculumQuery
      ->select($curriculumSelect)
      ->orderBy('ce.id')
      ->get();

    if ($curriculumRows->isEmpty()) {
      return collect();
    }

    $students = collect();

    foreach ($curriculumRows as $curriculumRow) {
      $programId = (int) ($curriculumRow->student_program_id ?? 0);
      if ($programId <= 0 && (int) ($curriculumRow->program_combo_refid ?? 0) > 0 && Schema::hasTable('subject_has_student_progams')) {
        $programId = (int) DB::table('subject_has_student_progams')
          ->where('id', (int) ($curriculumRow->program_combo_refid ?? 0))
          ->value('student_program_id');
      }

      if ($programId <= 0) {
        continue;
      }

      $resolverRequest = Request::create('/erp/itcell/resolve-student-list', 'GET', [
        'curriculum_row_id' => (int) ($curriculumRow->id ?? 0),
        'batch_id' => $batchId,
        'semester_id' => $semesterId,
        'program_id' => $programId,
      ]);
      $resolverRequest->headers->set('Accept', 'application/json');
      $resolverRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

      $resolverResponse = StaticController::resolveStudentList($resolverRequest);
      if (!($resolverResponse instanceof \Illuminate\Http\JsonResponse)) {
        continue;
      }

      $payload = $resolverResponse->getData(true);
      $resolvedRows = collect($payload['students'] ?? []);
      if ($resolvedRows->isEmpty()) {
        continue;
      }

      $students = $students->merge($resolvedRows->map(function ($row) use ($withProfile) {
        $student = (object) [
          'id' => (int) ($row['id'] ?? 0),
          'new_program_id' => (int) ($row['new_program_id'] ?? 0),
          'batch' => (int) ($row['batch'] ?? 0),
          'academic_pathway_id' => (int) ($row['academic_pathway_id'] ?? 0),
          'degree_track_id' => (int) ($row['degree_track_id'] ?? 0),
        ];

        if ($withProfile) {
          $student->roll_no = (string) ($row['roll_no'] ?? '');
          $student->register_no = (string) ($row['register_no'] ?? '');
          $student->first_name = (string) ($row['first_name'] ?? '');
          $student->last_name = (string) ($row['last_name'] ?? '');
        }

        return $student;
      }));
    }

    return $students
      ->filter(fn($student) => (int) ($student->id ?? 0) > 0)
      ->unique('id')
      ->sortBy('roll_no')
      ->values();
  }

  private function resolveStudentsViaCourseRoster(
    SubjectHasRoutine $routine,
    int $courseId,
    bool $withProfile = false
  ) {
    if (!Schema::hasTable('student_course_rosters')) {
      return collect();
    }

    $assignment = $routine->teachingAssignment ?: $routine->teachingAllocation;
    $assignmentId = (int) ($assignment->id ?? 0);

    if ($assignmentId <= 0 || $courseId <= 0) {
      return collect();
    }

    $studentIds = StudentCourseRoster::query()
      ->when(true, function ($query) use ($assignmentId, $courseId, $routine) {
        return $this->scopeStudentRosterQuery($query, $assignmentId, $courseId, (int) ($routine->id ?? 0), true);
      })
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();

    if ($studentIds->isEmpty()) {
      return collect();
    }

    $studentQuery = StudentMaster::query()
      ->whereIn('id', $studentIds->all())
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('roll_no')
      ->orderBy('first_name');

    if ($withProfile) {
      return $studentQuery->get(['id', 'first_name', 'last_name', 'roll_no', 'register_no']);
    }

    return $studentQuery->get(['id']);
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
        'syllabus.courseLink.courseMaster:id,course_title,course_code,course_type',
        'syllabus.courseLink.courseMaster.coursetypemaster:id,title',
        'teachingAssignment:id,delivery_type',
        'teachingAllocation:id,delivery_type',
      ])
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->unique(fn($routine) => $this->buildRoutineIdentityKey($routine))
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
        'teachingAssignment:id,delivery_type',
        'teachingAllocation:id,delivery_type',
      ])
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->unique(fn($routine) => $this->buildRoutineIdentityKey($routine))
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

    $routine = SubjectHasRoutine::with([
      'teachingAssignment:id,allocation_group,delivery_type',
      'teachingAllocation:id,allocation_group,delivery_type'
    ])->find($id);
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

    $students = $this->getEligibleStudentsForRoutine(
      $record,
      $routine,
      $effectiveBatchId,
      $effectiveSemesterId,
      (int) $course_id,
      true
    );

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
        'teachingAssignment:id,delivery_type',
        'teachingAllocation:id,delivery_type',
      ])
      ->get()
      ->unique(fn($routine) => $this->buildRoutineIdentityKey($routine));

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
        'teachingAssignment:id,delivery_type',
        'teachingAllocation:id,delivery_type',
      ])
      ->get()
      ->unique(fn($routine) => $this->buildRoutineIdentityKey($routine));


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

  //Student Course Roster
  function studentCourseRoster(Request $request)
  {
    $searchTerm = trim((string) $request->query('q', ''));
    $facultyId = $this->getCurrentFacultyId();

    if ($facultyId <= 0) {
      return view('faculty.courseroster.index', [
        'assignmentRows' => collect(),
        'searchTerm' => $searchTerm,
        'totalAssignmentCount' => 0,
      ]);
    }

    // Get all subjects assigned to this faculty
    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
        'teachingAssignment:id,delivery_type',
        'teachingAllocation:id,delivery_type',
      ])
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->unique(fn($routine) => $this->buildRoutineIdentityKey($routine))
      ->values();

    $assignmentRows = $syllabusAssignments->map(function ($routine) use ($facultyId) {
      $assignment = $routine->teachingAssignment ?: $routine->teachingAllocation;
      $courseMaster = $routine->syllabus->courseLink->courseMaster ?? null;
      $courseCode = trim((string) ($courseMaster->course_code ?? ''));
      $routeCode = $courseCode !== '' ? $courseCode : 'NA';
      $courseTitle = trim((string) ($courseMaster->course_title ?? 'N/A'));
      $courseType = trim((string) ($courseMaster->coursetypemaster->title ?? ''));
      $assignmentId = (int) ($assignment->id ?? 0);
      $courseId = (int) ($routine->syllabus->course_id ?? ($assignment->course_id ?? 0));
      $programType = $this->normalizeProgramTypeLabel($routine->program_type ?? $routine->syllabus->program_type ?? 'UG');

      $studentCount = 0;
      if ($assignmentId > 0 && $courseId > 0) {
        $studentCount = $this->scopeStudentRosterQuery(
          StudentCourseRoster::query(),
          $assignmentId,
          $courseId,
          (int) ($routine->id ?? 0),
          true
        )
          ->count();

        if ($studentCount === 0) {
          $eligibleStudents = $this->getEligibleStudentsForRoutine(
            $routine->syllabus,
            $routine,
            (int) ($routine->syllabus->batch_id ?? 0),
            (int) ($routine->syllabus->semester_id ?? 0),
            $courseId,
            false
          );

          $studentCount = (int) $eligibleStudents->count();
        }
      }

      $deliveryType = strtoupper(trim((string) (
        $assignment->delivery_type
        ?? $routine->teachingAssignment->delivery_type
        ?? $routine->teachingAllocation->delivery_type
        ?? ''
      )));

      $roles = [];
      if ((int) ($assignment->faculty_id ?? 0) === (int) $facultyId) {
        $roles[] = TeachingAssignment::ROLE_PRIMARY;
      }
      if (empty($roles)) {
        $roles[] = TeachingAssignment::ROLE_CO_FACULTY;
      }

      return [
        'id' => (int) ($routine->id ?? 0),
        'assignment_id' => $assignmentId,
        'course_id' => $courseId,
        'course_code' => $courseCode,
        'route_code' => $routeCode,
        'course_title' => $courseTitle,
        'course_type' => $courseType,
        'course_label' => trim($courseCode . ($courseCode !== '' ? ' - ' : '') . $courseTitle),
        'program_type' => $programType !== '' ? $programType : '-',
        'semester_title' => trim((string) ($routine->syllabus->semestermaster->title ?? 'N/A')),
        'subject_title' => trim((string) ($routine->syllabus->subject->title ?? 'N/A')),
        'roles' => $roles,
        'delivery_type' => $deliveryType,
        'allocation_group' => trim((string) (isset($assignment->allocation_group_label)
          ? $assignment->allocation_group_label
          : ('Group ' . (int) ($assignment->allocation_group ?? 0)))),
        'student_count' => (int) $studentCount,
        'shift' => ucfirst(strtolower(trim((string) ($routine->shift ?? 'common')))),
        'room' => trim((string) ($assignment->room ?? '')),
        'primary_faculty' => trim((string) ($assignment->faculty->FIRST_NAME ?? '') . ' ' . (string) ($assignment->faculty->LAST_NAME ?? '')),
      ];
    })->values();

    $totalAssignmentCount = (int) $assignmentRows->count();

    if ($searchTerm !== '') {
      $needle = strtolower($searchTerm);

      $assignmentRows = $assignmentRows->filter(function ($row) use ($needle) {
        $haystack = strtolower(implode(' ', [
          (string) ($row['course_code'] ?? ''),
          (string) ($row['course_title'] ?? ''),
          (string) ($row['program_type'] ?? ''),
          (string) ($row['semester_title'] ?? ''),
          (string) ($row['subject_title'] ?? ''),
          (string) ($row['delivery_type'] ?? ''),
          (string) ($row['allocation_group'] ?? ''),
          (string) ($row['shift'] ?? ''),
          (string) ($row['room'] ?? ''),
          (string) ($row['primary_faculty'] ?? ''),
        ]));

        return str_contains($haystack, $needle);
      })->values();
    }


    return view('faculty.courseroster.index', [
      'assignmentRows' => $assignmentRows,
      'searchTerm' => $searchTerm,
      'totalAssignmentCount' => $totalAssignmentCount,
    ]);
  }

  function createCourseRoster($id, $code)
  {
    $facultyId = $this->getCurrentFacultyId();
    $routineId = (int) $id;

    $routine = SubjectHasRoutine::with([
      'syllabus.subject:id,campus_id,title',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.semestermaster:id,title',
      'syllabus.courseLink.courseMaster:id,course_code,course_title',
      'teachingAssignment:id,course_id,allocation_group',
      'teachingAllocation:id,course_id,allocation_group',
    ])->find($routineId);

    if (!$routine || !$routine->syllabus) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid routine selected.');
    }

    $record = $routine->teachingAssignment ?: $routine->teachingAllocation;
    if (!$record) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'No teaching assignment found for this routine.');
    }

    $hasAssignmentAccess = (int) ($record->faculty_id ?? 0) === $facultyId
      || $record->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $record->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasAssignmentAccess) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'You are not allotted for this teaching assignment.');
    }

    $courseId = (int) ($routine->syllabus->course_id ?? ($record->course_id ?? 0));
    $batchId = (int) ($routine->syllabus->batch_id ?? 0);
    $semesterId = (int) ($routine->syllabus->semester_id ?? 0);

    if ($courseId <= 0 || $batchId <= 0 || $semesterId <= 0) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'Missing course, batch, or semester context.');
    }

    $studentsQuery = StudentMaster::query()
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('roll_no')
      ->orderBy('first_name');

    $students = $studentsQuery
      ->get(['id', 'first_name', 'last_name', 'roll_no', 'register_no', 'campus_id'])
      ->values();

    $existingStudentIds = StudentCourseRoster::query()
      ->when(true, function ($query) use ($record, $courseId, $routineId) {
        return $this->scopeStudentRosterQuery($query, (int) $record->id, $courseId, $routineId, true);
      })
      ->whereIn('student_id', $students->pluck('id')->all())
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->all();

    $copySourceRows = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_code,course_title',
        'teachingAssignment:id,course_id,delivery_type',
        'teachingAllocation:id,course_id,delivery_type',
      ])
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->filter(function ($candidate) use ($routineId) {
        return (int) ($candidate->id ?? 0) !== $routineId;
      })
      ->map(function ($candidate) {
        $candidateAssignment = $candidate->teachingAssignment ?: $candidate->teachingAllocation;
        $candidateCourseMaster = $candidate->syllabus->courseLink->courseMaster ?? null;
        $candidateCourseCode = trim((string) ($candidateCourseMaster->course_code ?? 'N/A'));
        $candidateCourseTitle = trim((string) ($candidateCourseMaster->course_title ?? 'N/A'));
        $candidateCourseId = (int) ($candidate->syllabus->course_id ?? ($candidateAssignment->course_id ?? 0));
        $candidateAssignmentId = (int) ($candidateAssignment->id ?? 0);
        $candidateBatchName = trim((string) ($candidate->syllabus->batchmaster->batch_name ?? 'N/A'));
        $candidateSemesterTitle = trim((string) ($candidate->syllabus->semestermaster->title ?? 'N/A'));
        $candidateProgramType = $this->normalizeProgramTypeLabel($candidate->program_type ?? $candidate->syllabus->program_type ?? 'UG');
        $candidateDeliveryType = strtoupper(trim((string) (
          $candidateAssignment->delivery_type
          ?? $candidate->teachingAssignment->delivery_type
          ?? $candidate->teachingAllocation->delivery_type
          ?? 'N/A'
        )));
        $candidateShift = ucfirst(strtolower(trim((string) ($candidate->shift ?? 'common'))));

        if ($candidateAssignmentId <= 0 || $candidateCourseId <= 0) {
          return null;
        }

        $candidateKey = $candidateAssignmentId . '_' . $candidateCourseId . '_' . (int) ($candidate->id ?? 0);

        $candidateRoutineId = (int) ($candidate->id ?? 0);
        $rosterCount = $this->scopeStudentRosterQuery(
          StudentCourseRoster::query(),
          $candidateAssignmentId,
          $candidateCourseId,
          $candidateRoutineId,
          true
        )
          ->count();

        return [
          'key' => $candidateKey,
          'routine_id' => (int) ($candidate->id ?? 0),
          'assignment_id' => $candidateAssignmentId,
          'course_id' => $candidateCourseId,
          'course_code' => $candidateCourseCode,
          'course_title' => $candidateCourseTitle,
          'batch_name' => $candidateBatchName,
          'semester_title' => $candidateSemesterTitle,
          'program_type' => $candidateProgramType !== '' ? $candidateProgramType : '-',
          'delivery_type' => $candidateDeliveryType,
          'shift' => $candidateShift,
          'subject_title' => trim((string) ($candidate->syllabus->subject->title ?? 'N/A')),
          'roster_count' => (int) $rosterCount,
          'label' => $candidateCourseCode . ' - ' . $candidateCourseTitle
            . ' | Batch: ' . $candidateBatchName
            . ' | Program: ' . ($candidateProgramType !== '' ? $candidateProgramType : '-')
            . ' | Semester: ' . $candidateSemesterTitle
            . ' | Delivery: ' . $candidateDeliveryType
            . ' | Shift: ' . $candidateShift
            . ' (' . $rosterCount . ' students)',
        ];
      })
      ->filter()
      ->groupBy('key')
      ->map(function ($groupedRows) {
        return $groupedRows->first();
      })
      ->sortByDesc('roster_count')
      ->values();

    return view('faculty.courseroster.create', [
      'routine' => $routine,
      'students' => $students,
      'record' => $record,
      'existingStudentIds' => $existingStudentIds,
      'copySourceRows' => $copySourceRows,
      'courseId' => $courseId,
      'batchId' => $batchId,
      'semesterId' => $semesterId,
    ]);
  }

  public function copyCourseRosterFromAssigned(Request $request, $id, $code)
  {
    $facultyId = $this->getCurrentFacultyId();
    $targetRoutineId = (int) $id;

    $request->validate([
      'source_routine_id' => 'required|integer|exists:subject_has_routines,id',
    ]);

    $sourceRoutineId = (int) $request->input('source_routine_id');
    if ($sourceRoutineId === $targetRoutineId) {
      return response()->json(['success' => false, 'message' => 'Source and target course cannot be the same.'], 422);
    }

    $targetRoutine = SubjectHasRoutine::with([
      'syllabus:id,course_id',
      'teachingAssignment:id,course_id,faculty_id',
      'teachingAllocation:id,course_id,faculty_id',
    ])->find($targetRoutineId);

    $sourceRoutine = SubjectHasRoutine::with([
      'syllabus:id,course_id',
      'teachingAssignment:id,course_id,faculty_id',
      'teachingAllocation:id,course_id,faculty_id',
      'syllabus.courseLink.courseMaster:id,course_code,course_title',
    ])->find($sourceRoutineId);

    if (!$targetRoutine || !$targetRoutine->syllabus || !$sourceRoutine || !$sourceRoutine->syllabus) {
      return response()->json(['success' => false, 'message' => 'Invalid source/target routine selected.'], 404);
    }

    $targetRecord = $targetRoutine->teachingAssignment ?: $targetRoutine->teachingAllocation;
    $sourceRecord = $sourceRoutine->teachingAssignment ?: $sourceRoutine->teachingAllocation;

    if (!$targetRecord || !$sourceRecord) {
      return response()->json(['success' => false, 'message' => 'Teaching assignment context missing for source/target course.'], 422);
    }

    $hasTargetAccess = (int) ($targetRecord->faculty_id ?? 0) === $facultyId
      || $targetRecord->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $targetRecord->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    $hasSourceAccess = (int) ($sourceRecord->faculty_id ?? 0) === $facultyId
      || $sourceRecord->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $sourceRecord->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasTargetAccess || !$hasSourceAccess) {
      return response()->json(['success' => false, 'message' => 'You are not allotted to source/target teaching assignment.'], 403);
    }

    $targetCourseId = (int) ($targetRoutine->syllabus->course_id ?? ($targetRecord->course_id ?? 0));
    $sourceCourseId = (int) ($sourceRoutine->syllabus->course_id ?? ($sourceRecord->course_id ?? 0));

    if ($targetCourseId <= 0 || $sourceCourseId <= 0) {
      return response()->json(['success' => false, 'message' => 'Invalid course mapping in source/target routine.'], 422);
    }

    $sourceStudentIds = StudentCourseRoster::query()
      ->when(true, function ($query) use ($sourceRecord, $sourceCourseId, $sourceRoutineId) {
        return $this->scopeStudentRosterQuery($query, (int) $sourceRecord->id, $sourceCourseId, $sourceRoutineId, true);
      })
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();

    if ($sourceStudentIds->isEmpty()) {
      return response()->json(['success' => false, 'message' => 'No students found in the selected source roster.'], 422);
    }

    $existingTargetIds = StudentCourseRoster::query()
      ->when(true, function ($query) use ($targetRecord, $targetCourseId, $targetRoutineId) {
        return $this->scopeStudentRosterQuery($query, (int) $targetRecord->id, $targetCourseId, $targetRoutineId, true);
      })
      ->whereIn('student_id', $sourceStudentIds->all())
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->all();

    $existingMap = array_flip($existingTargetIds);
    $added = 0;
    $skipped = 0;

    foreach ($sourceStudentIds as $studentId) {
      if (isset($existingMap[$studentId])) {
        $skipped++;
        continue;
      }

      StudentCourseRoster::create($this->buildStudentRosterPayload(
        (int) $targetRecord->id,
        $targetCourseId,
        (int) $studentId,
        $targetRoutineId
      ));
      $added++;
    }

    $sourceCourseCode = trim((string) ($sourceRoutine->syllabus->courseLink->courseMaster->course_code ?? 'source course'));
    $message = $added . ' student(s) copied from ' . $sourceCourseCode . '.';
    if ($skipped > 0) {
      $message .= ' ' . $skipped . ' already present (skipped).';
    }

    return response()->json([
      'success' => true,
      'message' => $message,
      'data' => [
        'added' => $added,
        'skipped' => $skipped,
      ],
    ]);
  }

  public function storeCourseRoster(Request $request, $id, $code)
  {
    $facultyId = $this->getCurrentFacultyId();
    $routineId = (int) $id;

    $routine = SubjectHasRoutine::with([
      'syllabus.subject:id,campus_id',
      'syllabus.batchmaster:id,batch_name',
      'teachingAssignment:id,allocation_group',
      'teachingAllocation:id,allocation_group',
    ])->find($routineId);

    if (!$routine || !$routine->syllabus) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'Invalid routine selected.'], 404);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid routine selected.');
    }

    $record = $routine->teachingAssignment ?: $routine->teachingAllocation;
    if (!$record) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'No teaching assignment found for this routine.'], 422);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'No teaching assignment found for this routine.');
    }

    $hasAssignmentAccess = (int) ($record->faculty_id ?? 0) === $facultyId
      || $record->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $record->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasAssignmentAccess) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'You are not allotted for this teaching assignment.'], 403);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'You are not allotted for this teaching assignment.');
    }

    $courseId = (int) ($routine->syllabus->course_id ?? 0);

    if ($courseId <= 0) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'Course roster context is incomplete.'], 422);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'Course roster context is incomplete.');
    }

    $request->validate([
      'student_ids' => 'required|array|min:1',
      'student_ids.*' => 'integer|exists:student_masters,id',
      'redirect_to' => 'nullable|string',
    ]);

    $selectedStudentIds = collect($request->input('student_ids', []))
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();

    if ($selectedStudentIds->isEmpty()) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'Please select at least one student.'], 422);
      }
      return back()->with('error', 'Please select at least one student.')->withInput();
    }

    $eligibleStudentsQuery = StudentMaster::query()
      ->whereIn('id', $selectedStudentIds->all())
      ->where('is_deleted', 0)
      ->where('is_left', 0);

    $eligibleStudents = $eligibleStudentsQuery->get(['id', 'campus_id']);
    $eligibleStudentIds = $eligibleStudents->pluck('id')->map(fn($studentId) => (int) $studentId)->all();

    if (count($eligibleStudentIds) === 0) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'No valid students were selected for this course roster.'], 422);
      }
      return back()->with('error', 'No valid students were selected for this course roster.')->withInput();
    }

    $existingStudentIds = StudentCourseRoster::query()
      ->when(true, function ($query) use ($record, $courseId, $routineId) {
        return $this->scopeStudentRosterQuery($query, (int) $record->id, $courseId, $routineId, true);
      })
      ->whereIn('student_id', $eligibleStudentIds)
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->all();

    $existingMap = array_flip($existingStudentIds);
    $affected = 0;
    $skipped = 0;
    foreach ($eligibleStudentIds as $studentId) {
      if (isset($existingMap[$studentId])) {
        $skipped++;
        continue;
      }

      StudentCourseRoster::create($this->buildStudentRosterPayload(
        (int) $record->id,
        $courseId,
        $studentId,
        $routineId
      ));
      $affected++;
    }

    $message = $affected . ' student(s) added to course roster.';
    if ($skipped > 0) {
      $message .= ' ' . $skipped . ' already present (skipped).';
    }

    if ($request->expectsJson() || $request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => $message,
        'data' => [
          'added' => $affected,
          'skipped' => $skipped,
        ],
      ]);
    }

    $redirectTarget = strtolower(trim((string) $request->input('redirect_to', 'create')));

    if ($redirectTarget === 'list') {
      return redirect()
        ->route('faculty.course.roster.list', ['id' => $routineId, 'code' => $code])
        ->with('success', $message);
    }

    return redirect()
      ->route('faculty.course.roster.create', ['id' => $routineId, 'code' => $code])
      ->with('success', $message);
  }

  public function viewCourseRoster($id, $code)
  {
    $facultyId = $this->getCurrentFacultyId();
    $routineId = (int) $id;

    $routine = SubjectHasRoutine::with([
      'syllabus.subject:id,campus_id,title',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.semestermaster:id,title',
      'syllabus.courseLink.courseMaster:id,course_code,course_title',
      'teachingAssignment:id,course_id,allocation_group,faculty_id',
      'teachingAllocation:id,course_id,allocation_group,faculty_id',
    ])->find($routineId);

    if (!$routine || !$routine->syllabus) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid routine selected.');
    }

    $record = $routine->teachingAssignment ?: $routine->teachingAllocation;
    if (!$record) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'No teaching assignment found for this routine.');
    }

    $hasAssignmentAccess = (int) ($record->faculty_id ?? 0) === $facultyId
      || $record->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $record->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasAssignmentAccess) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'You are not allotted for this teaching assignment.');
    }

    $courseId = (int) ($routine->syllabus->course_id ?? ($record->course_id ?? 0));
    if ($courseId <= 0) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid course context for roster list.');
    }

    $rosterRows = StudentCourseRoster::query()
      ->with('studentmaster:id,roll_no,register_no,first_name,last_name')
      ->when(true, function ($query) use ($record, $courseId, $routineId) {
        return $this->scopeStudentRosterQuery($query, (int) $record->id, $courseId, $routineId, true);
      })
      ->orderByDesc('id')
      ->get();

    $isResolvedFallback = false;
    if ($rosterRows->isEmpty()) {
      $resolvedStudents = $this->getEligibleStudentsForRoutine(
        $routine->syllabus,
        $routine,
        (int) ($routine->syllabus->batch_id ?? 0),
        (int) ($routine->syllabus->semester_id ?? 0),
        $courseId,
        true
      );

      if ($resolvedStudents->isNotEmpty()) {
        $isResolvedFallback = true;
        $rosterRows = $resolvedStudents->map(function ($student) {
          return (object) [
            'student_id' => (int) ($student->id ?? 0),
            'studentmaster' => (object) [
              'roll_no' => (string) ($student->roll_no ?? ''),
              'register_no' => (string) ($student->register_no ?? ''),
              'first_name' => (string) ($student->first_name ?? ''),
              'last_name' => (string) ($student->last_name ?? ''),
            ],
            'is_virtual' => true,
          ];
        })->values();
      }
    }

    return view('faculty.courseroster.list', [
      'routine' => $routine,
      'record' => $record,
      'courseId' => $courseId,
      'rosterRows' => $rosterRows,
      'isResolvedFallback' => $isResolvedFallback,
    ]);
  }

  public function exportCourseRoster($id, $code)
  {
    $facultyId = $this->getCurrentFacultyId();
    $routineId = (int) $id;

    $routine = SubjectHasRoutine::with([
      'syllabus.subject:id,title',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.semestermaster:id,title',
      'syllabus.courseLink.courseMaster:id,course_code,course_title',
      'teachingAssignment:id,course_id,faculty_id',
      'teachingAllocation:id,course_id,faculty_id',
    ])->find($routineId);

    if (!$routine || !$routine->syllabus) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid routine selected.');
    }

    $record = $routine->teachingAssignment ?: $routine->teachingAllocation;
    if (!$record) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'No teaching assignment found for this routine.');
    }

    $hasAssignmentAccess = (int) ($record->faculty_id ?? 0) === $facultyId
      || $record->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $record->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasAssignmentAccess) {
      return redirect()->route('faculty.student.course.roster')->with('error', 'You are not allotted for this teaching assignment.');
    }

    $courseId = (int) ($routine->syllabus->course_id ?? ($record->course_id ?? 0));

    $rows = StudentCourseRoster::query()
      ->with('studentmaster:id,roll_no,register_no,first_name,last_name')
      ->when(true, function ($query) use ($record, $courseId, $routineId) {
        return $this->scopeStudentRosterQuery($query, (int) $record->id, $courseId, $routineId, true);
      })
      ->orderBy('id')
      ->get();

    $courseCode = trim((string) ($routine->syllabus->courseLink->courseMaster->course_code ?? (string) $code));
    $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseCode ?: 'course');
    $fileName = 'course_roster_' . $safeCode . '_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new CourseRosterExport($rows), $fileName);
  }

  public function removeCourseRosterStudent(Request $request, $id, $code, $studentId)
  {
    $facultyId = $this->getCurrentFacultyId();
    $routineId = (int) $id;
    $targetStudentId = (int) $studentId;

    $routine = SubjectHasRoutine::with([
      'syllabus:id,course_id',
      'teachingAssignment:id,course_id,faculty_id',
      'teachingAllocation:id,course_id,faculty_id',
    ])->find($routineId);

    if (!$routine || !$routine->syllabus) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'Invalid routine selected.'], 404);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'Invalid routine selected.');
    }

    $record = $routine->teachingAssignment ?: $routine->teachingAllocation;
    if (!$record) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'No teaching assignment found for this routine.'], 422);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'No teaching assignment found for this routine.');
    }

    $hasAssignmentAccess = (int) ($record->faculty_id ?? 0) === $facultyId
      || $record->facultyAssignments()->where('faculty_id', $facultyId)->exists()
      || $record->coFacultyMembers()->where('faculties.id', $facultyId)->exists();

    if ($facultyId <= 0 || !$hasAssignmentAccess) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'You are not allotted for this teaching assignment.'], 403);
      }
      return redirect()->route('faculty.student.course.roster')->with('error', 'You are not allotted for this teaching assignment.');
    }

    $courseId = (int) ($routine->syllabus->course_id ?? ($record->course_id ?? 0));

    $deleted = StudentCourseRoster::query()
      ->when(true, function ($query) use ($record, $courseId, $routineId) {
        return $this->scopeStudentRosterQuery($query, (int) $record->id, $courseId, $routineId, true);
      })
      ->where('student_id', $targetStudentId)
      ->delete();

    if ($deleted <= 0) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'Student is not present in this roster.'], 404);
      }
      return redirect()
        ->route('faculty.course.roster.list', ['id' => $routineId, 'code' => $code])
        ->with('error', 'Student is not present in this roster.');
    }

    if ($request->expectsJson() || $request->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Student removed from roster.',
        'data' => [
          'student_id' => $targetStudentId,
        ],
      ]);
    }

    return redirect()
      ->route('faculty.course.roster.list', ['id' => $routineId, 'code' => $code])
      ->with('success', 'Student removed from roster.');
  }
}

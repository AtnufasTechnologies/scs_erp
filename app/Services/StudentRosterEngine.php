<?php

namespace App\Services;

use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\StudentRosterRuleMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StudentRosterEngine
{
  private array $lastEvaluationByStudent = [];

  /**
   * SINGLE ENTRY POINT
   *
   * Every module that needs the students attending a course
   * must call this method.
   */
  public function getStudentsForCourse($course, array $context = []): Collection
  {
    $course = $this->normalizeCourse($course, $context);
    $this->lastEvaluationByStudent = [];

    $candidates = $this->getCandidateStudents($course, $context);

    $included = collect();

    foreach ($candidates as $student) {
      if (!$student instanceof StudentMaster) {
        continue;
      }

      $decision = $this->evaluateStudent($student, $course, $context);
      $this->lastEvaluationByStudent[(int) $student->id] = $decision;

      $this->debugDecision(
        $student,
        $course,
        $decision['rule'] ?? null,
        (bool) ($decision['included'] ?? false),
        (string) ($decision['reason_code'] ?? 'UNKNOWN'),
        (string) ($decision['reason'] ?? 'Unknown decision'),
        [
          'roster_source' => $decision['roster_source'] ?? null,
          'trace' => $decision['trace'] ?? [],
        ]
      );

      $this->persistDecision($student, $course, $decision);

      if ((bool) ($decision['included'] ?? false)) {
        $included->push($student);
      }
    }

    return $included->unique('id')->values();
  }

  /**
   * Backward compatible roster output used by tests and legacy callers.
   */
  public function getRoster(int $courseId, array $context = []): Collection
  {
    $course = (object) [
      'id' => $courseId,
      'delivery_type' => strtoupper(trim((string) ($context['delivery_type'] ?? ''))),
      'selection_type' => strtoupper(trim((string) ($context['selection_type'] ?? ''))),
      'semester_id' => (int) ($context['semester_id'] ?? 0),
      'batch_id' => (int) ($context['batch_id'] ?? 0),
      'offering_dept' => (int) ($context['offering_dept'] ?? 0),
    ];

    return $this->getStudentsForCourse($course, $context)
      ->map(function (StudentMaster $student) use ($course) {
        return [
          'student_id' => (int) $student->id,
          'roll_no' => (string) ($student->roll_no ?? ''),
          'register_no' => (string) ($student->register_no ?? ''),
          'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
          'program_id' => (int) ($student->new_program_id ?? 0),
          'batch_id' => (int) ($student->batch ?? 0),
          'semester_id' => (int) ($course->semester_id ?? 0),
        ];
      })
      ->values();
  }

  /**
   * Full deterministic explanation for one student.
   */
  public function explainStudentForCourse(int $studentId, $course, array $context = []): array
  {
    $course = $this->normalizeCourse($course, $context);

    $student = StudentMaster::query()->find($studentId);
    if (!$student) {
      return [
        'student_id' => $studentId,
        'included' => false,
        'reason_code' => 'STUDENT_SELECTION_NOT_FOUND',
        'reason' => 'Student not found.',
        'trace' => [],
      ];
    }

    $decision = $this->evaluateStudent($student, $course, $context);

    return [
      'student_id' => (int) $student->id,
      'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
      'academic_pathway_id' => $this->getAcademicPathwayId($student),
      'degree_track_id' => $this->getDegreeTrackId($student),
      'delivery_type' => (string) ($course->delivery_type ?? ''),
      'selection_type' => (string) ($course->selection_type ?? ''),
      'rule_id' => (int) (($decision['rule']->id ?? 0) ?: 0),
      'rule_code' => (string) ($decision['rule']->rule->rule_code ?? ''),
      'roster_source' => (string) ($decision['roster_source'] ?? ''),
      'included' => (bool) ($decision['included'] ?? false),
      'reason_code' => (string) ($decision['reason_code'] ?? 'UNKNOWN'),
      'reason' => (string) ($decision['reason'] ?? ''),
      'trace' => $decision['trace'] ?? [],
    ];
  }

  /**
   * Full debug payload for roster inspection command.
   */
  public function explainRosterForCourse($course, array $context = []): array
  {
    $course = $this->normalizeCourse($course, $context);
    $candidates = $this->getCandidateStudents($course, $context);

    $rows = $candidates->map(function (StudentMaster $student) use ($course, $context) {
      $decision = $this->evaluateStudent($student, $course, $context);
      $rule = $decision['rule'] ?? null;

      return [
        'student_id' => (int) $student->id,
        'name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
        'program_id' => (int) ($student->new_program_id ?? 0),
        'batch_id' => (int) ($student->batch ?? 0),
        'semester_id' => (int) ($this->getStudentSemesterId($student, $course) ?? 0),
        'academic_pathway_id' => $this->getAcademicPathwayId($student),
        'degree_track_id' => $this->getDegreeTrackId($student),
        'rule_id' => (int) (($rule->id ?? 0) ?: 0),
        'rule_code' => (string) ($rule->rule->rule_code ?? ''),
        'roster_source' => (string) ($decision['roster_source'] ?? ''),
        'decision' => (bool) ($decision['included'] ?? false) ? 'INCLUDED' : 'EXCLUDED',
        'reason_code' => (string) ($decision['reason_code'] ?? 'UNKNOWN'),
        'reason' => (string) ($decision['reason'] ?? ''),
      ];
    })->values();

    return [
      'course' => [
        'course_id' => (int) ($course->id ?? 0),
        'delivery_type' => (string) ($course->delivery_type ?? ''),
        'selection_type' => (string) ($course->selection_type ?? ''),
        'batch_id' => (int) ($course->batch_id ?? 0),
        'semester_id' => (int) ($course->semester_id ?? 0),
        'offering_dept' => (int) ($course->offering_dept ?? 0),
      ],
      'total_candidates' => (int) $rows->count(),
      'total_roster' => (int) $rows->where('decision', 'INCLUDED')->count(),
      'students' => $rows,
    ];
  }

  // ========================================================================
  // MAIN STUDENT DECISION
  // ========================================================================

  protected function evaluateStudent(
    StudentMaster $student,
    $course,
    array $context = []
  ): array {
    if ($this->isStudentLeft($student)) {
      return $this->exclude(null, 'STUDENT_LEFT', 'Student is marked as left.', [
        'pathway' => 'SKIP',
      ]);
    }

    $pathwayId = $this->getAcademicPathwayId($student);
    if (!$pathwayId) {
      return $this->exclude(null, 'NO_ACADEMIC_PATHWAY', 'Academic pathway is missing for student.', [
        'pathway' => 'FAIL',
      ]);
    }

    $degreeTrackId = $this->getDegreeTrackId($student);

    $rule = $this->resolveRule($pathwayId, $degreeTrackId, $course);
    if (!$rule) {
      return $this->exclude(null, 'NO_RULE', 'No active roster rule mapping matched pathway/track/delivery/selection.', [
        'pathway' => 'PASS',
        'degree_track' => $degreeTrackId ? 'PASS' : 'MISSING',
      ]);
    }

    $semesterCheck = $this->passesSemesterScope($student, $course, $rule, $context);
    if (!$semesterCheck['pass']) {
      return $this->exclude($rule, 'SEMESTER_SCOPE', $semesterCheck['reason'], [
        'pathway' => 'PASS',
        'semester' => 'FAIL',
        'roster_source' => strtoupper(trim((string) $rule->roster_source)),
      ]);
    }

    $batchCheck = $this->passesBatchScope($student, $course, $rule, $context);
    if (!$batchCheck['pass']) {
      return $this->exclude($rule, 'BATCH_SCOPE', $batchCheck['reason'], [
        'pathway' => 'PASS',
        'semester' => 'PASS',
        'batch' => 'FAIL',
        'roster_source' => strtoupper(trim((string) $rule->roster_source)),
      ]);
    }

    $specializationCheck = $this->passesSpecializationScope($student, $course, $rule);
    if (!$specializationCheck['pass']) {
      return $this->exclude($rule, 'SPECIALIZATION_SCOPE', $specializationCheck['reason'], [
        'pathway' => 'PASS',
        'semester' => 'PASS',
        'batch' => 'PASS',
        'specialization' => 'FAIL',
      ]);
    }

    $majorCheck = $this->passesMajorRestriction($student, $course, $rule);
    if (!$majorCheck['pass']) {
      return $this->exclude($rule, 'MAJOR_RESTRICTION', $majorCheck['reason'], [
        'pathway' => 'PASS',
        'semester' => 'PASS',
        'batch' => 'PASS',
        'specialization' => 'PASS',
        'major_restriction' => 'FAIL',
      ]);
    }

    $sourceCheck = $this->executeRosterSource($student, $course, $rule, $context);
    if (!$sourceCheck['pass']) {
      return $this->exclude($rule, $sourceCheck['reason_code'], $sourceCheck['reason'], [
        'pathway' => 'PASS',
        'semester' => 'PASS',
        'batch' => 'PASS',
        'specialization' => 'PASS',
        'major_restriction' => 'PASS',
        'roster_source' => strtoupper(trim((string) $rule->roster_source)),
        'roster_source_result' => 'FAIL',
      ]);
    }

    return [
      'included' => true,
      'reason_code' => 'INCLUDED',
      'reason' => 'Student included by deterministic roster rule.',
      'rule' => $rule,
      'roster_source' => strtoupper(trim((string) $rule->roster_source)),
      'trace' => [
        'pathway' => 'PASS',
        'degree_track' => $degreeTrackId ? 'PASS' : 'GENERIC_RULE',
        'semester' => 'PASS',
        'batch' => 'PASS',
        'specialization' => 'PASS',
        'major_restriction' => 'PASS',
        'roster_source' => 'PASS',
      ],
    ];
  }

  protected function exclude(?StudentRosterRuleMapping $rule, string $code, string $reason, array $trace = []): array
  {
    return [
      'included' => false,
      'reason_code' => $code,
      'reason' => $reason,
      'rule' => $rule,
      'roster_source' => (string) ($rule ? strtoupper(trim((string) $rule->roster_source)) : ''),
      'trace' => $trace,
    ];
  }

  // ========================================================================
  // COURSE NORMALIZATION
  // ========================================================================

  protected function normalizeCourse($course, array $context = []): object
  {
    $normalized = (object) [
      'id' => (int) (
        data_get($course, 'id')
        ?? data_get($course, 'course_id')
        ?? data_get($context, 'course_id')
        ?? 0
      ),
      'delivery_type' => strtoupper(trim((string) (
        data_get($course, 'delivery_type')
        ?? data_get($course, 'delivery_category')
        ?? data_get($context, 'delivery_type')
        ?? ''
      ))),
      'selection_type' => strtoupper(trim((string) (
        data_get($course, 'selection_type')
        ?? data_get($course, 'course_type')
        ?? data_get($context, 'selection_type')
        ?? ''
      ))),
      'semester_id' => (int) (
        data_get($course, 'semester_id')
        ?? data_get($course, 'semester')
        ?? data_get($context, 'semester_id')
        ?? 0
      ),
      'batch_id' => (int) (
        data_get($course, 'batch_id')
        ?? data_get($course, 'batch')
        ?? data_get($context, 'batch_id')
        ?? 0
      ),
      'program_id' => (int) (
        data_get($course, 'program_id')
        ?? data_get($context, 'program_id')
        ?? 0
      ),
      'offering_dept' => (int) (
        data_get($course, 'offering_dept')
        ?? data_get($course, 'department')
        ?? data_get($context, 'offering_dept')
        ?? 0
      ),
    ];

    return $this->hydrateCourseFromCurriculum($normalized, $context);
  }

  protected function hydrateCourseFromCurriculum(object $course, array $context = []): object
  {
    if ($course->id <= 0) {
      return $course;
    }

    if ($course->delivery_type !== '' && $course->selection_type !== '') {
      return $course;
    }

    $table = (new ProgramWiseSemesterCourse())->getTable();

    $query = DB::table($table)
      ->where('course_id', $course->id);

    if (Schema::hasColumn($table, 'is_active')) {
      $query->where('is_active', 1);
    }

    if (Schema::hasColumn($table, 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    if ($course->batch_id > 0 && Schema::hasColumn($table, 'batch')) {
      $query->where('batch', $course->batch_id);
    }

    if ($course->semester_id > 0 && Schema::hasColumn($table, 'semester')) {
      $query->where('semester', $course->semester_id);
    }

    if (!empty($context['program_combo_refid']) && Schema::hasColumn($table, 'program_combo_refid')) {
      $query->where('program_combo_refid', (int) $context['program_combo_refid']);
    }

    $row = $query->orderBy('id')->first([
      'delivery_category',
      'course_type',
      'offering_dept',
      'batch',
      'semester',
    ]);

    if (!$row) {
      return $course;
    }

    if ($course->delivery_type === '') {
      $course->delivery_type = strtoupper(trim((string) ($row->delivery_category ?? '')));
    }

    if ($course->selection_type === '') {
      $course->selection_type = strtoupper(trim((string) ($row->course_type ?? '')));
    }

    if ((int) $course->offering_dept <= 0) {
      $course->offering_dept = (int) ($row->offering_dept ?? 0);
    }

    if ((int) $course->batch_id <= 0) {
      $course->batch_id = (int) ($row->batch ?? 0);
    }

    if ((int) $course->semester_id <= 0) {
      $course->semester_id = (int) ($row->semester ?? 0);
    }

    return $course;
  }

  // ========================================================================
  // RULE RESOLUTION
  // ========================================================================

  protected function resolveRule(
    int $academicPathwayId,
    ?int $degreeTrackId,
    $course
  ): ?StudentRosterRuleMapping {
    $delivery = strtoupper(trim((string) ($course->delivery_type ?? '')));
    $selection = strtoupper(trim((string) ($course->selection_type ?? '')));

    $query = StudentRosterRuleMapping::query()
      ->with(['rule', 'academicPathway', 'degreeTrack'])
      ->where('is_active', true)
      ->where(function ($q) use ($academicPathwayId) {
        $q->where('academic_pathway_id', $academicPathwayId);
        if (Schema::hasColumn('student_roster_rule_mappings', 'academic_pathway_id')) {
          $q->orWhereNull('academic_pathway_id');
        }
      });

    if ($delivery !== '') {
      $query->whereRaw('UPPER(TRIM(COALESCE(delivery_type, ""))) = ?', [$delivery]);
    }

    $query->where(function ($q) use ($degreeTrackId) {
      if ($degreeTrackId) {
        $q->where('degree_track_id', $degreeTrackId);
      }
      $q->orWhereNull('degree_track_id');
    });

    $query->where(function ($q) use ($selection) {
      if ($selection !== '') {
        $q->whereRaw('UPPER(TRIM(COALESCE(selection_type, ""))) = ?', [$selection]);
      }
      $q->orWhereNull('selection_type');
    });

    $query
      ->orderByRaw('CASE WHEN academic_pathway_id = ? THEN 0 ELSE 1 END', [$academicPathwayId])
      ->orderByRaw('CASE WHEN degree_track_id = ? THEN 0 WHEN degree_track_id IS NULL THEN 1 ELSE 2 END', [$degreeTrackId])
      ->orderByRaw('CASE WHEN UPPER(TRIM(COALESCE(delivery_type, ""))) = ? THEN 0 ELSE 1 END', [$delivery])
      ->orderByRaw('CASE WHEN UPPER(TRIM(COALESCE(selection_type, ""))) = ? THEN 0 WHEN selection_type IS NULL THEN 1 ELSE 2 END', [$selection])
      ->orderByRaw('(
        (CASE WHEN academic_pathway_id IS NOT NULL THEN 1 ELSE 0 END) +
        (CASE WHEN degree_track_id IS NOT NULL THEN 1 ELSE 0 END) +
        (CASE WHEN selection_type IS NOT NULL THEN 1 ELSE 0 END)
      ) DESC')
      ->orderBy('priority')
      ->orderBy('id');

    return $query->first();
  }

  // ========================================================================
  // ROSTER SOURCE
  // ========================================================================

  protected function executeRosterSource(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule,
    array $context = []
  ): array {
    $source = strtoupper(trim((string) $rule->roster_source));

    if ($source === 'COMBO1') {
      $pass = $this->qualifiesThroughCombo($student, $course, 'COMBO1');
      return [
        'pass' => $pass,
        'reason_code' => $pass ? 'INCLUDED' : 'COMBO1_NOT_MATCHED',
        'reason' => $pass ? 'COMBO1 matched.' : 'Student does not match required COMBO1 scope.',
      ];
    }

    if ($source === 'COMBO2') {
      $pass = $this->qualifiesThroughCombo($student, $course, 'COMBO2');
      return [
        'pass' => $pass,
        'reason_code' => $pass ? 'INCLUDED' : 'COMBO2_NOT_MATCHED',
        'reason' => $pass ? 'COMBO2 matched.' : 'Student does not match required COMBO2 scope.',
      ];
    }

    if ($source === 'STUDENT_SELECTION') {
      $pass = $this->qualifiesThroughStudentSelection($student, $course, $context);
      return [
        'pass' => $pass,
        'reason_code' => $pass ? 'INCLUDED' : 'STUDENT_SELECTION_NOT_FOUND',
        'reason' => $pass ? 'Student selection/enrollment matched.' : 'No explicit student selection/enrollment found for this course.',
      ];
    }

    if ($source === 'CURRICULUM') {
      $pass = $this->qualifiesThroughCurriculum($student, $course, $context);
      return [
        'pass' => $pass,
        'reason_code' => $pass ? 'INCLUDED' : 'CURRICULUM_NOT_APPLICABLE',
        'reason' => $pass ? 'Curriculum applicability matched.' : 'Curriculum applicability did not match this student.',
      ];
    }

    if ($source === 'TEACHING_GROUP') {
      $pass = $this->isInTeachingGroup($student, $course, $context);
      return [
        'pass' => $pass,
        'reason_code' => $pass ? 'INCLUDED' : 'TEACHING_GROUP_NOT_MATCHED',
        'reason' => $pass ? 'Student belongs to teaching group.' : 'Student is not a member of requested teaching group.',
      ];
    }

    return [
      'pass' => false,
      'reason_code' => 'NO_RULE',
      'reason' => 'Unknown roster source in mapping: ' . $source,
    ];
  }

  // ========================================================================
  // ACADEMIC PATHWAY / TRACK
  // ========================================================================

  protected function getAcademicPathwayId(StudentMaster $student): ?int
  {
    if (!empty($student->academic_pathway_id)) {
      return (int) $student->academic_pathway_id;
    }

    if ($student->relationLoaded('academicpathway') && $student->academicpathway) {
      return (int) $student->academicpathway->id;
    }

    if ($student->academicpathway) {
      return (int) $student->academicpathway->id;
    }

    return null;
  }

  protected function getDegreeTrackId(StudentMaster $student): ?int
  {
    if (!empty($student->degree_track_id)) {
      return (int) $student->degree_track_id;
    }

    if ($student->relationLoaded('degreetrack') && $student->degreetrack) {
      return (int) $student->degreetrack->id;
    }

    if ($student->degreetrack) {
      return (int) $student->degreetrack->id;
    }

    return null;
  }

  // ========================================================================
  // SEMESTER / BATCH
  // ========================================================================

  protected function passesSemesterScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule,
    array $context = []
  ): array {
    $scope = strtoupper(trim((string) ($rule->semester_scope ?? 'SAME')));

    if ($scope === 'ANY') {
      return ['pass' => true, 'reason' => 'Semester scope is ANY.'];
    }

    if ($this->isTeachingGroupSourceOrOverride($rule, $context)) {
      return ['pass' => true, 'reason' => 'Teaching group exception allows cross-semester.'];
    }

    $courseSemester = (int) ($course->semester_id ?? 0);
    $studentSemester = (int) ($this->getStudentSemesterId($student, $course) ?? 0);

    if ($courseSemester <= 0 || $studentSemester <= 0) {
      return ['pass' => false, 'reason' => 'Semester scope is SAME but semester data is unavailable.'];
    }

    return [
      'pass' => $studentSemester === $courseSemester,
      'reason' => $studentSemester === $courseSemester
        ? 'Semester matched.'
        : 'Semester mismatch for SAME semester scope.',
    ];
  }

  protected function passesBatchScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule,
    array $context = []
  ): array {
    $scope = strtoupper(trim((string) ($rule->batch_scope ?? 'SAME')));

    if ($scope === 'ANY') {
      return ['pass' => true, 'reason' => 'Batch scope is ANY.'];
    }

    if ($this->isTeachingGroupSourceOrOverride($rule, $context)) {
      return ['pass' => true, 'reason' => 'Teaching group exception allows cross-batch.'];
    }

    $courseBatch = (int) ($course->batch_id ?? 0);
    $studentBatch = (int) ($student->batch ?? 0);

    if ($courseBatch <= 0 || $studentBatch <= 0) {
      return ['pass' => false, 'reason' => 'Batch scope is SAME but batch data is unavailable.'];
    }

    return [
      'pass' => $studentBatch === $courseBatch,
      'reason' => $studentBatch === $courseBatch
        ? 'Batch matched.'
        : 'Batch mismatch for SAME batch scope.',
    ];
  }

  protected function getStudentSemesterId(StudentMaster $student, $course = null): ?int
  {
    if ($course && (int) ($course->id ?? 0) > 0) {
      $query = StudentCourseInfo::query()
        ->where('student_id', (int) $student->id)
        ->where('course_id', (int) $course->id);

      if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
        $query->where('is_deleted', 0);
      }

      if (Schema::hasColumn('student_course_infos', 'deleted_at')) {
        $query->whereNull('deleted_at');
      }

      $row = $query->orderByDesc('id')->first(['semester']);
      if ($row && !empty($row->semester)) {
        return (int) $row->semester;
      }
    }

    if (!empty($student->semester_id)) {
      return (int) $student->semester_id;
    }

    if (!empty($student->current_semester)) {
      return (int) $student->current_semester;
    }

    if ($student->activeSemesterConfig && !empty($student->activeSemesterConfig->semester_id)) {
      return (int) $student->activeSemesterConfig->semester_id;
    }

    return null;
  }

  // ========================================================================
  // SPECIALIZATION
  // ========================================================================

  protected function passesSpecializationScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): array {
    $scope = strtoupper(trim((string) ($rule->specialization_scope ?? 'ANY')));

    if ($scope === 'ANY') {
      return ['pass' => true, 'reason' => 'Specialization scope is ANY.'];
    }

    $pass = $this->studentCourseSpecializationApplies($student, $course);

    return [
      'pass' => $pass,
      'reason' => $pass
        ? 'Student specialization matched required curriculum specialization.'
        : 'Specialization required but no matching student specialization found.',
    ];
  }

  protected function studentCourseSpecializationApplies(
    StudentMaster $student,
    $course
  ): bool {
    if (!Schema::hasTable('student_specializations')) {
      return true;
    }

    $rows = $this->getApplicableCurriculumRowsForStudent($student, $course);
    if ($rows->isEmpty()) {
      return false;
    }

    foreach ($rows as $row) {
      $mode = strtoupper(trim((string) ($row->specialization_mode ?? 'COMMON')));
      if (in_array($mode, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAM_COMMON', 'ALL', 'ANY'], true)) {
        return true;
      }

      $requiredSpecIds = [];
      $singleSpecId = (int) ($row->specialization_master_id ?? 0);
      if ($singleSpecId > 0) {
        $requiredSpecIds[] = $singleSpecId;
      }

      $rawSpecIds = $row->specialization_master_ids ?? null;
      if (is_string($rawSpecIds) && trim($rawSpecIds) !== '') {
        $decoded = json_decode($rawSpecIds, true);
        if (is_array($decoded)) {
          $rawSpecIds = $decoded;
        }
      }

      if (is_array($rawSpecIds)) {
        foreach ($rawSpecIds as $value) {
          $id = (int) $value;
          if ($id > 0) {
            $requiredSpecIds[] = $id;
          }
        }
      }

      $requiredSpecIds = array_values(array_unique($requiredSpecIds));
      if (empty($requiredSpecIds)) {
        continue;
      }

      $studentSpecQuery = DB::table('student_specializations')
        ->where('student_id', (int) $student->id)
        ->whereIn('specialization_id', $requiredSpecIds);

      if (Schema::hasColumn('student_specializations', 'subject_has_student_program_id')) {
        $programComboId = (int) ($row->program_combo_refid ?? 0);
        if ($programComboId > 0) {
          $studentSpecQuery->where('subject_has_student_program_id', $programComboId);
        }
      }

      if (Schema::hasColumn('student_specializations', 'semester_id') && (int) ($course->semester_id ?? 0) > 0) {
        $semester = (int) ($course->semester_id ?? 0);
        $studentSpecQuery->where(function ($q) use ($semester) {
          $q->whereNull('semester_id')->orWhere('semester_id', $semester);
        });
      }

      if (Schema::hasColumn('student_specializations', 'is_active')) {
        $studentSpecQuery->where('is_active', 1);
      }

      if (Schema::hasColumn('student_specializations', 'deleted_at')) {
        $studentSpecQuery->whereNull('deleted_at');
      }

      if ($studentSpecQuery->exists()) {
        return true;
      }
    }

    return false;
  }

  // ========================================================================
  // MAJOR RESTRICTION
  // ========================================================================

  protected function passesMajorRestriction(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): array {
    $restriction = strtoupper(trim((string) ($rule->major_restriction ?? 'NONE')));

    if ($restriction === '' || $restriction === 'NONE') {
      return ['pass' => true, 'reason' => 'No major restriction.'];
    }

    if ($restriction === 'EXCLUDE_MAJOR_DEPARTMENTS') {
      $blocked = $this->courseBelongsToStudentsMajorDepartment($student, $course);
      return [
        'pass' => !$blocked,
        'reason' => $blocked
          ? 'Selected course belongs to student major department(s), restricted by rule.'
          : 'Course is outside student major department(s).',
      ];
    }

    return ['pass' => true, 'reason' => 'Unknown major restriction treated as pass.'];
  }

  protected function courseBelongsToStudentsMajorDepartment(
    StudentMaster $student,
    $course
  ): bool {
    $courseDept = (int) ($course->offering_dept ?? 0);
    if ($courseDept <= 0 && (int) ($course->id ?? 0) > 0) {
      $courseDept = (int) (
        ProgramCourseMaster::query()
        ->where('id', (int) $course->id)
        ->value('department')
      );
    }

    if ($courseDept <= 0) {
      return false;
    }

    $comboMap = $this->getComboMapForStudentProgram((int) ($student->new_program_id ?? 0));
    $majorIds = [];

    if ($comboMap) {
      $combo1 = (int) ($comboMap->combo_id_1 ?? 0);
      $combo2 = (int) ($comboMap->combo_id_2 ?? 0);
      if ($combo1 > 0) {
        $majorIds[] = $combo1;
      }
      if ($combo2 > 0) {
        $majorIds[] = $combo2;
      }
    }

    $selectedCombo = (int) ($student->selected_combo_id ?? 0);
    if ($selectedCombo > 0) {
      $majorIds[] = $selectedCombo;
    }

    $majorIds = array_values(array_unique(array_filter($majorIds, fn($v) => (int) $v > 0)));

    return in_array($courseDept, $majorIds, true);
  }

  // ========================================================================
  // COMBO
  // ========================================================================

  protected function qualifiesThroughCombo(
    StudentMaster $student,
    $course,
    string $combo
  ): bool {
    $combo = strtoupper(trim($combo));

    $studentProgramId = (int) ($student->new_program_id ?? 0);
    if ($studentProgramId <= 0) {
      return false;
    }

    $comboMap = $this->getComboMapForStudentProgram($studentProgramId);
    if (!$comboMap) {
      return false;
    }

    $expectedSubjectId = $combo === 'COMBO2'
      ? (int) ($comboMap->combo_id_2 ?? 0)
      : (int) ($comboMap->combo_id_1 ?? 0);

    if ($expectedSubjectId <= 0) {
      return false;
    }

    $studentComboRows = DB::table('subject_has_student_progams')
      ->where('student_program_id', $studentProgramId)
      ->when((int) ($course->batch_id ?? 0) > 0, function ($q) use ($course) {
        if (Schema::hasColumn('subject_has_student_progams', 'batch_id')) {
          $q->where('batch_id', (int) $course->batch_id);
        }
      })
      ->when(Schema::hasColumn('subject_has_student_progams', 'deleted_at'), function ($q) {
        $q->whereNull('deleted_at');
      })
      ->get(['id', 'subject_id']);

    if ($studentComboRows->isEmpty()) {
      return false;
    }

    $directSubjectComboRowIds = $studentComboRows
      ->filter(function ($row) use ($expectedSubjectId) {
        return (int) ($row->subject_id ?? 0) === $expectedSubjectId;
      })
      ->pluck('id')
      ->map(fn($v) => (int) $v)
      ->filter(fn($v) => $v > 0)
      ->values();

    // COMBO1 remains strict. COMBO2 supports fallback via program-combo mapping rows
    // when institutions store combo2 in std_prog_combo_maps but not as direct subject rows.
    $candidateComboRowIds = $directSubjectComboRowIds;
    if ($combo === 'COMBO2' && $candidateComboRowIds->isEmpty()) {
      $candidateComboRowIds = $studentComboRows
        ->pluck('id')
        ->map(fn($v) => (int) $v)
        ->filter(fn($v) => $v > 0)
        ->values();
    }

    if ($candidateComboRowIds->isEmpty()) {
      return false;
    }

    $curriculumRows = $this->getApplicableCurriculumRowsForStudent($student, $course)
      ->filter(function ($row) use ($combo) {
        $delivery = strtoupper(trim((string) ($row->delivery_category ?? '')));
        return $delivery === $combo;
      })
      ->values();

    if ($curriculumRows->isEmpty()) {
      return false;
    }

    $allowedComboRefIds = $curriculumRows
      ->pluck('program_combo_refid')
      ->map(fn($v) => (int) $v)
      ->filter(fn($v) => $v > 0)
      ->unique()
      ->values();

    if ($allowedComboRefIds->isEmpty()) {
      return false;
    }

    if (!$candidateComboRowIds->intersect($allowedComboRefIds)->isNotEmpty()) {
      return false;
    }

    if ($this->isSingleMajorPathway($this->getAcademicPathwayId($student))) {
      $selectedComboId = (int) ($student->selected_combo_id ?? 0);
      if ($selectedComboId > 0 && $selectedComboId !== $expectedSubjectId) {
        return false;
      }
    }

    return true;
  }

  // ========================================================================
  // STUDENT SELECTION
  // ========================================================================

  protected function qualifiesThroughStudentSelection(
    StudentMaster $student,
    $course,
    array $context = []
  ): bool {
    if (isset($context['student_ids']) && is_array($context['student_ids'])) {
      if (!in_array((int) $student->id, array_map('intval', $context['student_ids']), true)) {
        return false;
      }
    }

    $query = StudentCourseInfo::query()
      ->where('student_id', (int) $student->id)
      ->where('course_id', (int) ($course->id ?? 0));

    if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $query->where('is_deleted', 0);
    }

    if (Schema::hasColumn('student_course_infos', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    if (!$this->hasTeachingGroupContext($context) && (int) ($course->semester_id ?? 0) > 0) {
      $query->where('semester', (int) $course->semester_id);
    }

    $selected = $query->exists();
    if (!$selected) {
      return false;
    }

    // MDC student-choice should stay same-semester except Teaching Group contexts.
    $delivery = strtoupper(trim((string) ($course->delivery_type ?? '')));
    $selection = strtoupper(trim((string) ($course->selection_type ?? '')));
    if ($delivery === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE && $selection === ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE && !$this->hasTeachingGroupContext($context)) {
      $studentSemester = (int) ($this->getStudentSemesterId($student, $course) ?? 0);
      $courseSemester = (int) ($course->semester_id ?? 0);
      if ($studentSemester <= 0 || $courseSemester <= 0) {
        return false;
      }

      if ($studentSemester !== $courseSemester) {
        return false;
      }
    }

    return true;
  }

  // ========================================================================
  // CURRICULUM
  // ========================================================================

  protected function qualifiesThroughCurriculum(
    StudentMaster $student,
    $course,
    array $context = []
  ): bool {
    $rows = $this->getApplicableCurriculumRowsForStudent($student, $course);

    if ($rows->isEmpty()) {
      return false;
    }

    if (!empty($context['program_combo_refid'])) {
      $programComboRefId = (int) $context['program_combo_refid'];
      return $rows->contains(fn($row) => (int) ($row->program_combo_refid ?? 0) === $programComboRefId);
    }

    return true;
  }

  protected function getApplicableCurriculumRowsForStudent(StudentMaster $student, $course): Collection
  {
    $table = (new ProgramWiseSemesterCourse())->getTable();

    if (!Schema::hasTable($table)) {
      return collect();
    }

    $query = DB::table($table)
      ->where('course_id', (int) ($course->id ?? 0));

    if (Schema::hasColumn($table, 'is_active')) {
      $query->where('is_active', 1);
    }

    if (Schema::hasColumn($table, 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    if ((int) ($course->batch_id ?? 0) > 0 && Schema::hasColumn($table, 'batch')) {
      $query->where('batch', (int) $course->batch_id);
    }

    if ((int) ($course->semester_id ?? 0) > 0 && Schema::hasColumn($table, 'semester')) {
      $query->where('semester', (int) $course->semester_id);
    }

    if (Schema::hasColumn($table, 'academic_pathway_id')) {
      $pathwayId = (int) ($this->getAcademicPathwayId($student) ?? 0);
      $query->where(function ($q) use ($pathwayId) {
        if ($pathwayId > 0) {
          $q->where('academic_pathway_id', $pathwayId);
        }
        $q->orWhereNull('academic_pathway_id');
      });
    }

    if (Schema::hasColumn($table, 'degree_track_id')) {
      $degreeTrackId = (int) ($this->getDegreeTrackId($student) ?? 0);
      $query->where(function ($q) use ($degreeTrackId) {
        if ($degreeTrackId > 0) {
          $q->where('degree_track_id', $degreeTrackId);
        }
        $q->orWhereNull('degree_track_id');
      });
    }

    $studentProgramId = (int) ($student->new_program_id ?? 0);
    if ($studentProgramId > 0 && Schema::hasColumn($table, 'program_combo_refid') && Schema::hasTable('subject_has_student_progams')) {
      $query->whereIn('program_combo_refid', function ($sq) use ($studentProgramId, $course) {
        $sq->from('subject_has_student_progams')
          ->select('id')
          ->where('student_program_id', $studentProgramId)
          ->when((int) ($course->batch_id ?? 0) > 0, function ($inner) use ($course) {
            if (Schema::hasColumn('subject_has_student_progams', 'batch_id')) {
              $inner->where('batch_id', (int) $course->batch_id);
            }
          })
          ->when(Schema::hasColumn('subject_has_student_progams', 'deleted_at'), function ($inner) {
            $inner->whereNull('deleted_at');
          });
      });
    }

    return $query->orderBy('id')->get();
  }

  // ========================================================================
  // TEACHING GROUP
  // ========================================================================

  protected function isInTeachingGroup(
    StudentMaster $student,
    $course,
    array $context = []
  ): bool {
    $teachingGroupId = (int) ($context['teaching_group_id'] ?? 0);
    if ($teachingGroupId <= 0) {
      return false;
    }

    $groupItemQuery = DB::table('teaching_group_items')
      ->where('allocation_group_id', $teachingGroupId);

    if ((int) ($course->id ?? 0) > 0 && Schema::hasColumn('teaching_group_items', 'course_id')) {
      $groupItemQuery->where('course_id', (int) $course->id);
    }

    if (Schema::hasColumn('teaching_group_items', 'subject_id') && (int) ($context['subject_id'] ?? 0) > 0) {
      $groupItemQuery->where('subject_id', (int) $context['subject_id']);
    }

    if (Schema::hasColumn('teaching_group_items', 'student_program_id')) {
      $groupItemQuery->where(function ($q) use ($student) {
        $q->where('student_program_id', (int) ($student->new_program_id ?? 0))
          ->orWhereNull('student_program_id');
      });
    }

    if (Schema::hasColumn('teaching_group_items', 'deleted_at')) {
      $groupItemQuery->whereNull('deleted_at');
    }

    if (!$groupItemQuery->exists()) {
      return false;
    }

    $membershipQuery = StudentCourseInfo::query()
      ->where('student_id', (int) $student->id)
      ->where('allocation_group_id', $teachingGroupId);

    if ((int) ($course->id ?? 0) > 0) {
      $membershipQuery->where('course_id', (int) $course->id);
    }

    if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $membershipQuery->where('is_deleted', 0);
    }

    if (Schema::hasColumn('student_course_infos', 'deleted_at')) {
      $membershipQuery->whereNull('deleted_at');
    }

    return $membershipQuery->exists();
  }

  protected function getTeachingGroupStudents(
    $course,
    array $context
  ): Collection {
    $teachingGroupId = (int) ($context['teaching_group_id'] ?? 0);

    if ($teachingGroupId <= 0) {
      return collect();
    }

    $query = StudentMaster::query()
      ->join('student_course_infos as sci', 'sci.student_id', '=', 'student_masters.id')
      ->where('sci.allocation_group_id', $teachingGroupId)
      ->select('student_masters.*')
      ->distinct();

    if ((int) ($course->id ?? 0) > 0) {
      $query->where('sci.course_id', (int) $course->id);
    }

    if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $query->where('sci.is_deleted', 0);
    }

    if (Schema::hasColumn('student_course_infos', 'deleted_at')) {
      $query->whereNull('sci.deleted_at');
    }

    $query->where(function ($q) {
      $q->whereNull('student_masters.is_left')
        ->orWhere('student_masters.is_left', '!=', 1);
    });

    return $query->orderBy('student_masters.roll_no')->get();
  }

  // ========================================================================
  // CANDIDATE STUDENTS
  // ========================================================================

  protected function getCandidateStudents(
    $course,
    array $context = []
  ): Collection {
    if ($this->hasTeachingGroupContext($context)) {
      return $this->getTeachingGroupStudents($course, $context);
    }

    $query = StudentMaster::query()
      ->join('student_course_infos as sci', 'sci.student_id', '=', 'student_masters.id')
      ->select('student_masters.*')
      ->distinct();

    $query->where(function ($q) {
      $q->whereNull('student_masters.is_left')
        ->orWhere('student_masters.is_left', '!=', 1);
    });

    if (Schema::hasColumn('student_masters', 'is_deleted')) {
      $query->where('student_masters.is_deleted', 0);
    }

    if ((int) ($course->id ?? 0) > 0) {
      $query->where('sci.course_id', (int) $course->id);
    }

    if ((int) ($course->semester_id ?? 0) > 0) {
      $query->where('sci.semester', (int) $course->semester_id);
    }

    if ((int) ($course->batch_id ?? 0) > 0) {
      $query->where('student_masters.batch', (int) $course->batch_id);
    }

    if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $query->where('sci.is_deleted', 0);
    }

    if (Schema::hasColumn('student_course_infos', 'deleted_at')) {
      $query->whereNull('sci.deleted_at');
    }

    return $query
      ->orderBy('student_masters.roll_no')
      ->get();
  }

  // ========================================================================
  // SUPPORT HELPERS
  // ========================================================================

  protected function hasTeachingGroupContext(array $context): bool
  {
    return (int) ($context['teaching_group_id'] ?? 0) > 0;
  }

  protected function isTeachingGroupSourceOrOverride(StudentRosterRuleMapping $rule, array $context): bool
  {
    $source = strtoupper(trim((string) ($rule->roster_source ?? '')));
    return $source === 'TEACHING_GROUP' || ($this->hasTeachingGroupContext($context) && (bool) ($rule->teaching_group_override ?? false));
  }

  protected function isStudentLeft(StudentMaster $student): bool
  {
    return (int) ($student->is_left ?? 0) === 1 || (int) ($student->where_is_left ?? 0) === 1;
  }

  protected function getComboMapForStudentProgram(int $studentProgramId): ?object
  {
    if ($studentProgramId <= 0 || !Schema::hasTable('std_prog_combo_maps')) {
      return null;
    }

    $query = DB::table('std_prog_combo_maps')
      ->where('student_program_id', $studentProgramId);

    if (Schema::hasColumn('std_prog_combo_maps', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    return $query->orderBy('id')->first(['combo_id_1', 'combo_id_2']);
  }

  protected function isSingleMajorPathway(?int $pathwayId): bool
  {
    if (!$pathwayId) {
      return false;
    }

    $name = (string) DB::table('academic_pathway_masters')->where('id', $pathwayId)->value('name');
    return stripos($name, 'single') !== false;
  }

  protected function persistDecision(StudentMaster $student, $course, array $decision): void
  {
    if (!Schema::hasTable('student_roster_rule_results')) {
      return;
    }

    try {
      DB::table('student_roster_rule_results')->insert([
        'rule_mapping_id' => (int) (($decision['rule']->id ?? 0) ?: 0) ?: null,
        'student_id' => (int) $student->id,
        'subject_course_id' => (int) ($course->id ?? 0) ?: null,
        'included' => (bool) ($decision['included'] ?? false),
        'decision' => (bool) ($decision['included'] ?? false) ? 'INCLUDED' : 'EXCLUDED',
        'reason_code' => (string) ($decision['reason_code'] ?? 'UNKNOWN'),
        'reason' => (string) ($decision['reason'] ?? ''),
        'diagnostic_data' => json_encode([
          'delivery_type' => $course->delivery_type ?? null,
          'selection_type' => $course->selection_type ?? null,
          'trace' => $decision['trace'] ?? [],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    } catch (\Throwable $e) {
      Log::warning('StudentRosterEngine decision persistence failed', [
        'student_id' => (int) $student->id,
        'course_id' => (int) ($course->id ?? 0),
        'error' => $e->getMessage(),
      ]);
    }
  }

  // ========================================================================
  // DEBUGGING
  // ========================================================================

  protected function debugDecision(
    StudentMaster $student,
    $course,
    ?StudentRosterRuleMapping $rule,
    bool $included,
    string $reasonCode,
    string $reason,
    array $extra = []
  ): void {
    Log::debug(
      'StudentRosterEngine decision',
      array_merge(
        [
          'student_id' => (int) $student->id,
          'course_id' => (int) ($course->id ?? 0),
          'included' => $included,
          'reason_code' => $reasonCode,
          'reason' => $reason,
          'rule_id' => $rule?->id,
          'rule_code' => $rule?->rule?->rule_code,
          'academic_pathway_id' => $this->getAcademicPathwayId($student),
          'degree_track_id' => $this->getDegreeTrackId($student),
          'student_batch_id' => (int) ($student->batch ?? 0),
          'student_semester_id' => $this->getStudentSemesterId($student, $course),
          'course_batch_id' => (int) ($course->batch_id ?? 0),
          'course_semester_id' => (int) ($course->semester_id ?? 0),
          'delivery_type' => (string) ($course->delivery_type ?? ''),
          'selection_type' => (string) ($course->selection_type ?? ''),
        ],
        $extra
      )
    );
  }
}

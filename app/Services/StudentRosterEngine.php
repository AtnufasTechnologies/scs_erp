<?php

namespace App\Services;

use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\StudentSpecialization;
use App\Models\TeachingAssignment;
use App\Models\TeachingGroupItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentRosterEngine
{
  public function getRoster(int $courseId, array $context = []): Collection
  {
    if ($courseId <= 0) {
      return collect();
    }

    $course = ProgramCourseMaster::query()->find($courseId, ['id', 'department']);
    if (!$course) {
      return collect();
    }

    $context = $this->normalizeContext($context);

    $groupRoster = $this->resolveTeachingGroupRoster($courseId, $course, $context);
    if ($groupRoster->isNotEmpty()) {
      return $groupRoster->values();
    }

    return $this->resolveNormalRoster($courseId, $course, $context)->values();
  }

  private function resolveNormalRoster(int $courseId, ProgramCourseMaster $course, array $context): Collection
  {
    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();

    $curriculumQuery = ProgramWiseSemesterCourse::query()->where('course_id', $courseId);

    if (!empty($context['batch_id'])) {
      $curriculumQuery->where('batch', (int) $context['batch_id']);
    }

    if (!empty($context['semester_id'])) {
      $curriculumQuery->where('semester', (int) $context['semester_id']);
    }

    if (Schema::hasColumn($curriculumTable, 'is_active')) {
      $curriculumQuery->where('is_active', 1);
    }

    $curriculumRows = $curriculumQuery->get([
      'id',
      'program_combo_refid',
      'batch',
      'semester',
      'course_id',
      'offering_dept',
      'academic_pathway_id',
      'degree_track_id',
      'course_type',
      'delivery_category',
      'specialization_mode',
      'specialization_master_id',
      'specialization_master_ids',
    ]);

    if ($curriculumRows->isEmpty()) {
      return collect();
    }

    $comboIds = $curriculumRows
      ->pluck('program_combo_refid')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($comboIds->isEmpty()) {
      return collect();
    }

    $comboRowsQuery = DB::table('subject_has_student_progams as shp')
      ->whereIn('shp.id', $comboIds->all())
      ->select([
        'shp.id',
        'shp.subject_id',
        'shp.student_program_id',
        'shp.batch_id',
        'shp.program_type',
      ]);

    if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
      $comboRowsQuery->whereNull('shp.deleted_at');
    }

    $comboRows = $comboRowsQuery->get();
    if ($comboRows->isEmpty()) {
      return collect();
    }

    $comboById = $comboRows->keyBy(fn($row) => (int) $row->id);
    $allowedProgramIds = $comboRows->pluck('student_program_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($allowedProgramIds->isEmpty()) {
      return collect();
    }

    $semesters = $curriculumRows->pluck('semester')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $isOpenChoiceStudentChoice = ($context['delivery_type'] ?? '') === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE
      && ($context['selection_type'] ?? '') === ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE;
    $hasContextSemester = (int) ($context['semester_id'] ?? 0) > 0;

    if ($isOpenChoiceStudentChoice && $hasContextSemester) {
      // For open-choice elective rows, source candidates from program-combination membership
      // so counts align with department combination dashboards.
      $studentQuery = DB::table('student_masters as sm')
        ->leftJoin('academic_pathway_masters as ap', 'ap.id', '=', 'sm.academic_pathway_id')
        ->leftJoin('degree_track_masters as dt', 'dt.id', '=', 'sm.degree_track_id')
        ->whereIn('sm.new_program_id', $allowedProgramIds->all())
        ->select([
          'sm.id as student_id',
          'sm.roll_no',
          'sm.register_no',
          'sm.first_name',
          'sm.last_name',
          'sm.new_program_id as program_id',
          'sm.batch as batch_id',
          'sm.academic_pathway_id',
          'sm.degree_track_id',
          'sm.academic_dept_id',
          'sm.selected_combo_id',
          'ap.name as academic_pathway_name',
          'dt.name as degree_track_name',
          DB::raw((int) $context['semester_id'] . ' as semester_id'),
        ]);
    } else {
      $studentQuery = DB::table('student_masters as sm')
        ->join('student_course_infos as sci', 'sci.student_id', '=', 'sm.id')
        ->leftJoin('academic_pathway_masters as ap', 'ap.id', '=', 'sm.academic_pathway_id')
        ->leftJoin('degree_track_masters as dt', 'dt.id', '=', 'sm.degree_track_id')
        ->where('sci.course_id', $courseId)
        ->whereIn('sm.new_program_id', $allowedProgramIds->all())
        ->whereIn('sci.semester', $semesters->all())
        ->select([
          'sm.id as student_id',
          'sm.roll_no',
          'sm.register_no',
          'sm.first_name',
          'sm.last_name',
          'sm.new_program_id as program_id',
          'sm.batch as batch_id',
          'sm.academic_pathway_id',
          'sm.degree_track_id',
          'sm.academic_dept_id',
          'sm.selected_combo_id',
          'ap.name as academic_pathway_name',
          'dt.name as degree_track_name',
          'sci.semester as semester_id',
        ]);
    }

    $this->applyActiveStudentGuards($studentQuery, 'sm');

    if (!empty($context['batch_id'])) {
      $studentQuery->where('sm.batch', (int) $context['batch_id']);
    }

    if (!empty($context['semester_id']) && !$isOpenChoiceStudentChoice) {
      $studentQuery->where('sci.semester', (int) $context['semester_id']);
    }

    if (!$isOpenChoiceStudentChoice && Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $studentQuery->where('sci.is_deleted', 0);
    }

    $students = $studentQuery
      ->orderBy('sm.roll_no')
      ->get()
      ->unique(fn($row) => (int) $row->student_id)
      ->values();

    if ($students->isEmpty()) {
      return collect();
    }

    $comboMapRows = DB::table('std_prog_combo_maps')
      ->whereIn('student_program_id', $allowedProgramIds->all())
      ->get(['student_program_id', 'combo_id_1', 'combo_id_2']);

    $comboMapByProgramId = $comboMapRows->keyBy(fn($row) => (int) $row->student_program_id);
    $studentSpecs = $this->loadStudentSpecializations($students);

    $forceDualMajor = $this->isContextDualMajor((string) ($context['program_type'] ?? ''));

    $resolved = $students->map(function ($student) use ($course, $context, $comboById, $comboMapByProgramId, $curriculumRows, $studentSpecs, $courseId, $forceDualMajor) {
      $programId = (int) ($student->program_id ?? 0);
      $batchId = (int) ($student->batch_id ?? 0);
      $semesterId = (int) ($student->semester_id ?? 0);

      $eligibleComboIds = $comboById
        ->filter(fn($combo) => (int) ($combo->student_program_id ?? 0) === $programId && (int) ($combo->batch_id ?? 0) === $batchId)
        ->keys()
        ->map(fn($id) => (int) $id)
        ->values();

      if ($eligibleComboIds->isEmpty()) {
        return null;
      }

      $matchingRows = $curriculumRows->filter(function ($row) use ($eligibleComboIds, $semesterId, $student, $studentSpecs, $forceDualMajor) {
        if (!$eligibleComboIds->contains((int) ($row->program_combo_refid ?? 0))) {
          return false;
        }

        if ((int) ($row->semester ?? 0) !== $semesterId) {
          return false;
        }

        if (!$forceDualMajor) {
          $rowPathwayId = (int) ($row->academic_pathway_id ?? 0);
          if ($rowPathwayId > 0 && $rowPathwayId !== (int) ($student->academic_pathway_id ?? 0)) {
            return false;
          }

          $rowTrackId = (int) ($row->degree_track_id ?? 0);
          if ($rowTrackId > 0 && $rowTrackId !== (int) ($student->degree_track_id ?? 0)) {
            return false;
          }
        }

        $rowDeliveryType = $this->normalizeDeliveryType((string) ($row->delivery_category ?? ''));
        $skipDualMajorSpecializationGate = $forceDualMajor && in_array($rowDeliveryType, [
          ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
          ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
        ], true);

        if (!$skipDualMajorSpecializationGate && !$this->passesSpecializationGate($row, (int) ($student->student_id ?? 0), $studentSpecs)) {
          return false;
        }

        return true;
      })->values();

      if ($matchingRows->isEmpty()) {
        return null;
      }

      $comboMap = $comboMapByProgramId->get($programId);
      $combo1 = (int) ($comboMap->combo_id_1 ?? 0);
      $combo2 = (int) ($comboMap->combo_id_2 ?? 0);
      $isDual = $forceDualMajor || $this->isDualPathway($student, $combo1, $combo2);
      $singleMajorSubjectId = $this->resolveSingleMajorSubjectId($student, $combo1, $combo2);

      foreach ($matchingRows as $row) {
        $deliveryType = $this->normalizeDeliveryType((string) ($row->delivery_category ?? ''));
        $selectionType = $this->normalizeSelectionType((string) ($row->course_type ?? ''));
        $offeringDept = (int) ($row->offering_dept ?? 0);

        if (!$this->isDeliveryRowEligible(
          $deliveryType,
          $selectionType,
          $isDual,
          $combo1,
          $combo2,
          $singleMajorSubjectId,
          $offeringDept,
          $course
        )) {
          continue;
        }

        return [
          'student_id' => (int) ($student->student_id ?? 0),
          'course_id' => $courseId,
          'program_id' => (int) ($student->program_id ?? 0),
          'batch_id' => (int) ($student->batch_id ?? 0),
          'semester_id' => (int) ($student->semester_id ?? 0),
          'academic_pathway_id' => (int) ($student->academic_pathway_id ?? 0),
          'academic_pathway' => (string) ($student->academic_pathway_name ?? ''),
          'degree_track_id' => (int) ($student->degree_track_id ?? 0),
          'degree_track' => (string) ($student->degree_track_name ?? ''),
          'specialization_id' => null,
          'delivery_type' => $deliveryType,
          'selection_type' => $selectionType,
          'teaching_group_id' => (int) ($context['teaching_group_id'] ?? 0),
          'teaching_assignment_id' => (int) ($context['teaching_assignment_id'] ?? 0),
          'roll_no' => (string) ($student->roll_no ?? ''),
          'register_no' => (string) ($student->register_no ?? ''),
          'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
        ];
      }

      return null;
    })->filter()->values();

    return $resolved
      ->unique(fn($row) => (int) ($row['student_id'] ?? 0))
      ->values();
  }

  private function resolveTeachingGroupRoster(int $courseId, ProgramCourseMaster $course, array $context): Collection
  {
    $teachingGroupId = (int) ($context['teaching_group_id'] ?? 0);
    if ($teachingGroupId <= 0) {
      return collect();
    }

    $subjectId = (int) ($context['subject_id'] ?? 0);

    $groupRows = TeachingGroupItem::query()
      ->when($subjectId > 0, fn($query) => $query->where('subject_id', $subjectId))
      ->where('course_id', $courseId)
      ->where('allocation_group_id', $teachingGroupId)
      ->when(Schema::hasColumn('teaching_group_items', 'deleted_at'), fn($query) => $query->whereNull('deleted_at'))
      ->get([
        'course_id',
        'batch_id',
        'semester_id',
        'student_program_id',
        'delivery_type',
      ]);

    if ($groupRows->isEmpty()) {
      return collect();
    }

    $rows = collect();
    foreach ($groupRows as $groupRow) {
      $groupCourseId = (int) ($groupRow->course_id ?? 0);
      $batchId = (int) ($groupRow->batch_id ?? 0);
      $semesterId = (int) ($groupRow->semester_id ?? 0);
      $programId = (int) ($groupRow->student_program_id ?? 0);

      if ($groupCourseId <= 0 || $batchId <= 0 || $semesterId <= 0) {
        continue;
      }

      $studentQuery = DB::table('student_masters as sm')
        ->join('student_course_infos as sci', 'sci.student_id', '=', 'sm.id')
        ->leftJoin('academic_pathway_masters as ap', 'ap.id', '=', 'sm.academic_pathway_id')
        ->leftJoin('degree_track_masters as dt', 'dt.id', '=', 'sm.degree_track_id')
        ->where('sm.batch', $batchId)
        ->where('sci.course_id', $groupCourseId)
        ->where('sci.semester', $semesterId)
        ->select([
          'sm.id as student_id',
          'sm.roll_no',
          'sm.register_no',
          'sm.first_name',
          'sm.last_name',
          'sm.new_program_id as program_id',
          'sm.batch as batch_id',
          'sm.academic_pathway_id',
          'sm.degree_track_id',
          'ap.name as academic_pathway_name',
          'dt.name as degree_track_name',
          'sci.semester as semester_id',
        ]);

      $this->applyActiveStudentGuards($studentQuery, 'sm');

      if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
        $studentQuery->where('sci.is_deleted', 0);
      }

      if (Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
        $studentQuery->where('sci.allocation_group_id', $teachingGroupId);
      }

      if ($programId > 0) {
        $studentQuery->where('sm.new_program_id', $programId);
      }

      $rows = $rows->concat($studentQuery->get()->map(function ($student) use ($courseId, $teachingGroupId, $context, $groupRow) {
        return [
          'student_id' => (int) ($student->student_id ?? 0),
          'course_id' => $courseId,
          'program_id' => (int) ($student->program_id ?? 0),
          'batch_id' => (int) ($student->batch_id ?? 0),
          'semester_id' => (int) ($student->semester_id ?? 0),
          'academic_pathway_id' => (int) ($student->academic_pathway_id ?? 0),
          'academic_pathway' => (string) ($student->academic_pathway_name ?? ''),
          'degree_track_id' => (int) ($student->degree_track_id ?? 0),
          'degree_track' => (string) ($student->degree_track_name ?? ''),
          'specialization_id' => null,
          'delivery_type' => $this->normalizeDeliveryType((string) ($groupRow->delivery_type ?? ($context['delivery_type'] ?? ''))),
          'selection_type' => (string) ($context['selection_type'] ?? ''),
          'teaching_group_id' => $teachingGroupId,
          'teaching_assignment_id' => (int) ($context['teaching_assignment_id'] ?? 0),
          'roll_no' => (string) ($student->roll_no ?? ''),
          'register_no' => (string) ($student->register_no ?? ''),
          'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
        ];
      }));
    }

    return $rows
      ->unique(fn($row) => (int) ($row['student_id'] ?? 0))
      ->values();
  }

  private function normalizeContext(array $context): array
  {
    $teachingAssignmentId = (int) ($context['teaching_assignment_id'] ?? 0);
    $teachingGroupId = (int) ($context['teaching_group_id'] ?? 0);

    if ($teachingGroupId <= 0 && $teachingAssignmentId > 0) {
      $assignment = TeachingAssignment::query()->find($teachingAssignmentId, ['id', 'allocation_group']);
      $teachingGroupId = (int) ($assignment->allocation_group ?? 0);
    }

    return [
      'subject_id' => (int) ($context['subject_id'] ?? 0),
      'batch_id' => (int) ($context['batch_id'] ?? 0),
      'semester_id' => (int) ($context['semester_id'] ?? 0),
      'program_type' => strtoupper(trim((string) ($context['program_type'] ?? ''))),
      'delivery_type' => $this->normalizeDeliveryType((string) ($context['delivery_type'] ?? '')),
      'selection_type' => $this->normalizeSelectionType((string) ($context['selection_type'] ?? '')),
      'teaching_group_id' => $teachingGroupId,
      'teaching_assignment_id' => $teachingAssignmentId,
    ];
  }

  private function normalizeDeliveryType(string $value): string
  {
    $normalized = strtoupper(trim($value));
    if ($normalized === '') {
      return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
    }

    $aliases = [
      'CORE-A' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'COREA' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'MAJOR_COMBO1' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'COMBO1F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'COMBO1-F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'COMBO1_F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'CORE-B' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'COREB' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'MAJOR_COMBO2' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'COMBO2F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'COMBO2-F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'COMBO2_F' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
    ];

    return $aliases[$normalized] ?? $normalized;
  }

  private function isContextDualMajor(string $programType): bool
  {
    $normalized = strtoupper(trim($programType));
    if ($normalized === '') {
      return false;
    }

    return str_contains($normalized, 'DUAL');
  }

  private function normalizeSelectionType(string $value): string
  {
    $normalized = strtoupper(trim($value));

    if (in_array($normalized, ['COMPULSORY', 'MANDATORY'], true)) {
      return ProgramWiseSemesterCourse::TYPE_AUTO;
    }

    if (in_array($normalized, ['ELECTIVE', 'OPTIONAL'], true)) {
      return ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE;
    }

    if (in_array($normalized, [
      ProgramWiseSemesterCourse::TYPE_AUTO,
      ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      ProgramWiseSemesterCourse::TYPE_DEPARTMENT_CHOICE,
    ], true)) {
      return $normalized;
    }

    return ProgramWiseSemesterCourse::TYPE_AUTO;
  }

  private function isDualPathway(object $student, int $combo1, int $combo2): bool
  {
    $pathwayId = (int) ($student->academic_pathway_id ?? 0);
    if ($pathwayId === 2) {
      return true;
    }
    if ($pathwayId === 1) {
      return false;
    }

    $pathwayName = strtoupper(trim((string) ($student->academic_pathway_name ?? '')));
    if ($pathwayName !== '') {
      if (str_contains($pathwayName, 'DUAL')) {
        return true;
      }
      if (str_contains($pathwayName, 'SINGLE')) {
        return false;
      }
    }

    return $combo1 > 0 && $combo2 > 0 && $combo1 !== $combo2;
  }

  private function resolveSingleMajorSubjectId(object $student, int $combo1, int $combo2): int
  {
    $selectedCombo = (int) ($student->selected_combo_id ?? 0);
    if ($selectedCombo > 0) {
      return $selectedCombo;
    }

    $academicDept = (int) ($student->academic_dept_id ?? 0);
    if ($academicDept > 0) {
      return $academicDept;
    }

    if ($combo1 > 0 && $combo2 <= 0) {
      return $combo1;
    }

    return 0;
  }

  private function isDeliveryRowEligible(
    string $deliveryType,
    string $selectionType,
    bool $isDual,
    int $combo1,
    int $combo2,
    int $singleMajorSubjectId,
    int $offeringDept,
    ProgramCourseMaster $course
  ): bool {
    if ($deliveryType === ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1) {
      if ($isDual) {
        // Dual-major students should not be rejected by COMBO1 delivery gating.
        return true;
      }

      return $combo1 > 0 && $singleMajorSubjectId > 0 && $singleMajorSubjectId === $combo1;
    }

    if ($deliveryType === ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2) {
      if ($isDual) {
        // Dual-major students should not be rejected by COMBO2 delivery gating.
        return true;
      }

      return $combo2 > 0 && $singleMajorSubjectId > 0 && $singleMajorSubjectId === $combo2;
    }

    if ($deliveryType === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE) {
      if ($selectionType === ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE) {
        if ($isDual && $this->violatesDualMajorMdcDepartmentConstraint($offeringDept, $combo1, $combo2, $course)) {
          return false;
        }

        return true;
      }

      return $this->isDeliveryRowEligible(
        ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
        ProgramWiseSemesterCourse::TYPE_AUTO,
        $isDual,
        $combo1,
        $combo2,
        $singleMajorSubjectId,
        $offeringDept,
        $course
      );
    }

    if ($deliveryType === ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON) {
      if ($selectionType === ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE) {
        return true;
      }

      return $this->isDeliveryRowEligible(
        ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
        ProgramWiseSemesterCourse::TYPE_AUTO,
        $isDual,
        $combo1,
        $combo2,
        $singleMajorSubjectId,
        $offeringDept,
        $course
      );
    }

    return true;
  }

  private function violatesDualMajorMdcDepartmentConstraint(int $offeringDept, int $combo1, int $combo2, ProgramCourseMaster $course): bool
  {
    if ($offeringDept <= 0) {
      $offeringDept = (int) ($course->department ?? 0);
    }

    if ($offeringDept <= 0) {
      return false;
    }

    return ($combo1 > 0 && $offeringDept === $combo1)
      || ($combo2 > 0 && $offeringDept === $combo2);
  }

  private function applyActiveStudentGuards(object $query, string $alias = 'sm'): void
  {
    $prefix = $alias !== '' ? $alias . '.' : '';

    if (Schema::hasColumn('student_masters', 'is_deleted')) {
      $query->where($prefix . 'is_deleted', 0);
    }

    if (Schema::hasColumn('student_masters', 'is_left')) {
      $query->where(function ($leftScope) use ($prefix) {
        $leftScope->whereNull($prefix . 'is_left')->orWhere($prefix . 'is_left', 0);
      });
    }

    if (Schema::hasColumn('student_masters', 'where_is_left')) {
      $query->where(function ($leftScope) use ($prefix) {
        $leftScope->whereNull($prefix . 'where_is_left')->orWhere($prefix . 'where_is_left', 0);
      });
    }
  }

  private function loadStudentSpecializations(Collection $students): array
  {
    if (!Schema::hasTable('student_specializations')) {
      return [];
    }

    $studentIds = $students->pluck('student_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($studentIds->isEmpty()) {
      return [];
    }

    $query = StudentSpecialization::query()
      ->whereIn('student_id', $studentIds->all())
      ->select(['student_id', 'subject_has_student_program_id', 'specialization_id', 'semester_id']);

    if (Schema::hasColumn('student_specializations', 'is_active')) {
      $query->where('is_active', 1);
    }

    if (Schema::hasColumn('student_specializations', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    $rows = $query->get();
    $lookup = [];

    foreach ($rows as $row) {
      $studentId = (int) ($row->student_id ?? 0);
      $comboId = (int) ($row->subject_has_student_program_id ?? 0);
      $specId = (int) ($row->specialization_id ?? 0);
      $semesterId = (int) ($row->semester_id ?? 0);

      if ($studentId <= 0 || $comboId <= 0 || $specId <= 0) {
        continue;
      }

      $lookup[$studentId] = $lookup[$studentId] ?? [];
      $lookup[$studentId][$comboId] = $lookup[$studentId][$comboId] ?? [];
      $lookup[$studentId][$comboId][] = [
        'specialization_id' => $specId,
        'semester_id' => $semesterId,
      ];
    }

    return $lookup;
  }

  private function passesSpecializationGate(object $curriculumRow, int $studentId, array $studentSpecs): bool
  {
    $mode = strtoupper(trim((string) ($curriculumRow->specialization_mode ?? 'COMMON')));
    if (in_array($mode, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAM_COMMON', 'ALL'], true)) {
      return true;
    }

    $requiredSpecIds = [];

    $singleId = (int) ($curriculumRow->specialization_master_id ?? 0);
    if ($singleId > 0) {
      $requiredSpecIds[] = $singleId;
    }

    $rawSpecIds = $curriculumRow->specialization_master_ids;
    if (is_string($rawSpecIds) && trim($rawSpecIds) !== '') {
      $decoded = json_decode($rawSpecIds, true);
      if (is_array($decoded)) {
        $rawSpecIds = $decoded;
      }
    }

    if (is_array($rawSpecIds)) {
      foreach ($rawSpecIds as $value) {
        $specId = (int) $value;
        if ($specId > 0) {
          $requiredSpecIds[] = $specId;
        }
      }
    }

    $requiredSpecIds = array_values(array_unique($requiredSpecIds));
    if (empty($requiredSpecIds)) {
      return true;
    }

    $comboId = (int) ($curriculumRow->program_combo_refid ?? 0);
    $studentComboSpecs = $studentSpecs[$studentId][$comboId] ?? [];
    if (empty($studentComboSpecs)) {
      return false;
    }

    $assignedSpecIds = array_values(array_unique(array_map(function ($row) {
      return (int) ($row['specialization_id'] ?? 0);
    }, $studentComboSpecs)));

    return !empty(array_intersect($requiredSpecIds, $assignedSpecIds));
  }
}

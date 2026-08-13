<?php

namespace App\Services;

use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\SubjectHasRoutine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceEligibilityService
{
  public function getEligibleStudents(
    SubjectHasRoutine $routine,
    int $courseId,
    int $semesterId,
    int $batchId,
    int $campusId,
    int $subjectId,
    bool $withProfile = false,
    ?int $studentId = null
  ): Collection {
    if ($courseId <= 0 || $semesterId <= 0 || $batchId <= 0 || $campusId <= 0 || $subjectId <= 0) {
      return collect();
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));
    if ($routineShift === '') {
      $routineShift = 'common';
    }

    $combineStudents = $this->shouldCombineAttendanceStudents($routine, $courseId);
    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($subjectId, $batchId, $campusId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', $subjectId)
          ->where('shp.batch_id', '=', $batchId);

        if (Schema::hasColumn('subject_has_student_progams', 'campus_id')) {
          $join->where('shp.campus_id', '=', $campusId);
        }

        if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
          $join->whereNull('shp.deleted_at');
        }
      })
      ->select('sm.id', 'sm.new_program_id', 'shp.id as program_combo_id')
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $batchId)
      ->where('sci.course_id', $courseId)
      ->where('sci.semester', $semesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    if ($studentId !== null && $studentId > 0) {
      $baseQuery->where('sm.id', (int) $studentId);
    }

    if ($withProfile) {
      $baseQuery->addSelect(
        'sm.roll_no',
        'sm.first_name',
        'sm.last_name',
        'sci.id as course_info_id',
        'sp.shift as program_shift',
        'sci.allocation_group_id'
      );

      if (Schema::hasColumn('student_masters', 'selected_combo_id')) {
        $baseQuery->addSelect('sm.selected_combo_id');
      }
    }

    if (!$combineStudents && $routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    $students = $baseQuery
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->get();

    if ($students->isEmpty()) {
      return collect();
    }

    if (Schema::hasTable('student_specializations')) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $comboIds = $students->pluck('program_combo_id')->filter(fn($v) => (int) $v > 0)->map(fn($v) => (int) $v)->unique()->values();

      if ($comboIds->isNotEmpty() && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        $curriculumQuery = DB::table($curriculumTable)
          ->whereIn('program_combo_refid', $comboIds->all())
          ->where('course_id', $courseId)
          ->where('semester', $semesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $curriculumQuery->where('batch', $batchId);
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

    if (!$combineStudents && $students->isNotEmpty()) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $majorDeliveryScope = $this->resolveMajorDeliveryScope($routine);
      $majorSubjectId = $subjectId;
      $useCombo2Scope = $majorDeliveryScope === ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
      $studentsBeforeComboFilter = $students;

      if (Schema::hasColumn($curriculumTable, 'program_combo_refid') && Schema::hasColumn($curriculumTable, 'delivery_category')) {
        $normalizedDeliveryExpr = "REPLACE(REPLACE(REPLACE(UPPER(TRIM(COALESCE(delivery_category, ''))), ' ', ''), '_', ''), '-', '')";
        $majorDeliveryAliases = $useCombo2Scope
          ? ['COMBO2', 'COREB', 'MAJORCOMBO2']
          : ['COMBO1', 'COREA', 'MAJORCOMBO1'];

        $comboProgramIdsQuery = DB::table($curriculumTable)
          ->where('course_id', $courseId)
          ->where('semester', $semesterId)
          ->whereRaw("{$normalizedDeliveryExpr} IN ('" . implode("','", $majorDeliveryAliases) . "')");

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $comboProgramIdsQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $comboProgramIdsQuery->where('batch', $batchId);
        }

        $comboProgramIds = $comboProgramIdsQuery
          ->pluck('program_combo_refid')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values();

        if ($comboProgramIds->isEmpty()) {
          if ($majorSubjectId > 0 && Schema::hasTable('std_prog_combo_maps')) {
            $fallbackStudentProgramIds = DB::table('std_prog_combo_maps')
              ->when($useCombo2Scope, function ($query) use ($majorSubjectId) {
                $query->where('combo_id_2', $majorSubjectId);
              }, function ($query) use ($majorSubjectId) {
                $query->where('combo_id_1', $majorSubjectId);
              })
              ->pluck('student_program_id')
              ->map(fn($id) => (int) $id)
              ->filter(fn($id) => $id > 0)
              ->unique()
              ->values()
              ->all();

            if (!empty($fallbackStudentProgramIds)) {
              $students = $students->filter(function ($row) use ($fallbackStudentProgramIds) {
                return in_array((int) ($row->new_program_id ?? 0), $fallbackStudentProgramIds, true);
              })->values();
            } else {
              $students = collect();
            }
          } else {
            $students = collect();
          }
        } else {
          $allowedProgramIds = $comboProgramIds->all();
          $allowedStudentProgramIdsQuery = DB::table('subject_has_student_progams')
            ->whereIn('id', $allowedProgramIds)
            ->where('batch_id', $batchId)
            ->where('campus_id', $campusId);

          if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
            $allowedStudentProgramIdsQuery->whereNull('deleted_at');
          }

          $allowedStudentProgramIds = $allowedStudentProgramIdsQuery
            ->pluck('student_program_id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

          if (empty($allowedStudentProgramIds)) {
            $allowedStudentProgramIdsQuery = DB::table('subject_has_student_progams')
              ->whereIn('id', $allowedProgramIds);

            if (Schema::hasColumn('subject_has_student_progams', 'deleted_at')) {
              $allowedStudentProgramIdsQuery->whereNull('deleted_at');
            }

            $allowedStudentProgramIds = $allowedStudentProgramIdsQuery
              ->pluck('student_program_id')
              ->map(fn($id) => (int) $id)
              ->filter(fn($id) => $id > 0)
              ->unique()
              ->values()
              ->all();
          }

          $students = $students->filter(function ($row) use ($allowedProgramIds, $allowedStudentProgramIds) {
            $comboId = (int) ($row->program_combo_id ?? 0);
            $studentProgramId = (int) ($row->new_program_id ?? 0);

            if ($comboId > 0 && in_array($comboId, $allowedProgramIds, true)) {
              return true;
            }

            return $studentProgramId > 0 && in_array($studentProgramId, $allowedStudentProgramIds, true);
          })->values();

          if ($students->isEmpty() && $majorSubjectId > 0 && Schema::hasTable('std_prog_combo_maps')) {
            $fallbackStudentProgramIds = DB::table('std_prog_combo_maps')
              ->when($useCombo2Scope, function ($query) use ($majorSubjectId) {
                $query->where('combo_id_2', $majorSubjectId);
              }, function ($query) use ($majorSubjectId) {
                $query->where('combo_id_1', $majorSubjectId);
              })
              ->pluck('student_program_id')
              ->map(fn($id) => (int) $id)
              ->filter(fn($id) => $id > 0)
              ->unique()
              ->values()
              ->all();

            if (!empty($fallbackStudentProgramIds)) {
              $students = $studentsBeforeComboFilter->filter(function ($row) use ($fallbackStudentProgramIds) {
                return in_array((int) ($row->new_program_id ?? 0), $fallbackStudentProgramIds, true);
              })->values();
            }
          }
        }
      }
    }

    return $students->values();
  }

  public function isStudentEligible(
    SubjectHasRoutine $routine,
    int $studentId,
    int $courseId,
    int $semesterId,
    int $batchId,
    int $campusId,
    int $subjectId
  ): bool {
    if ($studentId <= 0) {
      return false;
    }

    $students = $this->getEligibleStudents(
      $routine,
      $courseId,
      $semesterId,
      $batchId,
      $campusId,
      $subjectId,
      false,
      $studentId
    );

    return $students->pluck('id')->map(fn($id) => (int) $id)->contains($studentId);
  }

  private function isGroupTeachingRoutine(?SubjectHasRoutine $routine): bool
  {
    if (!$routine) {
      return false;
    }

    if ((int) ($routine->teaching_group_id ?? 0) > 0) {
      return true;
    }

    $deliveryType = strtoupper(trim((string) (
      $routine->teachingAssignment->delivery_type
      ?? $routine->teachingAllocation->delivery_type
      ?? ''
    )));

    return in_array($deliveryType, ['GROUP', 'GROUP_TEACHING', 'TEAM_TEACHING'], true);
  }

  private function isMdcCourseById(int $courseId): bool
  {
    if ($courseId <= 0) {
      return false;
    }

    $course = ProgramCourseMaster::query()
      ->with('coursetypemaster:id,title')
      ->find($courseId, ['id', 'course_type', 'course_code']);

    if (!$course) {
      return false;
    }

    $typeTitle = strtoupper(trim((string) (optional($course->coursetypemaster)->title ?? '')));
    $courseCode = strtoupper(trim((string) ($course->course_code ?? '')));

    return str_contains($typeTitle, 'MDC')
      || str_contains($typeTitle, 'OPEN CHOICE')
      || str_contains($courseCode, 'MDC');
  }

  private function shouldCombineAttendanceStudents(?SubjectHasRoutine $routine, int $courseId): bool
  {
    return $this->isGroupTeachingRoutine($routine) || $this->isMdcCourseById($courseId);
  }

  private function resolveMajorDeliveryScope(?SubjectHasRoutine $routine): string
  {
    $deliveryType = strtoupper(trim((string) (
      $routine?->teachingAssignment?->delivery_type
      ?? $routine?->teachingAllocation?->delivery_type
      ?? ''
    )));

    $normalized = str_replace([' ', '_', '-'], '', $deliveryType);

    return $normalized === 'COMBO2'
      ? ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2
      : ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
  }
}

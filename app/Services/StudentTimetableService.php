<?php

namespace App\Services;

use App\Models\ProgramWiseSemesterCourse;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\StudentSpecialization;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasStudentProgam;
use App\Models\TeachingAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class StudentTimetableService
{
  public static function generate(int $studentId): Collection
  {
    if ($studentId <= 0) {
      return collect();
    }

    $student = StudentMaster::query()
      ->with('activeSemesterConfig:id,student_id,semester_id,current_semester')
      ->find($studentId);

    if (!$student) {
      return collect();
    }

    $programId = (int) ($student->new_program_id ?? 0);
    $batchId = (int) ($student->batch ?? 0);
    $semesterId = (int) ($student->activeSemesterConfig?->semester_id ?? 0);
    $pathwayId = (int) ($student->academic_pathway_id ?? 0);
    $degreeTrackId = (int) ($student->degree_track_id ?? 0);

    if ($semesterId <= 0) {
      $semesterId = (int) StudentCourseInfo::query()
        ->where('student_id', $studentId)
        ->whereNotNull('semester')
        ->orderByDesc('id')
        ->value('semester');
    }

    if ($programId <= 0 || $batchId <= 0 || $semesterId <= 0) {
      return collect();
    }

    $programCombinationId = (int) SubjectHasStudentProgam::query()
      ->where('student_program_id', $programId)
      ->where('batch_id', $batchId)
      ->orderByDesc('id')
      ->value('id');

    $specializationId = self::resolveSpecializationId($student, $programCombinationId, $semesterId);

    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    $curriculumGroupColumn = self::firstExistingColumn($curriculumTable, ['allocation_group', 'group', 'group_no']);
    $curriculumSpecColumn = self::firstExistingColumn($curriculumTable, ['specialization_master_id', 'specialization_id']);

    $curriculumQuery = ProgramWiseSemesterCourse::query()
      ->with('programinfo:id,course_code,course_title')
      ->where('batch', $batchId)
      ->where('semester', $semesterId);

    if (Schema::hasColumn($curriculumTable, 'program_id')) {
      $curriculumQuery->where('program_id', $programId);
    } elseif (Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
      if ($programCombinationId <= 0) {
        return collect();
      }
      $curriculumQuery->where('program_combo_refid', $programCombinationId);
    }

    if (Schema::hasColumn($curriculumTable, 'academic_pathway_id')) {
      if ($pathwayId > 0) {
        $curriculumQuery->where('academic_pathway_id', $pathwayId);
      } else {
        $curriculumQuery->whereNull('academic_pathway_id');
      }
    }

    if (Schema::hasColumn($curriculumTable, 'degree_track_id')) {
      if ($degreeTrackId > 0) {
        $curriculumQuery->where('degree_track_id', $degreeTrackId);
      } else {
        $curriculumQuery->whereNull('degree_track_id');
      }
    }

    if (Schema::hasColumn($curriculumTable, 'is_active')) {
      $curriculumQuery->where('is_active', 1);
    }

    if ($specializationId > 0) {
      $hasSpecIdColumn = Schema::hasColumn($curriculumTable, 'specialization_master_id');
      $hasSpecIdsColumn = Schema::hasColumn($curriculumTable, 'specialization_master_ids');

      if ($hasSpecIdColumn || $hasSpecIdsColumn) {
        $curriculumQuery->where(function ($query) use ($specializationId, $hasSpecIdColumn, $hasSpecIdsColumn) {
          if ($hasSpecIdColumn) {
            $query->orWhere('specialization_master_id', $specializationId);
          }

          if ($hasSpecIdsColumn) {
            $query->orWhereJsonContains('specialization_master_ids', $specializationId);
          }
        });
      }
    }

    $curriculumSelect = ['id', 'course_id', 'delivery_category'];
    if ($curriculumGroupColumn) {
      $curriculumSelect[] = $curriculumGroupColumn;
    }
    if ($curriculumSpecColumn) {
      $curriculumSelect[] = $curriculumSpecColumn;
    }

    $curriculumRows = $curriculumQuery->get($curriculumSelect);
    if ($curriculumRows->isEmpty()) {
      return collect();
    }

    $curriculumFilters = $curriculumRows
      ->map(function ($row) use ($curriculumGroupColumn, $curriculumSpecColumn) {
        $deliveryType = self::normalizeDeliveryType((string) ($row->delivery_category ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON));

        $groupValue = null;
        if ($curriculumGroupColumn) {
          $rawGroup = $row->{$curriculumGroupColumn};
          $groupValue = is_null($rawGroup) || $rawGroup === '' ? null : (int) $rawGroup;
        }

        $specValue = null;
        if ($curriculumSpecColumn) {
          $rawSpec = $row->{$curriculumSpecColumn};
          $specValue = is_null($rawSpec) || $rawSpec === '' ? null : (int) $rawSpec;
        }

        return [
          'course_id' => (int) ($row->course_id ?? 0),
          'delivery_type' => $deliveryType,
          'group' => $groupValue,
          'specialization_id' => $specValue,
          'course_code' => (string) ($row->programinfo?->course_code ?? ''),
          'course_title' => (string) ($row->programinfo?->course_title ?? ''),
        ];
      })
      ->filter(fn($row) => $row['course_id'] > 0)
      ->values();

    if ($curriculumFilters->isEmpty()) {
      return collect();
    }

    $assignmentTable = (new TeachingAssignment())->getTable();
    $assignmentGroupColumn = self::firstExistingColumn($assignmentTable, ['allocation_group', 'group', 'group_no']);
    $assignmentSpecColumn = self::firstExistingColumn($assignmentTable, ['specialization_master_id', 'specialization_id']);

    $assignments = TeachingAssignment::query()
      ->with([
        'course:id,course_code,course_title',
        'faculty:id,FIRST_NAME,LAST_NAME',
      ])
      ->where('is_active', 1)
      ->whereIn('course_id', $curriculumFilters->pluck('course_id')->unique()->values()->all())
      ->get();

    $matchedAssignments = $assignments
      ->filter(function ($assignment) use ($curriculumFilters, $assignmentGroupColumn, $assignmentSpecColumn) {
        $courseId = (int) ($assignment->course_id ?? 0);
        $deliveryType = self::normalizeDeliveryType((string) ($assignment->delivery_type ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON));

        $assignmentGroup = null;
        if ($assignmentGroupColumn) {
          $rawGroup = $assignment->{$assignmentGroupColumn};
          $assignmentGroup = is_null($rawGroup) || $rawGroup === '' ? null : (int) $rawGroup;
        }

        $assignmentSpec = null;
        if ($assignmentSpecColumn) {
          $rawSpec = $assignment->{$assignmentSpecColumn};
          $assignmentSpec = is_null($rawSpec) || $rawSpec === '' ? null : (int) $rawSpec;
        }

        return $curriculumFilters->contains(function ($filter) use ($courseId, $deliveryType, $assignmentGroup, $assignmentSpec) {
          if ($filter['course_id'] !== $courseId) {
            return false;
          }

          if ($filter['delivery_type'] !== $deliveryType) {
            return false;
          }

          if (!is_null($filter['group'])) {
            if (is_null($assignmentGroup) || (int) $filter['group'] !== (int) $assignmentGroup) {
              return false;
            }
          }

          if (!is_null($filter['specialization_id'])) {
            if (is_null($assignmentSpec) || (int) $filter['specialization_id'] !== (int) $assignmentSpec) {
              return false;
            }
          }

          return true;
        });
      })
      ->values();

    if ($matchedAssignments->isEmpty()) {
      return collect();
    }

    $assignmentById = $matchedAssignments->keyBy('id');
    $facultyIds = $matchedAssignments->pluck('faculty_id')->filter()->map(fn($id) => (int) $id)->unique()->values();
    $courseIds = $matchedAssignments->pluck('course_id')->filter()->map(fn($id) => (int) $id)->unique()->values();

    $subjectCourseMap = SubjectCourseMaster::query()
      ->whereIn('course_master_id', $courseIds->all())
      ->get(['id', 'course_master_id'])
      ->groupBy('course_master_id');

    $subjectCourseIds = $subjectCourseMap
      ->flatten(1)
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $routineTable = (new SubjectHasRoutine())->getTable();
    $hasTeachingAssignmentId = Schema::hasColumn($routineTable, 'teaching_assignment_id');
    $hasTeachingAllocationId = Schema::hasColumn($routineTable, 'teaching_allocation_id');
    $routineDeliveryColumn = self::firstExistingColumn($routineTable, ['delivery_type', 'delivery']);
    $routineGroupColumn = self::firstExistingColumn($routineTable, ['allocation_group', 'group', 'group_no']);

    $routineQuery = SubjectHasRoutine::query()
      ->where('batch_id', $batchId)
      ->where(function ($query) use (
        $matchedAssignments,
        $facultyIds,
        $subjectCourseIds,
        $hasTeachingAssignmentId,
        $hasTeachingAllocationId
      ) {
        $assignmentIds = $matchedAssignments
          ->pluck('id')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values();

        $matchedByAssignment = false;

        if ($hasTeachingAssignmentId && $assignmentIds->isNotEmpty()) {
          $query->whereIn('teaching_assignment_id', $assignmentIds->all());
          $matchedByAssignment = true;
        }

        if ($hasTeachingAllocationId && $assignmentIds->isNotEmpty()) {
          if ($matchedByAssignment) {
            $query->orWhereIn('teaching_allocation_id', $assignmentIds->all());
          } else {
            $query->whereIn('teaching_allocation_id', $assignmentIds->all());
            $matchedByAssignment = true;
          }
        }

        if ($subjectCourseIds->isNotEmpty() && $facultyIds->isNotEmpty()) {
          $query->orWhere(function ($fallback) use ($subjectCourseIds, $facultyIds, $hasTeachingAssignmentId, $hasTeachingAllocationId) {
            if ($hasTeachingAssignmentId) {
              $fallback->whereNull('teaching_assignment_id');
            }

            if ($hasTeachingAllocationId) {
              $fallback->whereNull('teaching_allocation_id');
            }

            $fallback->whereIn('subject_course_id', $subjectCourseIds->all())
              ->whereIn('faculty_id', $facultyIds->all());
          });
        }
      })
      ->with([
        'weekdaymaster:id,title',
        'hourmaster:id,hour_no,name',
        'lecturehallmaster:id,title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'subjectCourse:id,course_master_id,subject_id',
        'subjectCourse.courseMaster:id,course_code,course_title',
        'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAssignment.course:id,course_code,course_title',
        'teachingAssignment.faculty:id,FIRST_NAME,LAST_NAME',
        'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAllocation.course:id,course_code,course_title',
        'teachingAllocation.faculty:id,FIRST_NAME,LAST_NAME',
        'syllabus:id,course_id',
        'syllabus.coursemaster:id,course_code,course_title',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get();

    $assignmentsByCourse = $matchedAssignments->groupBy('course_id');

    $rows = $routineQuery
      ->map(function ($routine) use ($assignmentById, $assignmentsByCourse, $assignmentGroupColumn, $routineDeliveryColumn, $routineGroupColumn) {
        $assignment = null;

        if (!empty($routine->teaching_assignment_id)) {
          $assignment = $assignmentById->get((int) $routine->teaching_assignment_id);
        }

        if (!$assignment && !empty($routine->teaching_allocation_id)) {
          $assignment = $assignmentById->get((int) $routine->teaching_allocation_id);
        }

        $resolvedCourse = $assignment?->course
          ?? $routine->subjectCourse?->courseMaster
          ?? $routine->syllabus?->coursemaster;

        if (!$assignment && !empty($routine->subjectCourse?->course_master_id)) {
          $candidateAssignments = $assignmentsByCourse->get((int) $routine->subjectCourse->course_master_id, collect());

          if ($routineDeliveryColumn) {
            $routineDeliveryType = self::normalizeDeliveryType((string) ($routine->{$routineDeliveryColumn} ?? ''));
            if ($routineDeliveryType !== '') {
              $candidateAssignments = $candidateAssignments->filter(function ($item) use ($routineDeliveryType) {
                return self::normalizeDeliveryType((string) ($item->delivery_type ?? '')) === $routineDeliveryType;
              })->values();
            }
          }

          if ($routineGroupColumn && $assignmentGroupColumn) {
            $rawRoutineGroup = $routine->{$routineGroupColumn};
            $routineGroup = is_null($rawRoutineGroup) || $rawRoutineGroup === '' ? null : (int) $rawRoutineGroup;
            if (!is_null($routineGroup)) {
              $candidateAssignments = $candidateAssignments->filter(function ($item) use ($routineGroup, $assignmentGroupColumn) {
                $rawAssignmentGroup = $item->{$assignmentGroupColumn} ?? null;
                $assignmentGroup = is_null($rawAssignmentGroup) || $rawAssignmentGroup === '' ? null : (int) $rawAssignmentGroup;
                return !is_null($assignmentGroup) && $assignmentGroup === $routineGroup;
              })->values();
            }
          }

          if (!empty($routine->faculty_id)) {
            $assignment = $candidateAssignments->first(fn($item) => (int) $item->faculty_id === (int) $routine->faculty_id);
          }

          if (!$assignment) {
            $assignment = $candidateAssignments->first();
          }
        }

        $courseCode = (string) ($assignment?->course?->course_code ?? $resolvedCourse?->course_code ?? '');
        $courseTitle = (string) ($assignment?->course?->course_title ?? $resolvedCourse?->course_title ?? '');

        $facultyModel = $assignment?->faculty ?? $routine->faculty;
        $facultyName = trim((string) ($facultyModel?->FIRST_NAME ?? '') . ' ' . (string) ($facultyModel?->LAST_NAME ?? ''));

        $deliveryType = self::normalizeDeliveryType((string) ($assignment?->delivery_type ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON));

        $groupValue = null;
        if ($assignment && $assignmentGroupColumn) {
          $rawGroup = $assignment->{$assignmentGroupColumn};
          $groupValue = is_null($rawGroup) || $rawGroup === '' ? null : (int) $rawGroup;
        }

        $room = trim((string) ($assignment?->room ?? ''));
        if ($room === '') {
          $room = trim((string) ($routine->lecturehallmaster?->title ?? ''));
        }

        $hourLabel = (string) ($routine->hourmaster?->title ?? $routine->hourmaster?->name ?? '');

        return [
          'weekday_id' => (int) ($routine->weekday_id ?? 0),
          'hour_order' => (int) ($routine->hourmaster?->hour_no ?? $routine->hour_id ?? 0),
          'weekday' => (string) ($routine->weekdaymaster?->title ?? ''),
          'hour' => $hourLabel,
          'course_code' => $courseCode,
          'course_title' => $courseTitle,
          'faculty' => $facultyName,
          'room' => $room,
          'delivery_type' => $deliveryType,
          'group' => $groupValue,
          'shift' => (string) ($routine->shift ?? ''),
        ];
      })
      ->filter(fn($row) => !empty($row['weekday']) && !empty($row['hour']))
      ->sortBy(fn($row) => sprintf('%02d-%03d', (int) $row['weekday_id'], (int) $row['hour_order']))
      ->values()
      ->map(function ($row) {
        unset($row['weekday_id'], $row['hour_order']);
        return $row;
      });

    return $rows;
  }

  private static function resolveSpecializationId(StudentMaster $student, int $programCombinationId, int $semesterId): int
  {
    $specializationId = 0;

    if (Schema::hasTable('student_specializations')) {
      $query = StudentSpecialization::query()
        ->where('student_id', (int) $student->id)
        ->orderByDesc('id');

      if ($programCombinationId > 0 && Schema::hasColumn('student_specializations', 'subject_has_student_program_id')) {
        $query->where('subject_has_student_program_id', $programCombinationId);
      }

      if ($semesterId > 0 && Schema::hasColumn('student_specializations', 'semester_id')) {
        $query->where(function ($builder) use ($semesterId) {
          $builder->whereNull('semester_id')->orWhere('semester_id', $semesterId);
        });
      }

      if (Schema::hasColumn('student_specializations', 'is_active')) {
        $query->where('is_active', 1);
      }

      $specializationId = (int) $query->value('specialization_id');
    }

    if ($specializationId > 0) {
      return $specializationId;
    }

    $studentTable = $student->getTable();
    if (Schema::hasColumn($studentTable, 'specialization_id')) {
      $specializationId = (int) ($student->specialization_id ?? 0);
    } elseif (Schema::hasColumn($studentTable, 'specialization_master_id')) {
      $specializationId = (int) ($student->specialization_master_id ?? 0);
    }

    return max(0, $specializationId);
  }

  private static function firstExistingColumn(string $table, array $candidates): ?string
  {
    foreach ($candidates as $column) {
      if (Schema::hasColumn($table, $column)) {
        return $column;
      }
    }

    return null;
  }

  private static function normalizeDeliveryType(string $value): string
  {
    $normalized = strtoupper(trim($value));
    if ($normalized === '') {
      return ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
    }

    $normalized = str_replace(['_', ' '], '-', $normalized);
    $normalized = preg_replace('/-+/', '-', $normalized) ?? $normalized;

    if ($normalized === 'COREA') {
      return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
    }

    if ($normalized === 'COREB') {
      return ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
    }

    return match ($normalized) {
      'CORE-A' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'CORE-B' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'COMMON' => ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      'MDC' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      default => $normalized,
    };
  }
}

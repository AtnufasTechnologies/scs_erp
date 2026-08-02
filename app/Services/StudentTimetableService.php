<?php

namespace App\Services;

use App\Models\ProgramWiseSemesterCourse;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\StudentSpecialization;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasStudentProgam;
use App\Models\TeachingAssignment;
use App\Models\ShiftMaster;
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

    if ($semesterId <= 0 && Schema::hasTable('student_specializations') && Schema::hasColumn('student_specializations', 'semester_id')) {
      $specSemesterQuery = StudentSpecialization::query()
        ->where('student_id', $studentId)
        ->whereNotNull('semester_id')
        ->orderByDesc('id');

      if (Schema::hasColumn('student_specializations', 'is_active')) {
        $specSemesterQuery->where('is_active', 1);
      }

      $semesterId = (int) $specSemesterQuery->value('semester_id');
    }

    if ($programId <= 0 || $batchId <= 0 || $semesterId <= 0) {
      return collect();
    }

    $programCombinationQuery = SubjectHasStudentProgam::query()
      ->where('student_program_id', $programId)
      ->where('batch_id', $batchId);

    if (Schema::hasColumn('subject_has_student_progams', 'campus_id')) {
      $campusId = (int) ($student->campus_id ?? 0);
      if ($campusId > 0) {
        $programCombinationQuery->where('campus_id', $campusId);
      }
    }

    $combinationSelect = ['id'];
    if (Schema::hasColumn('subject_has_student_progams', 'shift')) {
      $combinationSelect[] = 'shift';
    }
    if (Schema::hasColumn('subject_has_student_progams', 'shift_id')) {
      $combinationSelect[] = 'shift_id';
    }

    $programCombination = $programCombinationQuery
      ->orderByDesc('id')
      ->first($combinationSelect);

    if (!$programCombination) {
      $programCombination = SubjectHasStudentProgam::query()
        ->where('student_program_id', $programId)
        ->where('batch_id', $batchId)
        ->orderByDesc('id')
        ->first($combinationSelect);
    }

    // If student_specializations stores the exact program-combination linkage,
    // prefer that row to avoid selecting a wrong shift/combo when multiple rows exist.
    if (Schema::hasTable('student_specializations') && Schema::hasColumn('student_specializations', 'subject_has_student_program_id')) {
      $specComboQuery = StudentSpecialization::query()
        ->where('student_id', $studentId)
        ->whereNotNull('subject_has_student_program_id')
        ->where('subject_has_student_program_id', '>', 0)
        ->orderByDesc('id');

      if (Schema::hasColumn('student_specializations', 'is_active')) {
        $specComboQuery->where('is_active', 1);
      }

      if ($semesterId > 0 && Schema::hasColumn('student_specializations', 'semester_id')) {
        $specComboQuery->where(function ($query) use ($semesterId) {
          $query->whereNull('semester_id')->orWhere('semester_id', $semesterId);
        });
      }

      $specializationComboId = (int) $specComboQuery->value('subject_has_student_program_id');
      if ($specializationComboId > 0) {
        $specializationCombo = SubjectHasStudentProgam::query()->where('id', $specializationComboId)->first($combinationSelect);
        if ($specializationCombo) {
          $programCombination = $specializationCombo;
        }
      }
    }

    $programCombinationId = (int) ($programCombination->id ?? 0);

    $specializationId = self::resolveSpecializationId($student, $programCombinationId, $semesterId);

    $fallbackProgramShift = '';
    if (Schema::hasColumn('student_programs', 'shift')) {
      $fallbackProgramShift = (string) StudentProgram::query()
        ->where('id', $programId)
        ->value('shift');
    }

    $programShiftFilter = self::resolveProgramShiftFilter(
      Schema::hasColumn('subject_has_student_progams', 'shift_id') ? (int) ($programCombination->shift_id ?? 0) : 0,
      Schema::hasColumn('subject_has_student_progams', 'shift') ? (string) ($programCombination->shift ?? '') : '',
      $fallbackProgramShift
    );
    $programShiftTokens = (array) ($programShiftFilter['tokens'] ?? []);
    $allowBlankShift = (bool) ($programShiftFilter['allow_blank'] ?? false);

    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    $curriculumGroupColumn = self::firstExistingColumn($curriculumTable, ['allocation_group', 'group', 'group_no']);
    $curriculumSpecColumn = self::firstExistingColumn($curriculumTable, ['specialization_master_id', 'specialization_id']);
    $curriculumSpecIdsColumn = Schema::hasColumn($curriculumTable, 'specialization_master_ids') ? 'specialization_master_ids' : null;
    $curriculumSpecModeColumn = Schema::hasColumn($curriculumTable, 'specialization_mode') ? 'specialization_mode' : null;
    $curriculumPathwayColumn = Schema::hasColumn($curriculumTable, 'academic_pathway_id') ? 'academic_pathway_id' : null;
    $curriculumDegreeTrackColumn = Schema::hasColumn($curriculumTable, 'degree_track_id') ? 'degree_track_id' : null;

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
        $curriculumQuery->where(function ($query) use ($pathwayId) {
          $query->where('academic_pathway_id', $pathwayId)
            ->orWhereNull('academic_pathway_id')
            ->orWhere('academic_pathway_id', 0);
        });
      } else {
        $curriculumQuery->where(function ($query) {
          $query->whereNull('academic_pathway_id')
            ->orWhere('academic_pathway_id', 0);
        });
      }
    }

    if (Schema::hasColumn($curriculumTable, 'degree_track_id')) {
      if ($degreeTrackId > 0) {
        $curriculumQuery->where(function ($query) use ($degreeTrackId) {
          $query->where('degree_track_id', $degreeTrackId)
            ->orWhereNull('degree_track_id')
            ->orWhere('degree_track_id', 0);
        });
      } else {
        $curriculumQuery->where(function ($query) {
          $query->whereNull('degree_track_id')
            ->orWhere('degree_track_id', 0);
        });
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
          $applied = false;

          if ($hasSpecIdColumn) {
            $query->where(function ($builder) use ($specializationId) {
              $builder->whereNull('specialization_master_id')
                ->orWhere('specialization_master_id', 0)
                ->orWhere('specialization_master_id', $specializationId);
            });
            $applied = true;
          }

          if ($hasSpecIdsColumn) {
            $specIdsMatcher = function ($builder) use ($specializationId) {
              $builder->whereNull('specialization_master_ids')
                ->orWhere('specialization_master_ids', '')
                ->orWhere('specialization_master_ids', '[]')
                ->orWhereJsonContains('specialization_master_ids', $specializationId)
                ->orWhereJsonContains('specialization_master_ids', (string) $specializationId);
            };

            if ($applied) {
              $query->orWhere($specIdsMatcher);
            } else {
              $query->where($specIdsMatcher);
            }
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
    if ($curriculumSpecIdsColumn) {
      $curriculumSelect[] = $curriculumSpecIdsColumn;
    }
    if ($curriculumSpecModeColumn) {
      $curriculumSelect[] = $curriculumSpecModeColumn;
    }
    if ($curriculumPathwayColumn) {
      $curriculumSelect[] = $curriculumPathwayColumn;
    }
    if ($curriculumDegreeTrackColumn) {
      $curriculumSelect[] = $curriculumDegreeTrackColumn;
    }

    $curriculumRows = $curriculumQuery->get($curriculumSelect);
    if ($curriculumRows->isEmpty()) {
      // Fallback: if pathway/track-restricted lookup returns nothing,
      // retry with base program+batch+semester scope to avoid empty timetable.
      $fallbackQuery = ProgramWiseSemesterCourse::query()
        ->with('programinfo:id,course_code,course_title')
        ->where('batch', $batchId)
        ->where('semester', $semesterId);

      if (Schema::hasColumn($curriculumTable, 'program_id')) {
        $fallbackQuery->where('program_id', $programId);
      } elseif (Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        if ($programCombinationId <= 0) {
          return collect();
        }
        $fallbackQuery->where('program_combo_refid', $programCombinationId);
      }

      if (Schema::hasColumn($curriculumTable, 'is_active')) {
        $fallbackQuery->where('is_active', 1);
      }

      if ($specializationId > 0) {
        $hasSpecIdColumn = Schema::hasColumn($curriculumTable, 'specialization_master_id');
        $hasSpecIdsColumn = Schema::hasColumn($curriculumTable, 'specialization_master_ids');

        if ($hasSpecIdColumn || $hasSpecIdsColumn) {
          $fallbackQuery->where(function ($query) use ($specializationId, $hasSpecIdColumn, $hasSpecIdsColumn) {
            $applied = false;

            if ($hasSpecIdColumn) {
              $query->where(function ($builder) use ($specializationId) {
                $builder->whereNull('specialization_master_id')
                  ->orWhere('specialization_master_id', 0)
                  ->orWhere('specialization_master_id', $specializationId);
              });
              $applied = true;
            }

            if ($hasSpecIdsColumn) {
              $specIdsMatcher = function ($builder) use ($specializationId) {
                $builder->whereNull('specialization_master_ids')
                  ->orWhere('specialization_master_ids', '')
                  ->orWhere('specialization_master_ids', '[]')
                  ->orWhereJsonContains('specialization_master_ids', $specializationId)
                  ->orWhereJsonContains('specialization_master_ids', (string) $specializationId);
              };

              if ($applied) {
                $query->orWhere($specIdsMatcher);
              } else {
                $query->where($specIdsMatcher);
              }
            }
          });
        }
      }

      $curriculumRows = $fallbackQuery->get($curriculumSelect);
      if ($curriculumRows->isEmpty()) {
        return collect();
      }
    }

    $curriculumRows = $curriculumRows
      ->filter(function ($row) use ($pathwayId, $degreeTrackId, $curriculumPathwayColumn, $curriculumDegreeTrackColumn) {
        return self::isCurriculumApplicableForStudentPathwayAndTrack(
          $row,
          $pathwayId,
          $degreeTrackId,
          $curriculumPathwayColumn,
          $curriculumDegreeTrackColumn
        );
      })
      ->values();

    if ($curriculumRows->isEmpty()) {
      return collect();
    }

    $curriculumFilters = $curriculumRows
      ->filter(function ($row) use ($specializationId, $curriculumSpecColumn, $curriculumSpecIdsColumn, $curriculumSpecModeColumn) {
        return self::isCurriculumApplicableForStudentSpecialization(
          $row,
          $specializationId,
          $curriculumSpecColumn,
          $curriculumSpecIdsColumn,
          $curriculumSpecModeColumn
        );
      })
      ->map(function ($row) use ($curriculumGroupColumn, $curriculumSpecColumn, $curriculumSpecIdsColumn, $curriculumSpecModeColumn) {
        $deliveryType = self::normalizeDeliveryType((string) ($row->delivery_category ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON));
        $specializationMode = self::normalizeSpecializationMode((string) ($curriculumSpecModeColumn ? ($row->{$curriculumSpecModeColumn} ?? '') : ''));

        $specializationIds = self::extractSpecializationIds(
          $curriculumSpecIdsColumn ? ($row->{$curriculumSpecIdsColumn} ?? null) : null,
          $curriculumSpecColumn ? ($row->{$curriculumSpecColumn} ?? null) : null
        );

        $requiresSpecializationMatch = $specializationMode !== ''
          && !self::isCommonSpecializationMode($specializationMode)
          && !empty($specializationIds);

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
          'specialization_ids' => $specializationIds,
          'requires_specialization_match' => $requiresSpecializationMatch,
          'course_code' => (string) ($row->programinfo?->course_code ?? ''),
          'course_title' => (string) ($row->programinfo?->course_title ?? ''),
        ];
      })
      ->filter(fn($row) => $row['course_id'] > 0)
      ->values();

    if ($curriculumFilters->isEmpty()) {
      return collect();
    }

    $enrolledCourseQuery = StudentCourseInfo::query()
      ->where('student_id', $studentId)
      ->whereIn('course_id', $curriculumFilters->pluck('course_id')->unique()->values()->all())
      ->where(function ($query) use ($semesterId) {
        $query->where('semester', $semesterId)->orWhereNull('semester');
      });

    if (Schema::hasColumn('student_course_infos', 'is_deleted')) {
      $enrolledCourseQuery->where('is_deleted', 0);
    }

    $enrolledCourseRows = $enrolledCourseQuery
      ->orderByDesc('id')
      ->get(['course_id', 'semester', 'allocation_group_id']);

    if ($enrolledCourseRows->isEmpty()) {
      return collect();
    }

    $enrolledByCourse = $enrolledCourseRows
      ->groupBy(fn($row) => (int) ($row->course_id ?? 0))
      ->map(function ($rowsForCourse) use ($semesterId) {
        $bestRow = $rowsForCourse->first(function ($row) use ($semesterId) {
          return (int) ($row->semester ?? 0) === (int) $semesterId;
        });

        if (!$bestRow) {
          $bestRow = $rowsForCourse->first(function ($row) {
            return is_null($row->semester);
          });
        }

        if (!$bestRow) {
          $bestRow = $rowsForCourse->first();
        }

        $rawGroup = $bestRow?->allocation_group_id;

        return [
          'allocation_group_id' => is_null($rawGroup) || $rawGroup === '' ? null : (int) $rawGroup,
        ];
      });

    $effectiveCurriculumFilters = $curriculumFilters
      ->filter(function ($filter) use ($enrolledByCourse) {
        $courseId = (int) ($filter['course_id'] ?? 0);
        if ($courseId <= 0 || !$enrolledByCourse->has($courseId)) {
          return false;
        }

        $studentAllocationId = $enrolledByCourse->get($courseId)['allocation_group_id'] ?? null;
        $curriculumAllocationId = $filter['group'] ?? null;

        // Rule: allocation should match when curriculum row is allocation-specific.
        if (!is_null($curriculumAllocationId)) {
          return !is_null($studentAllocationId) && (int) $studentAllocationId === (int) $curriculumAllocationId;
        }

        return true;
      })
      ->values();

    if ($effectiveCurriculumFilters->isEmpty()) {
      return collect();
    }

    $effectiveCourseIds = $effectiveCurriculumFilters
      ->pluck('course_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $subjectCourseMap = SubjectCourseMaster::query()
      ->whereIn('course_master_id', $effectiveCourseIds->all())
      ->get(['id', 'course_master_id'])
      ->groupBy('course_master_id');

    $subjectCourseIds = $subjectCourseMap
      ->flatten(1)
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($subjectCourseIds->isEmpty()) {
      return collect();
    }

    $assignmentTable = (new TeachingAssignment())->getTable();
    $assignmentGroupColumn = self::firstExistingColumn($assignmentTable, ['allocation_group', 'group', 'group_no']);
    $assignmentSpecColumn = self::firstExistingColumn($assignmentTable, ['specialization_master_id', 'specialization_id']);

    $assignments = TeachingAssignment::query()
      ->with([
        'course:id,course_code,course_title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'coFacultyMembers:id,FIRST_NAME,LAST_NAME',
      ])
      ->where('is_active', 1)
      ->whereIn('course_id', $effectiveCourseIds->all())
      ->get();

    $assignmentById = $assignments->keyBy('id');

    $routineTable = (new SubjectHasRoutine())->getTable();
    $hasTeachingAssignmentId = Schema::hasColumn($routineTable, 'teaching_assignment_id');
    $hasTeachingAllocationId = Schema::hasColumn($routineTable, 'teaching_allocation_id');
    $routineDeliveryColumn = self::firstExistingColumn($routineTable, ['delivery_type', 'delivery']);
    $routineGroupColumn = self::firstExistingColumn($routineTable, ['allocation_group', 'group', 'group_no']);

    $assignmentIds = $assignments
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $routineQuery = SubjectHasRoutine::query()
      ->where('batch_id', $batchId)
      ->where(function ($query) use ($subjectCourseIds, $assignmentIds, $hasTeachingAssignmentId, $hasTeachingAllocationId) {
        $hasCondition = false;

        if ($subjectCourseIds->isNotEmpty()) {
          $query->whereIn('subject_course_id', $subjectCourseIds->all());
          $hasCondition = true;
        }

        if ($hasTeachingAssignmentId && $assignmentIds->isNotEmpty()) {
          if ($hasCondition) {
            $query->orWhereIn('teaching_assignment_id', $assignmentIds->all());
          } else {
            $query->whereIn('teaching_assignment_id', $assignmentIds->all());
            $hasCondition = true;
          }
        }

        if ($hasTeachingAllocationId && $assignmentIds->isNotEmpty()) {
          if ($hasCondition) {
            $query->orWhereIn('teaching_allocation_id', $assignmentIds->all());
          } else {
            $query->whereIn('teaching_allocation_id', $assignmentIds->all());
          }
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
        'teachingAssignment.coFacultyMembers:id,FIRST_NAME,LAST_NAME',
        'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAllocation.course:id,course_code,course_title',
        'teachingAllocation.faculty:id,FIRST_NAME,LAST_NAME',
        'syllabus:id,course_id',
        'syllabus.coursemaster:id,course_code,course_title',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get();

    $curriculumByCourse = $effectiveCurriculumFilters
      ->groupBy(fn($row) => (int) ($row['course_id'] ?? 0));

    $rows = $routineQuery
      ->map(function ($routine) use (
        $assignmentById,
        $routineDeliveryColumn,
        $routineGroupColumn,
        $assignmentGroupColumn,
        $curriculumByCourse,
        $programShiftTokens,
        $allowBlankShift
      ) {
        if (!self::matchesRoutineShift((string) ($routine->shift ?? ''), $programShiftTokens, $allowBlankShift)) {
          return null;
        }

        $assignment = null;

        if (!empty($routine->teaching_assignment_id)) {
          $assignment = $assignmentById->get((int) $routine->teaching_assignment_id);
        }

        if (!$assignment && !empty($routine->teaching_allocation_id)) {
          $assignment = $assignmentById->get((int) $routine->teaching_allocation_id);
        }

        $resolvedCourse = $assignment?->course
          ?? $routine->subjectCourse?->courseMaster
          ?? $routine->syllabus?->coursemaster
          ?? $routine->teachingAllocation?->course;

        $resolvedCourseId = (int) ($assignment?->course_id ?? $resolvedCourse?->id ?? 0);
        if ($resolvedCourseId <= 0) {
          return null;
        }

        $curriculumRowsForCourse = $curriculumByCourse->get($resolvedCourseId, collect());
        if ($curriculumRowsForCourse->isEmpty()) {
          return null;
        }

        $routineDelivery = '';
        if ($routineDeliveryColumn) {
          $routineDelivery = trim((string) ($routine->{$routineDeliveryColumn} ?? ''));
        }
        if ($routineDelivery === '') {
          $routineDelivery = trim((string) ($assignment?->delivery_type ?? $routine->teachingAllocation?->delivery_type ?? ''));
        }
        $routineDelivery = $routineDelivery === '' ? '' : self::normalizeDeliveryType($routineDelivery);

        $routineGroup = null;
        if ($routineGroupColumn) {
          $rawRoutineGroup = $routine->{$routineGroupColumn};
          $routineGroup = is_null($rawRoutineGroup) || $rawRoutineGroup === '' ? null : (int) $rawRoutineGroup;
        }
        if (is_null($routineGroup) && $assignment && $assignmentGroupColumn) {
          $rawAssignmentGroup = $assignment->{$assignmentGroupColumn} ?? null;
          $routineGroup = is_null($rawAssignmentGroup) || $rawAssignmentGroup === '' ? null : (int) $rawAssignmentGroup;
        }

        $matchedCurriculum = $curriculumRowsForCourse->first(function ($curr) use ($routineDelivery, $routineGroup) {
          $deliveryMatch = $routineDelivery === ''
            ? true
            : ((string) ($curr['delivery_type'] ?? '') === $routineDelivery);

          if (!$deliveryMatch) {
            return false;
          }

          if (!is_null($curr['group'] ?? null)) {
            return !is_null($routineGroup) && (int) $curr['group'] === (int) $routineGroup;
          }

          return true;
        });

        if (!$matchedCurriculum) {
          return null;
        }

        $courseCode = (string) ($resolvedCourse?->course_code ?? '');
        $courseTitle = (string) ($resolvedCourse?->course_title ?? '');

        $facultyModel = $assignment?->faculty ?? $routine->faculty ?? $routine->teachingAllocation?->faculty;
        $facultyName = trim((string) ($facultyModel?->FIRST_NAME ?? '') . ' ' . (string) ($facultyModel?->LAST_NAME ?? ''));
        $coFacultyNames = collect($assignment?->coFacultyMembers ?? [])
          ->map(fn($coFaculty) => trim((string) ($coFaculty->FIRST_NAME ?? '') . ' ' . (string) ($coFaculty->LAST_NAME ?? '')))
          ->filter()
          ->values();
        $facultyLabel = $facultyName !== '' ? $facultyName : '-';
        if ($coFacultyNames->isNotEmpty()) {
          $facultyLabel .= ' (Co-Faculty: ' . $coFacultyNames->implode(', ') . ')';
        }

        $room = trim((string) ($assignment?->room ?? $routine->teachingAllocation?->room ?? ''));
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
          'faculty' => $facultyLabel,
          'room' => $room,
          'delivery_type' => (string) ($matchedCurriculum['delivery_type'] ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON),
          'group' => $matchedCurriculum['group'] ?? null,
          'shift' => (string) ($routine->shift ?? ''),
        ];
      })
      ->filter(fn($row) => is_array($row) && !empty($row['weekday']) && !empty($row['hour']))
      ->unique(fn($row) => implode('|', [
        (string) ($row['weekday'] ?? ''),
        (string) ($row['hour'] ?? ''),
        (string) ($row['course_code'] ?? ''),
        (string) ($row['faculty'] ?? ''),
        (string) ($row['shift'] ?? ''),
      ]))
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
      $baseQuery = StudentSpecialization::query()
        ->where('student_id', (int) $student->id)
        ->orderByDesc('id');

      if ($programCombinationId > 0 && Schema::hasColumn('student_specializations', 'subject_has_student_program_id')) {
        $baseQuery->where('subject_has_student_program_id', $programCombinationId);
      }

      if (Schema::hasColumn('student_specializations', 'is_active')) {
        $baseQuery->where('is_active', 1);
      }

      $query = clone $baseQuery;

      if ($semesterId > 0 && Schema::hasColumn('student_specializations', 'semester_id')) {
        $query->where(function ($builder) use ($semesterId) {
          $builder->whereNull('semester_id')->orWhere('semester_id', $semesterId);
        });
      }

      $specializationId = (int) $query->value('specialization_id');

      // Fallback: if no specialization row matches current semester, use latest active row.
      if ($specializationId <= 0 && $semesterId > 0 && Schema::hasColumn('student_specializations', 'semester_id')) {
        $specializationId = (int) (clone $baseQuery)->value('specialization_id');
      }

      // Safety fallback: if combo-scoped specialization is missing, retry without combo constraint.
      if ($specializationId <= 0 && $programCombinationId > 0 && Schema::hasColumn('student_specializations', 'subject_has_student_program_id')) {
        $relaxedQuery = StudentSpecialization::query()
          ->where('student_id', (int) $student->id)
          ->orderByDesc('id');

        if (Schema::hasColumn('student_specializations', 'is_active')) {
          $relaxedQuery->where('is_active', 1);
        }

        if ($semesterId > 0 && Schema::hasColumn('student_specializations', 'semester_id')) {
          $relaxedQuery->where(function ($builder) use ($semesterId) {
            $builder->whereNull('semester_id')->orWhere('semester_id', $semesterId);
          });
        }

        $specializationId = (int) $relaxedQuery->value('specialization_id');
      }
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

  private static function normalizeSpecializationMode(string $value): string
  {
    return strtoupper(trim($value));
  }

  private static function isCommonSpecializationMode(string $mode): bool
  {
    return in_array($mode, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAM_COMMON', 'ALL'], true);
  }

  private static function extractSpecializationIds(mixed $rawIds, mixed $rawSingle): array
  {
    $ids = [];

    if (is_array($rawIds)) {
      $ids = $rawIds;
    } elseif (is_string($rawIds) && trim($rawIds) !== '') {
      $decoded = json_decode($rawIds, true);
      if (is_array($decoded)) {
        $ids = $decoded;
      }
    }

    $single = (int) ($rawSingle ?? 0);
    if ($single > 0) {
      $ids[] = $single;
    }

    return collect($ids)
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values()
      ->all();
  }

  private static function isCurriculumApplicableForStudentSpecialization(
    mixed $row,
    int $studentSpecializationId,
    ?string $specColumn,
    ?string $specIdsColumn,
    ?string $specModeColumn
  ): bool {
    $mode = self::normalizeSpecializationMode((string) ($specModeColumn ? ($row->{$specModeColumn} ?? '') : ''));

    if ($mode === '' || self::isCommonSpecializationMode($mode)) {
      return true;
    }

    $specIds = self::extractSpecializationIds(
      $specIdsColumn ? ($row->{$specIdsColumn} ?? null) : null,
      $specColumn ? ($row->{$specColumn} ?? null) : null
    );

    if (empty($specIds)) {
      return true;
    }

    if ($studentSpecializationId <= 0) {
      return false;
    }

    return in_array($studentSpecializationId, $specIds, true);
  }

  private static function isCurriculumApplicableForStudentPathwayAndTrack(
    mixed $row,
    int $studentPathwayId,
    int $studentDegreeTrackId,
    ?string $pathwayColumn,
    ?string $degreeTrackColumn
  ): bool {
    if (!self::matchesStudentCurriculumDimension($row, $studentPathwayId, $pathwayColumn)) {
      return false;
    }

    if (!self::matchesStudentCurriculumDimension($row, $studentDegreeTrackId, $degreeTrackColumn)) {
      return false;
    }

    return true;
  }

  private static function matchesStudentCurriculumDimension(mixed $row, int $studentValue, ?string $column): bool
  {
    if (!$column) {
      return true;
    }

    $curriculumValue = (int) ($row->{$column} ?? 0);

    if ($studentValue > 0) {
      return $curriculumValue === $studentValue;
    }

    return $curriculumValue <= 0;
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

  private static function resolveProgramShiftFilter(int $shiftId, ?string $programShiftSlug = null, ?string $fallbackShiftSlug = null): array
  {
    $tokens = [];
    $allowBlank = false;

    $programShiftToken = self::normalizeShiftToken((string) ($programShiftSlug ?? ''));
    if ($programShiftToken !== '') {
      $tokens[] = $programShiftToken;
    }

    if ($shiftId > 0) {
      $shift = ShiftMaster::query()->where('id', $shiftId)->first(['slug', 'title']);
      if ($shift) {
        $slugToken = self::normalizeShiftToken((string) ($shift->slug ?? ''));
        $titleToken = self::normalizeShiftToken((string) ($shift->title ?? ''));

        if ($slugToken !== '') {
          $tokens[] = $slugToken;
        }
        if ($titleToken !== '') {
          $tokens[] = $titleToken;
        }
      }
    }

    if (empty($tokens)) {
      $fallbackToken = self::normalizeShiftToken((string) ($fallbackShiftSlug ?? ''));
      if ($fallbackToken !== '') {
        $tokens[] = $fallbackToken;
      }
    }

    $tokens = collect($tokens)->filter()->unique()->values()->all();
    if (in_array('common', $tokens, true)) {
      $allowBlank = true;
    }

    return [
      'tokens' => $tokens,
      'allow_blank' => $allowBlank,
    ];
  }

  private static function matchesRoutineShift(string $routineShift, array $allowedShiftTokens, bool $allowBlank): bool
  {
    if (empty($allowedShiftTokens)) {
      return true;
    }

    $normalizedRoutineShift = self::normalizeShiftToken($routineShift);
    if ($normalizedRoutineShift === '') {
      return $allowBlank;
    }

    return in_array($normalizedRoutineShift, $allowedShiftTokens, true);
  }

  private static function normalizeShiftToken(string $value): string
  {
    $normalized = strtolower(trim($value));
    if ($normalized === '') {
      return '';
    }

    $normalized = str_replace(['_', '-'], ' ', $normalized);
    $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

    if (str_ends_with($normalized, ' shift')) {
      $normalized = trim(substr($normalized, 0, -6));
    }

    return str_replace(' ', '-', trim($normalized));
  }
}

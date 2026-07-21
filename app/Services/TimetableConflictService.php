<?php

namespace App\Services;

use App\Models\HourMaster;
use App\Models\ShiftMaster;
use App\Models\Subject;
use App\Models\SubjectHasStudentProgam;
use App\Models\SubjectHasRoutine;
use App\Models\TeachingAssignment;
use App\Models\ProgramWiseSemesterCourse;
use Illuminate\Support\Facades\Schema;

class TimetableConflictService
{
  public function validate(array $payload): array
  {
    $subjectId = (int) ($payload['subject_id'] ?? 0);
    $batchId = (int) ($payload['batch_id'] ?? 0);
    $semesterId = (int) ($payload['semester_id'] ?? 0);
    $programType = strtoupper(trim((string) ($payload['program_type'] ?? 'UG')));
    $weekdayId = (int) ($payload['weekday_id'] ?? 0);
    $hourInput = (int) ($payload['hour_id'] ?? 0);
    $shift = (string) ($payload['shift'] ?? 'common');
    $teachingAssignmentId = (int) ($payload['teaching_assignment_id'] ?? 0);
    $ignoreRoutineId = (int) ($payload['ignore_routine_id'] ?? 0);
    $draftEntries = is_array($payload['draft_entries'] ?? null) ? $payload['draft_entries'] : [];

    if ($subjectId <= 0 || $batchId <= 0 || $semesterId <= 0 || $weekdayId <= 0 || $hourInput <= 0 || $teachingAssignmentId <= 0) {
      return [
        'success' => false,
        'message' => 'Missing required fields for timetable conflict validation.',
      ];
    }

    if (!in_array($programType, ['UG', 'PG'], true)) {
      return [
        'success' => false,
        'message' => 'Invalid program type selected.',
      ];
    }

    $subject = Subject::query()->find($subjectId);
    if (!$subject) {
      return [
        'success' => false,
        'message' => 'Subject not found for timetable validation.',
      ];
    }

    $shiftValidation = $this->validateDepartmentShift($subject, $shift);
    if (!$shiftValidation['success']) {
      return $shiftValidation;
    }

    $shiftId = $this->resolveShiftId($shift);
    if ($shiftId <= 0) {
      return [
        'success' => false,
        'message' => 'Invalid shift selected.',
      ];
    }

    $incomingHour = $this->resolveHourSlot($hourInput, $shiftId);
    if (!$incomingHour) {
      return [
        'success' => false,
        'message' => 'Selected hour is not available for this shift.',
      ];
    }

    $incomingAssignment = TeachingAssignment::query()
      ->with([
        'course:id,course_code,course_title',
        'faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
      ])
      ->where('id', $teachingAssignmentId)
      ->where('is_active', 1)
      ->first();

    if (!$incomingAssignment) {
      return [
        'success' => false,
        'message' => 'Teaching assignment is not active or does not exist.',
      ];
    }

    $existingRoutines = SubjectHasRoutine::query()
      ->with([
        'hourmaster:id,hour_no,name,start_time,end_time',
        'teachingAssignment:id,subject_id,course_id,faculty_id,allocation_group,delivery_type,room',
        'teachingAssignment.course:id,course_code,course_title',
        'teachingAssignment.faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
        'syllabus.coursemaster:id,course_code,course_title',
      ])
      ->when($ignoreRoutineId > 0, fn($q) => $q->where('id', '!=', $ignoreRoutineId))
      ->when(
        Schema::hasColumn('subject_has_routines', 'program_type'),
        fn($q) => $q->where('program_type', $programType)
      )
      ->get();

    $baseProgramCombinationQuery = SubjectHasStudentProgam::query()
      ->where('subject_id', $subjectId)
      ->where('batch_id', $batchId);

    $programCombinationIds = (clone $baseProgramCombinationQuery)
      ->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType])
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter()
      ->unique()
      ->values();

    if ($programCombinationIds->isEmpty()) {
      $programCombinationIds = (clone $baseProgramCombinationQuery)
        ->whereRaw("TRIM(COALESCE(program_type, '')) = ''")
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();
    }

    if ($programCombinationIds->isEmpty()) {
      $programCombinationIds = (clone $baseProgramCombinationQuery)
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();
    }

    $specializationMap = ProgramWiseSemesterCourse::query()
      ->when($programCombinationIds->isNotEmpty(), fn($q) => $q->whereIn('program_combo_refid', $programCombinationIds))
      ->where('batch', $batchId)
      ->where('semester', $semesterId)
      ->whereNotNull('specialization_master_id')
      ->pluck('specialization_master_id', 'course_id')
      ->map(fn($id) => (int) $id)
      ->all();

    $weeklyHoursCheck = $this->checkWeeklyHours($incomingAssignment, $existingRoutines, $draftEntries, $ignoreRoutineId);
    if (!$weeklyHoursCheck['success']) {
      return $weeklyHoursCheck;
    }

    $facultyCheck = $this->checkFacultyConflict($incomingAssignment, $incomingHour, $existingRoutines, $draftEntries, $weekdayId, $ignoreRoutineId, $shiftId);
    if (!$facultyCheck['success']) {
      return $facultyCheck;
    }

    $roomCheck = $this->checkRoomConflict($incomingAssignment, $incomingHour, $existingRoutines, $draftEntries, $weekdayId, $ignoreRoutineId, $shiftId);
    if (!$roomCheck['success']) {
      return $roomCheck;
    }

    $specializationCheck = $this->checkSpecializationConflict($incomingAssignment, $incomingHour, $existingRoutines, $draftEntries, $specializationMap, $weekdayId, $ignoreRoutineId, $shiftId);
    if (!$specializationCheck['success']) {
      return $specializationCheck;
    }

    return [
      'success' => true,
      'message' => 'No conflict found.',
    ];
  }

  public function checkFacultyConflict(TeachingAssignment $incomingAssignment, HourMaster $incomingHour, $existingRoutines, array $draftEntries, int $weekdayId, int $ignoreRoutineId, int $shiftId): array
  {
    $incomingFacultyId = (int) ($incomingAssignment->faculty_id ?? 0);
    if ($incomingFacultyId <= 0) {
      return [
        'success' => true,
        'message' => 'No faculty conflict check needed.',
      ];
    }

    foreach ($existingRoutines as $routine) {
      if ((int) $routine->weekday_id !== $weekdayId) {
        continue;
      }
      if ((int) ($routine->id ?? 0) === $ignoreRoutineId) {
        continue;
      }
      if ((int) ($routine->faculty_id ?? 0) !== $incomingFacultyId) {
        continue;
      }

      $existingHour = $routine->hourmaster;
      if (!$existingHour || !$this->isTimeOverlapping($incomingHour, $existingHour)) {
        continue;
      }

      $facultyLabel = $this->facultyLabel($routine->teachingAssignment?->faculty);
      $courseLabel = $this->courseLabel($routine);

      return [
        'success' => false,
        'message' => "Faculty {$facultyLabel} is already teaching {$courseLabel} during {$this->formatTimeRange($existingHour)}.",
      ];
    }

    foreach ($draftEntries as $entry) {
      if (!$this->isRelevantDraftEntry($entry, $weekdayId, $ignoreRoutineId, $shiftId)) {
        continue;
      }

      $assignmentId = (int) ($entry['teaching_assignment_id'] ?? 0);
      $assignment = $assignmentId > 0 ? TeachingAssignment::query()->find($assignmentId) : null;
      if (!$assignment || (int) ($assignment->faculty_id ?? 0) !== $incomingFacultyId) {
        continue;
      }

      $draftHour = $this->resolveHourSlot((int) ($entry['hour_id'] ?? 0), (int) ($entry['shift_id'] ?? $shiftId));
      if (!$draftHour || !$this->isTimeOverlapping($incomingHour, $draftHour)) {
        continue;
      }

      return [
        'success' => false,
        'message' => "Faculty already has a class during {$this->formatTimeRange($draftHour)} in current draft timetable.",
      ];
    }

    return [
      'success' => true,
      'message' => 'No faculty conflict found.',
    ];
  }

  public function checkRoomConflict(TeachingAssignment $incomingAssignment, HourMaster $incomingHour, $existingRoutines, array $draftEntries, int $weekdayId, int $ignoreRoutineId, int $shiftId): array
  {
    return [
      'success' => true,
      'message' => 'Room conflict rule is disabled.',
    ];
  }

  public function checkTeachingGroupConflict(TeachingAssignment $incomingAssignment, HourMaster $incomingHour, $existingRoutines, array $draftEntries, int $weekdayId, int $ignoreRoutineId, int $shiftId): array
  {
    return [
      'success' => true,
      'message' => 'Teaching group conflict rule is disabled.',
    ];
  }

  public function checkSpecializationConflict(TeachingAssignment $incomingAssignment, HourMaster $incomingHour, $existingRoutines, array $draftEntries, array $specializationMap, int $weekdayId, int $ignoreRoutineId, int $shiftId): array
  {
    $incomingSpecId = (int) ($specializationMap[(int) ($incomingAssignment->course_id ?? 0)] ?? 0);
    if ($incomingSpecId <= 0) {
      return [
        'success' => true,
        'message' => 'No specialization conflict check needed.',
      ];
    }

    foreach ($existingRoutines as $routine) {
      if ((int) $routine->weekday_id !== $weekdayId) {
        continue;
      }
      if ((int) ($routine->id ?? 0) === $ignoreRoutineId) {
        continue;
      }

      $existingTa = $routine->teachingAssignment;
      if (!$existingTa) {
        continue;
      }

      $existingSpecId = (int) ($specializationMap[(int) ($existingTa->course_id ?? 0)] ?? 0);
      if ($existingSpecId <= 0 || $existingSpecId !== $incomingSpecId) {
        continue;
      }

      $existingHour = $routine->hourmaster;
      if (!$existingHour || !$this->isTimeOverlapping($incomingHour, $existingHour)) {
        continue;
      }

      return [
        'success' => false,
        'message' => "Specialization already has a scheduled paper during {$this->formatTimeRange($existingHour)}.",
      ];
    }

    foreach ($draftEntries as $entry) {
      if (!$this->isRelevantDraftEntry($entry, $weekdayId, $ignoreRoutineId, $shiftId)) {
        continue;
      }

      $assignmentId = (int) ($entry['teaching_assignment_id'] ?? 0);
      $assignment = $assignmentId > 0 ? TeachingAssignment::query()->find($assignmentId) : null;
      if (!$assignment) {
        continue;
      }

      $existingSpecId = (int) ($specializationMap[(int) ($assignment->course_id ?? 0)] ?? 0);
      if ($existingSpecId <= 0 || $existingSpecId !== $incomingSpecId) {
        continue;
      }

      $draftHour = $this->resolveHourSlot((int) ($entry['hour_id'] ?? 0), (int) ($entry['shift_id'] ?? $shiftId));
      if (!$draftHour || !$this->isTimeOverlapping($incomingHour, $draftHour)) {
        continue;
      }

      return [
        'success' => false,
        'message' => "Specialization already has a class during {$this->formatTimeRange($draftHour)} in current draft timetable.",
      ];
    }

    return [
      'success' => true,
      'message' => 'No specialization conflict found.',
    ];
  }

  public function checkWeeklyHours(TeachingAssignment $incomingAssignment, $existingRoutines, array $draftEntries, int $ignoreRoutineId): array
  {
    if (!Schema::hasColumn('teaching_assignments', 'weekly_hours')) {
      return [
        'success' => true,
        'message' => 'Weekly hours not configured.',
      ];
    }

    $weeklyHours = (int) ($incomingAssignment->weekly_hours ?? 0);
    if ($weeklyHours <= 0) {
      return [
        'success' => true,
        'message' => 'Weekly hours not configured for assignment.',
      ];
    }

    $assignmentId = (int) ($incomingAssignment->id ?? 0);

    $dbCount = $existingRoutines
      ->filter(function ($routine) use ($assignmentId, $ignoreRoutineId) {
        if ((int) ($routine->id ?? 0) === $ignoreRoutineId) {
          return false;
        }
        return (int) ($routine->teaching_assignment_id ?? 0) === $assignmentId;
      })
      ->count();

    $existingAssignmentByRoutineId = $existingRoutines
      ->mapWithKeys(function ($routine) {
        return [
          (int) ($routine->id ?? 0) => (int) ($routine->teaching_assignment_id ?? 0),
        ];
      })
      ->all();

    $draftCount = collect($draftEntries)
      ->filter(function ($entry) use ($assignmentId, $ignoreRoutineId, $existingAssignmentByRoutineId) {
        if ((int) ($entry['routine_id'] ?? 0) === $ignoreRoutineId) {
          return false;
        }

        if ((int) ($entry['teaching_assignment_id'] ?? 0) !== $assignmentId) {
          return false;
        }

        // Existing persisted rows are already counted from DB. Count only truly new
        // draft rows, or edits that changed assignment to the incoming one.
        $routineId = (int) ($entry['routine_id'] ?? 0);
        if ($routineId <= 0) {
          return true;
        }

        $existingAssignmentId = (int) ($existingAssignmentByRoutineId[$routineId] ?? 0);
        return $existingAssignmentId !== $assignmentId;
      })
      ->count();

    if (($dbCount + $draftCount) >= $weeklyHours) {
      return [
        'success' => false,
        'message' => "Weekly hours limit reached for this teaching assignment ({$weeklyHours}).",
      ];
    }

    return [
      'success' => true,
      'message' => 'Weekly hours check passed.',
    ];
  }

  private function validateDepartmentShift(Subject $subject, string $shift): array
  {
    $defaultShift = $this->getDefaultShiftSlug();

    if ((int) ($subject->has_shift_delivery ?? 0) !== 1 && $shift !== $defaultShift) {
      return [
        'success' => false,
        'message' => 'This department supports only Common timetable shift.',
      ];
    }

    if (!ShiftMaster::query()->where('slug', $shift)->exists()) {
      return [
        'success' => false,
        'message' => 'Invalid shift selection.',
      ];
    }

    return [
      'success' => true,
      'message' => 'Shift is valid.',
    ];
  }

  private function resolveShiftId(string $shift): int
  {
    return (int) (ShiftMaster::query()->where('slug', $shift)->value('id') ?? 0);
  }

  private function resolveHourSlot(int $hourInput, int $shiftId): ?HourMaster
  {
    if ($hourInput <= 0 || $shiftId <= 0) {
      return null;
    }

    return HourMaster::query()
      ->where('shift_id', $shiftId)
      ->where('status', 1)
      ->where('is_teaching', 1)
      ->where(function ($q) use ($hourInput) {
        $q->where('hour_no', $hourInput)
          ->orWhere('id', $hourInput);
      })
      ->orderBy('hour_no')
      ->first();
  }

  private function isTimeOverlapping(HourMaster $incomingHour, HourMaster $existingHour): bool
  {
    $incomingStart = strtotime((string) $incomingHour->start_time);
    $incomingEnd = strtotime((string) $incomingHour->end_time);
    $existingStart = strtotime((string) $existingHour->start_time);
    $existingEnd = strtotime((string) $existingHour->end_time);

    if (!$incomingStart || !$incomingEnd || !$existingStart || !$existingEnd) {
      return false;
    }

    return $incomingStart < $existingEnd && $incomingEnd > $existingStart;
  }

  private function formatTimeRange(HourMaster $hour): string
  {
    return trim((string) ($hour->start_time ?? '')) . ' - ' . trim((string) ($hour->end_time ?? ''));
  }

  private function facultyLabel($faculty): string
  {
    if (!$faculty) {
      return 'Faculty';
    }

    $name = trim((string) ($faculty->FIRST_NAME ?? '') . ' ' . (string) ($faculty->LAST_NAME ?? ''));
    $code = trim((string) ($faculty->USER_CODE ?? ''));
    if ($name !== '' && $code !== '') {
      return $name . ' (' . $code . ')';
    }

    return $name !== '' ? $name : 'Faculty';
  }

  private function courseLabel(SubjectHasRoutine $routine): string
  {
    $taCourse = $routine->teachingAssignment?->course;
    if ($taCourse) {
      return trim((string) ($taCourse->course_code ?? '-') . ' - ' . (string) ($taCourse->course_title ?? '-'));
    }

    $syllabusCourse = $routine->syllabus?->coursemaster;
    if ($syllabusCourse) {
      return trim((string) ($syllabusCourse->course_code ?? '-') . ' - ' . (string) ($syllabusCourse->course_title ?? '-'));
    }

    return 'another class';
  }

  private function isRelevantDraftEntry(array $entry, int $weekdayId, int $ignoreRoutineId, int $shiftId = 0): bool
  {
    if ((int) ($entry['weekday_id'] ?? 0) !== $weekdayId) {
      return false;
    }
    if ($ignoreRoutineId > 0 && (int) ($entry['routine_id'] ?? 0) === $ignoreRoutineId) {
      return false;
    }

    $entryShiftId = (int) ($entry['shift_id'] ?? 0);
    if ($shiftId > 0 && $entryShiftId > 0 && $entryShiftId !== $shiftId) {
      return false;
    }

    return (int) ($entry['hour_id'] ?? 0) > 0;
  }

  private function getDefaultShiftSlug(): string
  {
    $common = ShiftMaster::query()->where('slug', 'common')->value('slug');
    if (!empty($common)) {
      return (string) $common;
    }

    $fallback = ShiftMaster::query()->orderBy('sort_order')->value('slug');
    return !empty($fallback) ? (string) $fallback : 'common';
  }
}

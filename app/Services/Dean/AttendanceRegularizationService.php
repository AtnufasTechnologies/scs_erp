<?php

namespace App\Services\Dean;

use App\Models\DepartmentActivity;
use App\Models\DepartmentActivityHasParticipant;
use App\Models\DsaAttendanceRegularization;
use App\Models\DsaAttendanceRegularizationItem;
use App\Models\EcProgram;
use App\Models\EcProgramParticipant;
use App\Models\StudentAttendance;
use App\Models\StudentMaster;
use App\Services\Dean\CampusContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRegularizationService
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function fetchApprovedEvents(string $source): Collection
  {
    $campusId = $this->campusContext->campusId();

    if ($source === 'ec_event') {
      $query = EcProgram::with('event:id,title,start_date,end_date')
        ->whereIn('status', ['completed', 'approved'])
        ->latest('program_date')
        ->limit(200);

      if ($campusId) {
        $query->whereIn('id', $this->ecCampusProgramIds($campusId)->all());
      }

      return $query->get()
        ->map(function ($program) {
          return [
            'id' => (int) $program->id,
            'title' => (string) ($program->name ?? 'Event Program'),
            'start_date' => optional($program->program_date)->toDateString(),
            'end_date' => optional($program->program_date)->toDateString(),
          ];
        });
    }

    $query = DepartmentActivity::whereIn('status', ['completed', 'approved'])
      ->latest('activity_date')
      ->limit(200);

    if ($campusId) {
      $query->whereHas('subject', function ($subjectQuery) use ($campusId) {
        $subjectQuery->where('campus_id', $campusId);
      });
    }

    return $query->get()
      ->map(function ($activity) {
        return [
          'id' => (int) $activity->id,
          'title' => (string) ($activity->title ?? 'Department Activity'),
          'start_date' => optional($activity->activity_date)->toDateString(),
          'end_date' => optional($activity->activity_date)->toDateString(),
        ];
      });
  }

  public function previewAbsences(string $eventSource, int $eventId, string $startDate, string $endDate): Collection
  {
    $participantStudentIds = $this->resolveParticipantStudentIds($eventSource, $eventId);
    if ($participantStudentIds->isEmpty()) {
      return collect();
    }

    // Integration constraint: pull absences from attendance only, never mutate originals in preview.
    return StudentAttendance::with(['student:id,first_name,last_name,roll_no'])
      ->whereIn('student_id', $participantStudentIds->all())
      ->whereBetween('attendance_date', [$startDate, $endDate])
      ->where('status', 'absent')
      ->latest('attendance_date')
      ->limit(1000)
      ->get()
      ->map(function ($attendance) {
        return [
          'attendance_id' => (int) $attendance->id,
          'student_id' => (int) $attendance->student_id,
          'student_name' => trim((string) (($attendance->student->first_name ?? '') . ' ' . ($attendance->student->last_name ?? ''))),
          'roll_no' => (string) ($attendance->student->roll_no ?? '-'),
          'date' => optional($attendance->attendance_date)->toDateString(),
          'original_status' => (string) $attendance->status,
          'effective_status' => 'present',
        ];
      });
  }

  public function approveRegularization(string $eventSource, int $eventId, string $startDate, string $endDate, array $attendanceIds, ?string $remarks = null): DsaAttendanceRegularization
  {
    $participantStudentIds = $this->resolveParticipantStudentIds($eventSource, $eventId);
    if ($participantStudentIds->isEmpty()) {
      throw new \RuntimeException('No eligible student participants found for the selected event.');
    }

    $rows = StudentAttendance::whereIn('id', $attendanceIds)
      ->whereIn('student_id', $participantStudentIds->all())
      ->whereBetween('attendance_date', [$startDate, $endDate])
      ->where('status', 'absent')
      ->get();

    return DB::transaction(function () use ($eventSource, $eventId, $startDate, $endDate, $rows, $remarks) {
      $regularization = DsaAttendanceRegularization::create([
        'request_no' => 'DSA-REG-' . now()->format('YmdHis') . '-' . random_int(100, 999),
        'event_source' => $eventSource,
        'event_id' => $eventId,
        'event_start_date' => $startDate,
        'event_end_date' => $endDate,
        'approval_status' => 'approved',
        'requested_by' => Auth::id(),
        'approved_by' => Auth::id(),
        'approved_at' => now(),
        'remarks' => $remarks,
      ]);

      foreach ($rows as $attendance) {
        DsaAttendanceRegularizationItem::create([
          'regularization_id' => $regularization->id,
          'attendance_id' => $attendance->id,
          'student_id' => $attendance->student_id,
          'attendance_date' => $attendance->attendance_date,
          'original_status' => (string) $attendance->status,
          'effective_status' => 'present',
          'remarks' => $remarks,
          'actioned_by' => Auth::id(),
          'actioned_at' => now(),
        ]);
      }

      return $regularization;
    });
  }

  private function resolveParticipantStudentIds(string $eventSource, int $eventId): Collection
  {
    if ($eventSource === 'department_activity') {
      $rollNumbers = DepartmentActivityHasParticipant::where('activity_id', $eventId)
        ->where('is_student', 1)
        ->pluck('participant_rollno')
        ->map(fn($rollNo) => trim((string) $rollNo))
        ->filter()
        ->unique()
        ->values();

      if ($rollNumbers->isEmpty()) {
        return collect();
      }

      $studentQuery = StudentMaster::whereIn('roll_no', $rollNumbers->all());
      $this->campusContext->applyStudentCampus($studentQuery);

      return $studentQuery
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();
    }

    $participants = EcProgramParticipant::where('program_id', $eventId)
      ->get(['email', 'phone']);

    $emails = $participants
      ->pluck('email')
      ->map(fn($email) => strtolower(trim((string) $email)))
      ->filter()
      ->unique()
      ->values();

    $phones = $participants
      ->pluck('phone')
      ->map(function ($phone) {
        return preg_replace('/\D+/', '', (string) $phone);
      })
      ->filter()
      ->unique()
      ->values();

    if ($emails->isEmpty() && $phones->isEmpty()) {
      return collect();
    }

    $normalizedMobileExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(mobile_no, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')";

    $studentQuery = StudentMaster::query();
    $this->campusContext->applyStudentCampus($studentQuery);

    return $studentQuery
      ->where(function ($query) use ($emails, $phones, $normalizedMobileExpression) {
        if ($emails->isNotEmpty()) {
          $query->whereIn(DB::raw('LOWER(mail_id)'), $emails->all());
        }

        if ($phones->isNotEmpty()) {
          $query->orWhereIn(DB::raw($normalizedMobileExpression), $phones->all());
        }
      })
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();
  }

  private function ecCampusProgramIds(int $campusId): Collection
  {
    $campusStudents = StudentMaster::where('campus_id', $campusId)
      ->get(['mail_id', 'mobile_no']);

    $emails = $campusStudents
      ->pluck('mail_id')
      ->map(fn($email) => strtolower(trim((string) $email)))
      ->filter()
      ->unique()
      ->values();

    $phones = $campusStudents
      ->pluck('mobile_no')
      ->map(fn($phone) => preg_replace('/\D+/', '', (string) $phone))
      ->filter()
      ->unique()
      ->values();

    if ($emails->isEmpty() && $phones->isEmpty()) {
      return collect();
    }

    $normalizedPhoneExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')";

    return EcProgramParticipant::query()
      ->where(function ($query) use ($emails, $phones, $normalizedPhoneExpression) {
        if ($emails->isNotEmpty()) {
          $query->whereIn(DB::raw('LOWER(email)'), $emails->all());
        }

        if ($phones->isNotEmpty()) {
          $query->orWhereIn(DB::raw($normalizedPhoneExpression), $phones->all());
        }
      })
      ->groupBy('program_id')
      ->pluck('program_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->values();
  }
}

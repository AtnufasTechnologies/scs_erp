<?php

namespace App\Repositories\Dean;

use App\Models\DepartmentActivity;
use App\Models\EcEvent;
use App\Models\EcProgramParticipant;
use App\Models\EcProgram;
use App\Models\StudentMaster;
use App\Models\UserCampusSetting;
use App\Services\Dean\CampusContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventAnalyticsRepository
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function summary(): array
  {
    $campusId = $this->campusContext->campusId();
    $programIds = $this->campusProgramIds($campusId);

    $ecProgramQuery = EcProgram::withCount('participants');
    if ($campusId) {
      $ecProgramQuery->whereIn('id', $programIds->all())
        ->withCount([
          'participants as participants_count' => function ($query) use ($campusId) {
            $this->applyParticipantCampusFilter($query, $campusId);
          }
        ]);
    }

    $ecPrograms = $ecProgramQuery->get();

    $ecEventQuery = EcEvent::query();
    if ($campusId) {
      $eventIds = $ecPrograms->pluck('event_id')->filter()->unique()->values();
      $ecEventQuery->whereIn('id', $eventIds->all());
    }

    $ecEvents = $ecEventQuery->count();

    $departmentActivityQuery = $this->departmentActivityBaseQuery($campusId);

    $departmentActivities = $departmentActivityQuery->count();
    $deptParticipants = $departmentActivityQuery->sum('actual_participants');

    return [
      'ec_events' => $ecEvents,
      'ec_programs' => $ecPrograms->count(),
      'ec_participants' => (int) $ecPrograms->sum('participants_count'),
      'department_activities' => $departmentActivities,
      'department_participants' => (int) ($deptParticipants ?? 0),
    ];
  }

  public function eventProgramRows()
  {
    $campusId = $this->campusContext->campusId();

    $query = EcProgram::with(['event:id,title,start_date,end_date'])
      ->withCount('participants');

    if ($campusId) {
      $programIds = $this->campusProgramIds($campusId);
      $query->whereIn('id', $programIds->all())
        ->withCount([
          'participants as participants_count' => function ($participantQuery) use ($campusId) {
            $this->applyParticipantCampusFilter($participantQuery, $campusId);
          }
        ]);
    }

    return $query->latest()->limit(100)->get();
  }

  public function departmentActivityRows()
  {
    $campusId = $this->campusContext->campusId();

    return $this->departmentActivityBaseQuery($campusId)
      ->with(['subject:id,title,campus_id'])
      ->latest('activity_date')
      ->limit(100)
      ->get();
  }

  private function departmentActivityBaseQuery(?int $campusId)
  {
    $query = DepartmentActivity::query();

    if (!$campusId) {
      return $query;
    }

    $campusUserIds = UserCampusSetting::where('campus_id', $campusId)->select('user_id');

    return $query->where(function ($innerQuery) use ($campusId, $campusUserIds) {
      $innerQuery->whereHas('subject', function ($subjectQuery) use ($campusId) {
        $subjectQuery->where('campus_id', $campusId);
      })->orWhereIn('created_by', $campusUserIds);
    });
  }

  private function campusProgramIds(?int $campusId): Collection
  {
    if (!$campusId) {
      return EcProgram::query()->pluck('id');
    }

    return EcProgramParticipant::query()
      ->select('program_id')
      ->where(function ($participantQuery) use ($campusId) {
        $this->applyParticipantCampusFilter($participantQuery, $campusId);
      })
      ->groupBy('program_id')
      ->pluck('program_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->values();
  }

  private function applyParticipantCampusFilter($participantQuery, int $campusId): void
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
      $participantQuery->whereRaw('1 = 0');
      return;
    }

    $normalizedPhoneExpression = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')";

    $participantQuery->where(function ($query) use ($emails, $phones, $normalizedPhoneExpression) {
      if ($emails->isNotEmpty()) {
        $query->whereIn(DB::raw('LOWER(email)'), $emails->all());
      }

      if ($phones->isNotEmpty()) {
        $query->orWhereIn(DB::raw($normalizedPhoneExpression), $phones->all());
      }
    });
  }
}

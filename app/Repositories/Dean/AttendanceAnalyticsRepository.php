<?php

namespace App\Repositories\Dean;

use App\Models\StudentAttendance;
use App\Services\Dean\CampusContextService;
use Illuminate\Support\Collection;

class AttendanceAnalyticsRepository
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function studentPercentages(?int $subjectId = null): Collection
  {
    $query = StudentAttendance::query()
      ->selectRaw('student_id, COUNT(*) as total_classes')
      ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_classes")
      ->with(['student:id,first_name,last_name,academic_dept_id']);

    $this->campusContext->applyStudentRelationCampus($query, 'student');

    if ($subjectId) {
      $query->whereHas('student', function ($q) use ($subjectId) {
        $q->where('academic_dept_id', $subjectId);
      });
    }

    return $query
      ->groupBy('student_id')
      ->get()
      ->map(function ($row) {
        $total = (int) ($row->total_classes ?? 0);
        $present = (int) ($row->present_classes ?? 0);
        $pct = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        return [
          'student_id' => (int) $row->student_id,
          'student_name' => trim((string) (($row->student->first_name ?? '') . ' ' . ($row->student->last_name ?? ''))),
          'department_id' => (int) ($row->student->academic_dept_id ?? 0),
          'total_classes' => $total,
          'present_classes' => $present,
          'attendance_pct' => $pct,
        ];
      });
  }

  public function thresholdBuckets(Collection $studentPercentages): array
  {
    return [
      'below_75' => $studentPercentages->where('attendance_pct', '<', 75)->count(),
      'below_60' => $studentPercentages->where('attendance_pct', '<', 60)->count(),
      'below_50' => $studentPercentages->where('attendance_pct', '<', 50)->count(),
      'below_40' => $studentPercentages->where('attendance_pct', '<', 40)->count(),
    ];
  }
}

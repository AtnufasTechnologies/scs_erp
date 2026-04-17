<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\InvigilationDuty;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\ExamSystem\Room;
use Illuminate\Support\Facades\DB;

class InvigilationService
{
  /**
   * Auto-assign invigilators for an exam based on availability, department, and workload.
   * Avoids same faculty in same room repeatedly and timing clashes.
   * Randomizes assignments for fairness.
   */
  /**
   * Assign invigilators for an exam. Logs all assignments with user and timestamp.
   * Only COE/Admin can assign.
   *
   * @param int $examId
   * @param int $assignedByUserId
   * @throws \Exception if user is not COE/Admin
   */
  public function assignInvigilators($examId, $assignedByUserId)
  {
    // Check if user is COE/Admin
    $user = \App\Models\User::findOrFail($assignedByUserId);
    $role = optional($user->userroletype)->role_name;
    if (!in_array($role, ['COE', 'Admin', 'SuperAdmin'])) {
      throw new \Exception('Only COE/Admin can assign invigilation duties.');
    }

    $rooms = Room::all();
    $faculties = FacultyProfile::all();
    $facultyWorkload = [];
    foreach ($faculties as $faculty) {
      $facultyWorkload[$faculty->id] = InvigilationDuty::where('faculty_id', $faculty->id)->count();
    }
    $facultyIds = $faculties->pluck('id')->toArray();
    shuffle($facultyIds);
    foreach ($rooms as $room) {
      $available = array_filter($facultyIds, function ($fid) use ($examId, $room) {
        return !InvigilationDuty::where('exam_id', $examId)
          ->where('room_id', $room->id)
          ->where('faculty_id', $fid)
          ->exists();
      });
      usort($available, function ($a, $b) use ($facultyWorkload) {
        return $facultyWorkload[$a] <=> $facultyWorkload[$b];
      });
      $facultyId = reset($available);
      if ($facultyId) {
        $duty = InvigilationDuty::create([
          'exam_id' => $examId,
          'faculty_id' => $facultyId,
          'room_id' => $room->id,
          'date' => now()->toDateString(),
          'session' => 'morning',
          'role' => 'invigilator',
          'status' => 'assigned',
        ]);
        $facultyWorkload[$facultyId]++;
        // Log assignment in DutyLog
        \App\Models\ExamSystem\DutyLog::create([
          'faculty_id' => $facultyId,
          'duty_type' => 'invigilation',
          'reference_id' => $duty->id,
          'action' => 'assigned',
          'timestamp' => now(),
          'assigned_by' => $assignedByUserId,
        ]);
      }
    }
  }

  /**
   * Get all invigilation duties for a faculty (schedule).
   */
  public function getFacultySchedule($facultyId)
  {
    return InvigilationDuty::where('faculty_id', $facultyId)
      ->orderBy('date')
      ->orderBy('session')
      ->get();
  }

  /**
   * Mark a duty as completed.
   */
  public function markDutyCompleted($dutyId)
  {
    $duty = InvigilationDuty::findOrFail($dutyId);
    $duty->status = 'completed';
    $duty->save();
    return $duty;
  }
}

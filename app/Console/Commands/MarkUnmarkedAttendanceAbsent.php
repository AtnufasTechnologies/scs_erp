<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExamSystem\AttendanceSession;
use App\Models\ExamSystem\ExamAttendance;
use Carbon\Carbon;

class MarkUnmarkedAttendanceAbsent extends Command
{
  protected $signature = 'attendance:mark-absent';
  protected $description = 'Mark all unmarked attendance as absent after session closes';

  public function handle()
  {
    $sessions = AttendanceSession::where('status', 'closed')->get();
    $count = 0;
    foreach ($sessions as $session) {
      $unmarked = ExamAttendance::where('exam_id', $session->exam_id)
        ->where('room_id', $session->room_id)
        ->whereNull('status')
        ->get();
      foreach ($unmarked as $attendance) {
        $attendance->status = 'absent';
        $attendance->marked_at = Carbon::now();
        $attendance->save();
        $count++;
      }
    }
    $this->info("Marked $count unmarked attendance records as absent.");
    return 0;
  }
}

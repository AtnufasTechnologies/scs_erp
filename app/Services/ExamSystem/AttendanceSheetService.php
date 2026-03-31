<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\Room;
use App\Models\ExamSystem\ExamAttendance;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceSheetService
{
  /**
   * Generate and download attendance sheet PDF for a room and exam.
   *
   * @param int $examId
   * @param int $roomId
   * @return \Illuminate\Http\Response
   */
  public function downloadSheet($examId, $roomId)
  {
    $room = Room::findOrFail($roomId);
    $attendances = ExamAttendance::where('exam_id', $examId)
      ->where('room_id', $roomId)
      ->with('student')
      ->orderBy('seat_no')
      ->get();

    $pdf = Pdf::loadView('attendance.sheet', [
      'room' => $room,
      'attendances' => $attendances,
    ]);
    $filename = 'AttendanceSheet_Room_' . $room->name . '_Exam_' . $examId . '.pdf';
    return $pdf->download($filename);
  }
}

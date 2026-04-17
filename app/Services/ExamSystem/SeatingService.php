<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\SeatingAllocation;
use App\Models\ExamSystem\ExamAttendance;
use App\Services\ExamSystem\DummyNumberService;
use App\Models\ExamSystem\Room;
use App\Models\ExamSystem\Registration;
use Illuminate\Support\Facades\DB;

class SeatingService
{
  /**
   * Assign students to rooms, shuffle and mix subjects.
   */
  /**
   * Assign students to rooms, shuffle and mix subjects, and pre-generate attendance records.
   */
  public function assignSeating($examId)
  {
    $rooms = Room::all();
    $registrations = Registration::where('exam_id', $examId)->get();
    $students = $registrations->pluck('exam_student_id')->toArray();
    shuffle($students);

    // Generate dummy numbers for all students
    $dummyService = app(DummyNumberService::class);
    $dummyService->generateDummyNumbers($examId, $students);

    $roomIndex = 0;
    $seatNo = 1;
    foreach ($students as $studentId) {
      $room = $rooms[$roomIndex] ?? null;
      if (!$room) break;
      // Find dummy number for this student
      $dummy = \App\Models\ExamSystem\DummyNumber::where('exam_id', $examId)
        ->where('exam_student_id', $studentId)
        ->first();

      // Find all subjects for this student in this exam
      $studentRegistration = $registrations->where('exam_student_id', $studentId)->first();
      $subjects = [];
      if ($studentRegistration && isset($studentRegistration->subjects)) {
        $subjects = $studentRegistration->subjects;
      } else {
        // fallback: if subjects not eager loaded, skip or fetch as needed
        // $subjects = ...
      }

      // If no subject info, create a single attendance record (fallback)
      if (empty($subjects)) {
        ExamAttendance::create([
          'exam_id' => $examId,
          'student_id' => $studentId,
          'subject_id' => null,
          'room_id' => $room->id,
          'seat_no' => $seatNo,
          'dummy_no' => $dummy ? $dummy->dummy_number : null,
          'status' => 'absent',
        ]);
      } else {
        foreach ($subjects as $subjectId) {
          ExamAttendance::create([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'room_id' => $room->id,
            'seat_no' => $seatNo,
            'dummy_no' => $dummy ? $dummy->dummy_number : null,
            'status' => 'absent',
          ]);
        }
      }

      SeatingAllocation::create([
        'exam_schedule_id' => $examId, // or schedule id if available
        'room_id' => $room->id,
        'exam_student_id' => $studentId,
        'seat_no' => $seatNo,
      ]);
      $seatNo++;
      if ($seatNo > $room->capacity) {
        $roomIndex++;
        $seatNo = 1;
      }
    }
  }
}

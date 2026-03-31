<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\MalpracticeCase;
use App\Models\ExamSystem\Room;
use App\Models\ExamSystem\Subject;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceReportService
{
  // Room-wise attendance
  public function roomWise($examId)
  {
    return Room::with(['examAttendances' => function ($q) use ($examId) {
      $q->where('exam_id', $examId)->with('student');
    }])->get();
  }

  // Subject-wise attendance
  public function subjectWise($examId)
  {
    return Subject::with(['examAttendances' => function ($q) use ($examId) {
      $q->where('exam_id', $examId)->with('student');
    }])->get();
  }

  // Absentee list
  public function absenteeList($examId)
  {
    return ExamAttendance::where('exam_id', $examId)
      ->absent()
      ->with(['student', 'room', 'subject'])
      ->get();
  }

  // Malpractice list
  public function malpracticeList($examId)
  {
    return MalpracticeCase::where('exam_id', $examId)
      ->with(['student', 'subject', 'room'])
      ->get();
  }

  // Export to Excel
  public function exportExcel($data, $view, $filename)
  {
    return Excel::download(new \App\Exports\GenericExport($data, $view), $filename);
  }

  // Export to PDF
  public function exportPdf($data, $view, $filename)
  {
    $pdf = Pdf::loadView($view, ['data' => $data]);
    return $pdf->download($filename);
  }
}

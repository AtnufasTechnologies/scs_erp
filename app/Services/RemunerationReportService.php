<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\FacultyRemuneration;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RemunerationReportService
{
  /**
   * Get faculty-wise payment summary for a given month
   * @param string|null $month (format: YYYY-MM)
   * @return \Illuminate\Support\Collection
   */
  public function facultyWiseSummary($month = null)
  {
    $query = FacultyRemuneration::query();
    if ($month) {
      $query->whereRaw('DATE_FORMAT(generated_at, "%Y-%m") = ?', [$month]);
    }
    return $query->select(
      'faculty_id',
      DB::raw('COUNT(*) as total_duties'),
      DB::raw('SUM(total_amount) as total_amount')
    )
      ->groupBy('faculty_id')
      ->with('faculty')
      ->get();
  }

  /**
   * Export summary to Excel
   * @param string|null $month
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function exportExcel($month = null)
  {
    $data = $this->facultyWiseSummary($month);
    $export = new \App\Exports\FacultyRemunerationExport($data);
    $filename = 'faculty_remuneration_' . ($month ?? 'all') . '.xlsx';
    return Excel::download($export, $filename);
  }

  /**
   * Export summary to PDF
   * @param string|null $month
   * @return \Illuminate\Http\Response
   */
  public function exportPdf($month = null)
  {
    $data = $this->facultyWiseSummary($month);
    $pdf = Pdf::loadView('remuneration.report', ['summary' => $data, 'month' => $month]);
    return $pdf->download('faculty_remuneration_' . ($month ?? 'all') . '.pdf');
  }
}

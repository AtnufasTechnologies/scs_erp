<?php

namespace App\Exports;

use App\Models\FacultyRemuneration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FacultyRemunerationExport implements FromView
{
  protected $filters;

  public function __construct($filters = [])
  {
    $this->filters = $filters;
  }

  public function view(): View
  {
    $query = FacultyRemuneration::with('faculty');

    if (!empty($this->filters['faculty_id'])) {
      $query->where('faculty_id', $this->filters['faculty_id']);
    }
    if (!empty($this->filters['duty_type'])) {
      $query->where('duty_type', $this->filters['duty_type']);
    }
    if (!empty($this->filters['status'])) {
      $query->where('status', $this->filters['status']);
    }

    $remunerations = $query->orderBy('created_at', 'desc')->get();

    return view('remuneration.report_excel', [
      'remunerations' => $remunerations
    ]);
  }
}

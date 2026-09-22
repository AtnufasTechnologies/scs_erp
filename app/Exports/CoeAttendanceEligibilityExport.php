<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoeAttendanceEligibilityExport implements FromCollection, WithHeadings, ShouldAutoSize
{
  private Collection $rows;
  private int $threshold;

  public function __construct(Collection $rows, int $threshold)
  {
    $this->rows = $rows;
    $this->threshold = $threshold;
  }

  public function headings(): array
  {
    return [
      'Roll No',
      'Student Name',
      'Program Type',
      'Batch',
      'Semester',
      'Attended Classes',
      'Total Classes',
      'Attendance Percentage',
      'Eligibility',
    ];
  }

  public function collection(): Collection
  {
    return $this->rows->map(function ($row) {
      $percentage = (float) ($row->attendance_percentage ?? 0);
      $eligible = $percentage >= $this->threshold ? 'Eligible' : 'Shortage';

      return [
        (string) ($row->roll_no ?? '-'),
        trim((string) (($row->first_name ?? '') . ' ' . ($row->last_name ?? ''))) ?: '-',
        strtoupper((string) ($row->program_type ?? '-')),
        (string) ($row->batch_name ?: (string) ((int) ($row->batch_id ?? 0))),
        (string) ($row->semester_title ?: ('Semester ' . (int) ($row->semester_id ?? 0))),
        (int) ($row->attended_classes ?? 0),
        (int) ($row->total_classes ?? 0),
        number_format($percentage, 2) . '%',
        $eligible,
      ];
    });
  }
}

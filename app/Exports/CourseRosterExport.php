<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseRosterExport implements FromCollection, WithHeadings, ShouldAutoSize
{
  protected Collection $rows;

  public function __construct(Collection $rows)
  {
    $this->rows = $rows;
  }

  public function collection(): Collection
  {
    $sl = 1;

    return $this->rows->map(function ($row) use (&$sl) {
      $student = $row->studentmaster;

      return [
        'SL' => $sl++,
        'Roll No' => (string) ($student->roll_no ?? ''),
        'Register No' => (string) ($student->register_no ?? ''),
        'Student Name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
      ];
    });
  }

  public function headings(): array
  {
    return ['SL', 'Roll No', 'Register No', 'Student Name'];
  }
}

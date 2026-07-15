<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentLibraryCodeExport implements FromCollection, WithHeadings, ShouldAutoSize
{
  protected Collection $students;

  public function __construct(Collection $students)
  {
    $this->students = $students;
  }

  public function collection(): Collection
  {
    return $this->students->map(function ($student) {
      return [
        'student_id' => $student->id,
        'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
        'batch' => $student->batchmaster->batch_name ?? (string) $student->batch,
        'roll_no' => $student->roll_no,
        'library_code' => $student->library_code,
      ];
    });
  }

  public function headings(): array
  {
    return [
      'Student ID',
      'Student Name',
      'Batch',
      'Roll No',
      'Library Code',
    ];
  }
}

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
        'roll_no' => $student->roll_no,
        'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
        'dob' =>  date('d-m-Y', strtotime($student->dob)) ?? '',
        'gender' =>  $student->gender == 1 ? 'Male' : 'Female',
        'batch' => $student->batchmaster->batch_name ?? (string) $student->batch,
        'library_code' => $student->library_code,
        'dept' => $student->stdprogramenrolled->code ?? '',
        'program_name' => $student->stdprogramenrolled->name ?? '',
        'address' => $student->address ?? '',
        'contact_no' => $student->fr_mobile_no ?? '',
      ];
    });
  }

  public function headings(): array
  {
    return [
      'Student ID',
      'Roll No',
      'Student Name',
      'Dob',
      'Gender',
      'Batch',
      'Library Code',
      'Dept',
      'Program Name',
      'Address',
      'Contact No'
    ];
  }
}

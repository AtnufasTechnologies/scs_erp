<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubstitutionHistoryExport implements FromCollection, WithHeadings
{
  protected $data;

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function collection()
  {
    return collect($this->data);
  }

  public function headings(): array
  {
    return [
      'Date',
      'Day',
      'Hour',
      'Subject',
      'Course',
      'Semester',
      'Original Teacher',
      'Original Teacher Code',
      'Substitute Teacher',
      'Substitute Teacher Code',
      'Reason',
      'Created By',
      'Created At',
    ];
  }
}

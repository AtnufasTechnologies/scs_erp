<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CentralOfficeStudentTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
  protected Collection $data;


  public function __construct(Collection $data)
  {
    $this->data = $data->values();
  }

  public function headings(): array
  {
    return [
      'SL. NO.',
      'TYPE',
      'ADMISSION APPLICATION NUMBER',
      'COLLEGE ROLL NUMBER',
      'PROGRAM CODE',
      'PROGRAM NAME',
      'NAME OF THE STUDENT',
      'GENDER',
      'SEMESTER',
      'SESSION',
      'CASTE CATEGORY',
      'PHYSICALLY CHALLENGED',
      'HAS LAPTOP',
      'FROM TEAESTATE',
      "FATHER'S NAME",
      "MOTHER'S NAME",
      "FATHER'S OCCUPATION",
      'STUDENT CONTACT NUMBER',
      'STUDENT MAIL ID',
      "FATHER'S CONTACT NUMBER",
      "MOTHER'S CONTACT NUMBER",
      'ADDRESS',
      'POSTAL PIN',
      'XII SCHOOL',
      'XII BOARD',
      'YEAR OF PASSING XII',
      'ROLL NUMBER XII',
      'DATE OF ADMISSION',
      'DATE OF BIRTH',
      'NATIONALITY',
      'STATE',
      'DISTRICT',
      'RELIGION',
      'BLOOD GROUP',
      'AADHAR NUMBER',
      'COLLEGE REGISTRATION NUMBER',
      'ABC ID (APAR)',
    ];
  }

  public function collection(): Collection
  {
    return $this->data->values()->map(function ($row, $index) {
      $student = data_get($row, 'studentmaster');
      $address = $this->firstFilled([
        data_get($row, 'permanent_address'),
        data_get($student, 'address'),
      ]);
      $postalPin = $this->firstFilled([
        data_get($row, 'pincode'),
        data_get($student, 'pincode'),
      ]);

      return [
        'sl_no' => $index + 1,
        'type' => $this->firstFilled([
          data_get($row, 'registrationmaster.application_type'),
          data_get($row, 'application_type'),
        ]),
        'admission_application_number' => $this->firstFilled([
          data_get($row, 'application_code'),
          data_get($student, 'user_code'),
        ]),
        'college_roll_number' => $this->firstFilled([
          data_get($student, 'roll_no'),
          data_get($row, 'roll_no'),
        ]),
        'program_code' =>  trim((string) (data_get($student, 'stdprogramenrolled.code', ''))),
        'program_name' =>  trim((string) (data_get($student, 'stdprogramenrolled.name', ''))),

        'name_of_student' => trim((string) $this->firstFilled([
          trim((string) (data_get($student, 'first_name', '') . ' ' . data_get($student, 'last_name', ''))),
          trim((string) (data_get($row, 'first_name', '') . ' ' . data_get($row, 'last_name', ''))),
        ])),
        'gender' => $this->formatGender(data_get($student, 'gender', data_get($row, 'gender'))),
        'semester' => $this->firstFilled([
          data_get($student, 'activeSemesterConfig.semester_id'),
          data_get($student, 'current_year'),
        ]),
        'session' => $this->firstFilled([
          data_get($student, 'batchmaster.batch_name'),
          data_get($student, 'batch'),
          data_get($row, 'registrationmaster.batch'),
        ]),
        'caste_category' => (string) data_get($student, 'caste', data_get($row, 'caste', '')),
        'physically_challenged' => $this->formatYesNo(data_get($student, 'is_physically_challenged', data_get($row, 'phychallenged'))),
        'has_laptop' => $this->formatYesNo(data_get($student, 'has_laptop', data_get($row, 'has_laptop'))),
        'from_teaestate' => $this->formatYesNo(data_get($student, 'from_teaestate', data_get($row, 'from_teaestate'))),
        'father_name' => (string) data_get($student, 'father_name', data_get($row, 'father_name', '')),
        'mother_name' => (string) data_get($student, 'mother_name', data_get($row, 'mother_name', '')),
        'father_occupation' => (string) data_get($student, 'fr_occupation', data_get($row, 'father_occupation', '')),
        'student_contact_number' => (string) data_get($student, 'mobile_no', data_get($row, 'registrationmaster.mobile_no', '')),
        'student_mail_id' => (string) data_get($student, 'mail_id', data_get($row, 'registrationmaster.mail_id', '')),
        'father_contact_number' => (string) data_get($student, 'fr_mobile_no', data_get($row, 'father_contact', '')),
        'mother_contact_number' => (string) data_get($student, 'mr_mobile_no', data_get($row, 'mother_contact', '')),
        'address' => $address,
        'postal_pin' => $postalPin,
        'xii_school' => (string) data_get($row, 'institution12', ''),
        'xii_board' => (string) data_get($row, 'board12', ''),
        'year_of_passing_xii' => (string) data_get($row, 'passingyear12', ''),
        'roll_number_xii' => (string) data_get($row, 'rollno12', ''),
        'date_of_admission' => $this->formatDate(data_get($student, 'admission_date', null)),
        'date_of_birth' => $this->formatDate(data_get($student, 'dob', null)),
        'nationality' => (string) data_get($student, 'nationalitymaster.name', data_get($row, 'registrationmaster.countrymaster.name', '')),
        'state' => $this->firstFilled([
          data_get($row, 'state'),
          data_get($student, 'state'),
          data_get($row, 'local_state'),
        ]),
        'district' => $this->firstFilled([
          data_get($row, 'district'),
          data_get($student, 'district'),
          data_get($row, 'local_district'),
        ]),
        'religion' => (string) data_get($student, 'religionmaster.name', data_get($row, 'religionmaster.name', '')),
        'blood_group' => $this->firstFilled([
          data_get($student, 'bloodgroup.name'),
          data_get($student, 'bloodgroup.blood_group'),
          data_get($row, 'bloodgroupmaster.name'),
        ]),
        'aadhar_number' => (string) data_get($student, 'aadhar_no', data_get($row, 'adhaar', '')),
        'college_registration_number' => $this->firstFilled([
          data_get($student, 'register_no'),
          data_get($student, 'university_register_no'),
        ]),
        'abc_id_apar' => (string) (data_get($student, 'abc_id') ?? data_get($student, 'apar_id') ?? data_get($student, 'apaar_id') ?? ''),
      ];
    });
  }

  private function firstFilled(array $values): string
  {
    foreach ($values as $value) {
      if ($value === null) {
        continue;
      }

      $stringValue = is_string($value) ? trim($value) : (string) $value;
      if ($stringValue !== '') {
        return $stringValue;
      }
    }

    return '';
  }

  private function formatDate($value): string
  {
    if (empty($value)) {
      return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d-m-Y', $timestamp) : (string) $value;
  }

  private function formatGender($value): string
  {
    if ($value === null || $value === '') {
      return '';
    }

    $normalized = strtolower(trim((string) $value));
    if ($normalized === 'male' || $normalized === 'm' || (string) $value === '1') {
      return 'Male';
    }

    if ($normalized === 'female' || $normalized === 'f' || (string) $value === '0' || (string) $value === '2') {
      return 'Female';
    }

    return (string) $value;
  }

  private function formatYesNo($value): string
  {
    if ($value === null || $value === '') {
      return '';
    }

    if (is_string($value)) {
      $normalized = strtolower(trim($value));
      if (in_array($normalized, ['yes', 'y', 'true', '1'], true)) {
        return 'Yes';
      }

      if (in_array($normalized, ['no', 'n', 'false', '0'], true)) {
        return 'No';
      }
    }

    return (int) $value === 1 ? 'Yes' : 'No';
  }
}

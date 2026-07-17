<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class StudentDataExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $students;
    protected Collection $rows;
    protected array $headings = [];
    protected array $headingMap = [
        'student_id' => 'Student ID',
        'user_code' => 'Application Code',
        'roll_no' => 'Roll No',
        'register_no' => 'Register No',
        'university_register_no' => 'University Register No',
        'batch' => 'Batch',
        'admission_date' => 'Admission Date',
        'student_name' => 'Name',
        'mobile_no' => 'Mobile No',
        'email' => 'Email',
        'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'blood_group' => 'Blood Group',
        'religion' => 'Religion',
        'mother_tongue' => 'Mother Tongue',
        'physically_challenged' => 'Physically Challenged',
        'caste' => 'Caste',
        'nationality' => 'Nationality',
        'campus' => 'Campus',
        'department' => 'Department',
        'program_code' => 'Program Code',
        'program_name' => 'Program Name',
        'academic_pathway' => 'Academic Pathway',
        'degree_track' => 'Degree Track',
        'selected_combo' => 'Selected Combo',
        'active_semester' => 'Active Semester',
        'father_name' => "Father's Name",
        'father_occupation' => "Father's Occupation",
        'father_contact' => "Father's Contact",
        'mother_name' => "Mother's Name",
        'mother_occupation' => "Mother's Occupation",
        'mother_contact' => "Mother's Contact",
        'guardian_name' => "Guardian's Name",
        'guardian_contact' => "Guardian's Contact",
        'family_income' => 'Family Monthly Income',
        'permanent_address' => 'Permanent Address',
        'district' => 'District',
        'city' => 'City',
        'state' => 'State',
        'pincode' => 'Pincode',
        'local_address' => 'Local Address',
        'local_district' => 'Local District',
        'local_city' => 'Local City',
        'local_state' => 'Local State',
        'local_pincode' => 'Local Pincode',
        'current_year' => 'Current Year',
        'graduation_year' => 'Graduation Year',
        'status' => 'Status',
        'aadhar_no' => 'Aadhar No',
        'community' => 'Community',
        'is_roman_catholic' => 'Roman Catholic',
        'library_code' => 'Library Code',
        'remarks' => 'Remarks',
    ];

    public function __construct(Collection $students)
    {
        $this->students = $students;
        $this->rows = collect();
        $this->prepareRowsAndHeadings();
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    private function prepareRowsAndHeadings(): void
    {
        $orderedKeys = array_keys($this->headingMap);

        $rows = $this->students->map(function ($student) {
            $addressRelation = $student->relationLoaded('address') ? $student->getRelation('address') : null;

            return [
                // Form / identity
                'student_id' => $student->id,
                'user_code' => $student->user_code ?? '',
                'roll_no' => $student->roll_no ?? '',
                'register_no' => $student->register_no ?? '',
                'university_register_no' => $student->university_register_no ?? '',
                'batch' => $student->batchmaster->batch_name ?? (string) ($student->batch ?? ''),
                'admission_date' => $this->formatDate($student->admission_date ?? null),

                // Personal details
                'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                'mobile_no' => $student->mobile_no ?? '',
                'email' => $student->mail_id ?? '',
                'date_of_birth' => $this->formatDate($student->dob ?? null),
                'gender' => $this->formatGender($student->gender ?? null),
                'blood_group' => $student->bloodgroup->name ?? ($student->bloodgroup->blood_group ?? ''),
                'religion' => $student->religionmaster->name ?? '',
                'mother_tongue' => $student->mother_tongue ?? '',
                'physically_challenged' => $this->formatYesNo($student->is_physically_challenged ?? null),
                'caste' => $student->caste ?? '',
                'nationality' => $student->nationalitymaster->name ?? '',
                'campus' => $student->campusmaster->name ?? '',
                'department' => $student->deptmaster->title ?? ($student->deptmaster->name ?? ''),
                'program_code' => $student->stdprogramenrolled->code ?? '',
                'program_name' => $student->stdprogramenrolled->name ?? '',
                'academic_pathway' => $student->academicpathway->name ?? '',
                'degree_track' => $student->degreetrack->name ?? '',
                'selected_combo' => $student->singleselection->title ?? '',
                'active_semester' => $student->activeSemesterConfig->semester_id ?? '',

                // Parent / guardian details
                'father_name' => $student->father_name ?? '',
                'father_occupation' => $student->fr_occupation ?? '',
                'father_contact' => $student->fr_mobile_no ?? '',
                'mother_name' => $student->mother_name ?? '',
                'mother_occupation' => $student->mr_occupation ?? '',
                'mother_contact' => $student->mr_mobile_no ?? '',
                'guardian_name' => $student->guardian_name ?? '',
                'guardian_contact' => $student->guardian_mobile_no ?? '',
                'family_income' => $student->annual_income ?? '',

                // Address details
                'permanent_address' => data_get($addressRelation, 'permanent_address', $student->getAttribute('address') ?? ''),
                'district' => data_get($addressRelation, 'district', ''),
                'city' => data_get($addressRelation, 'city', ''),
                'state' => data_get($addressRelation, 'state', ''),
                'pincode' => data_get($addressRelation, 'pincode', ''),
                'local_address' => data_get($addressRelation, 'local_address', ''),
                'local_district' => data_get($addressRelation, 'local_district', ''),
                'local_city' => data_get($addressRelation, 'local_city', ''),
                'local_state' => data_get($addressRelation, 'local_state', ''),
                'local_pincode' => data_get($addressRelation, 'local_pincode', ''),

                // Additional academic / profile details
                'current_year' => $student->current_year ?? '',
                'graduation_year' => $student->graduation_year ?? '',
                'status' => $student->status ?? '',
                'aadhar_no' => $student->aadhar_no ?? '',
                'community' => $student->community ?? '',
                'is_roman_catholic' => $this->formatYesNo($student->is_roman_catholic ?? null),
                'library_code' => $student->library_code ?? '',
                'remarks' => $student->remarks ?? '',
            ];
        });

        $this->headings = array_map(function ($key) {
            return $this->headingMap[$key] ?? Str::headline($key);
        }, $orderedKeys);

        $this->rows = $rows->map(function ($row) use ($orderedKeys) {
            $normalized = [];
            foreach ($orderedKeys as $key) {
                $normalized[$key] = $row[$key] ?? '';
            }
            return $normalized;
        });
    }

    private function flattenRow(array $data, string $prefix = ''): array
    {
        $flat = [];

        foreach ($data as $key => $value) {
            $flatKey = $prefix === '' ? (string) $key : $prefix . '_' . $key;

            if (is_array($value)) {
                if ($this->isListArray($value)) {
                    $flat[$flatKey] = json_encode($value, JSON_UNESCAPED_UNICODE);
                    continue;
                }

                $flat = array_merge($flat, $this->flattenRow($value, $flatKey));
                continue;
            }

            $flat[$flatKey] = $value ?? '';
        }

        return $flat;
    }

    private function isListArray(array $value): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($value);
        }

        return array_keys($value) === range(0, count($value) - 1);
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
        if ($normalized === 'male' || $normalized === 'm') {
            return 'Male';
        }

        if ($normalized === 'female' || $normalized === 'f') {
            return 'Female';
        }

        if ((string) $value === '1') {
            return 'Male';
        }

        if ((string) $value === '0' || (string) $value === '2') {
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
            if (in_array($normalized, ['yes', 'y', 'true'], true)) {
                return 'Yes';
            }

            if (in_array($normalized, ['no', 'n', 'false'], true)) {
                return 'No';
            }
        }

        return (int) $value === 1 ? 'Yes' : 'No';
    }
}

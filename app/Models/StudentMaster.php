<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_code',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'mobile_no',
        'mail_id',
        'address',
        'father_name',
        'mother_name',
        'guardian_name',
        'fr_mobile_no',
        'mr_mobile_no',
        'guardian_mobile_no',
        'fr_occupation',
        'mr_occupation',
        'department',
        'batch',
        'campus_id',
        'roll_no',
        'register_no',
        'university_register_no',
        'current_year',
        'admission_date',
        'graduation_year',
        'status',
        'nationality',
        'religion',
        'community',
        'caste',
        'blood_group_id',
        'mother_tongue',
        'aadhar_no',
        'annual_income',
        'is_roman_catholic',
        'remarks',
        'user_type',
        'nationality',
        'new_program_id',
        'academic_dept_id',
        'photo_path',
    ];

    function religionmaster()
    {
        return $this->hasOne(ReligionMaster::class, 'id', 'religion');
    }

    function deptmaster()
    {
        return $this->hasOne(DepartmentMaster::class, 'id', 'department');
    }

    function nationalitymaster()
    {
        return $this->hasOne(NationalityMaster::class, 'id', 'nationality');
    }

    function usertype()
    {
        return $this->hasOne(UserType::class, 'id', 'user_type');
    }

    function bloodgroup()
    {
        return $this->hasOne(BloodGroupMaster::class, 'id', 'blood_group_id');
    }

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch');
    }

    function stdfeestructure()
    {
        return $this->hasMany(FeesStructure::class, 'batch_id', 'batch');
    }

    function feepayment()
    {
        return $this->hasMany(StudentPayment::class, 'student_id', 'id');
    }



    function programgroup()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'programme');
    }

    // Total amount for this fee structure
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }


    function studentsyllabusinfo()
    {
        return $this->hasMany(StudentSyllabusInfo::class, 'student_id', 'id');
    }

    function address()
    {
        return $this->hasOne(StudentAddress::class, 'student_id', 'id');
    }

    function stdprogramenrolled()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'new_program_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'DEPARTMENT',
        'USER_CODE',
        'FIRST_NAME',
        'MIDDLE_NAME',
        'LAST_NAME',
        'GENDER',
        'MAIL_ID',
        'MOBILE_NO',
        'ADDRESS',
        'DOB',
        'DOJ',
        'DOL',
        'IS_LEFT',
        'photo',
    ];

    // function timetablepivot(){
    //     return $this->hasMany(SyllabusHasFaculty::class,'faculty_id','id');
    // }

    //    function deptmaster(){
    //     return $this->hasOne(Department::class,'id','department_id');
    // }

    function nationality()
    {
        return $this->hasOne(NationalityMaster::class, 'id', 'NATIONALITY');
    }

    function useraccess()
    {
        return $this->hasOne(User::class, 'id', 'faculty_id');
    }

    /**
     * Get salary slips for this faculty
     */
    public function salarySlips()
    {
        return $this->hasMany(FacultySalarySlip::class, 'faculty_id');
    }

    /**
     * Get loans for this faculty
     */
    public function loans()
    {
        return $this->hasMany(FacultyLoan::class, 'faculty_id');
    }

    /**
     * Get active loans
     */
    public function activeLoans()
    {
        return $this->hasMany(FacultyLoan::class, 'faculty_id')->where('status', 'active');
    }
}

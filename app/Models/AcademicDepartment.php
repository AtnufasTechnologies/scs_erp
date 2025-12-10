<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicDepartment extends Model
{
    use HasFactory, SoftDeletes;

    public function annualsession()
    {
        return $this->hasOne(AnnualSession::class, 'id', 'session_id');
    }
    public function campus()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    public function program()
    {
        return $this->hasOne(MainProgram::class, 'id', 'main_program_id');
    }

    public function coresubject()
    {
        return $this->hasOne(Subject::class, 'id', 'core_subject_id');
    }

    public function combinations()
    {
        return $this->hasMany(DeptOfferSubjectCombination::class, 'dept_id', 'id');
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'department_id', 'id');
    }

    public function deptmaster()
    {
        return $this->hasOne(DepartmentMaster::class, 'id', 'dept_id');
    }
}

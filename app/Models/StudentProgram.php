<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgram extends Model
{
    use HasFactory;
    protected $table = "student_program";

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function applicationmaster()
    {
        return $this->hasMany(AdmissionApplication::class, 'course', 'id')->with('registrationmaster.programinfo.campus');
    }
    function applicationCount()
    {
        return $this->hasMany(AdmissionApplication::class, 'course', 'id');
    }

    function departmentmaster()
    {
        return $this->hasOne(DepartmentMaster::class, 'id', 'department');
    }
    function programgroup()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'programme');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCourseMaster extends Model
{
    use HasFactory;

    function semestermaster()
    {
        return  $this->hasOne(Semester::class, 'id', 'SEMESTER_ID');
    }

    function departmentmaster()
    {
        return  $this->hasOne(DepartmentMaster::class, 'id', 'DEPARTMENT');
    }

    function coursetypemaster()
    {
        return  $this->hasOne(SubjectTypeMaster::class, 'id', 'COURSE_TYPE');
    }
}

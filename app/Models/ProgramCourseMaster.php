<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCourseMaster extends Model
{
    use HasFactory;

    function semestermaster()
    {
        return  $this->hasOne(Semester::class, 'id', 'semester_id');
    }

    function departmentmaster()
    {
        return  $this->hasOne(DepartmentMaster::class, 'id', 'department');
    }

    function coursetypemaster()
    {
        return  $this->hasOne(SubjectTypeMaster::class, 'id', 'course_type');
    }

    function stucourseinfo()
    {
        return  $this->hasMany(StudentCourseInfo::class, 'course_id', 'id');
    }

    function papertypemaster()
    {
        return  $this->hasOne(PaperTypeMaster::class, 'id', 'paper_type');
    }

    function csos()
    {
        return $this->hasMany(CoHasCso::class, 'co_id', 'id');
    }
}

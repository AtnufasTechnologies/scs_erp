<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramCourseMaster extends Model
{
    use HasFactory, SoftDeletes;

    //   protected $table = 'program_course_master_new';
    protected $fillable = [
        'department',
        'academic_year',
        'course_type',
        'hrs_per_week',
        'total_alloted_hours',
        'credits',
        'course_title',
        'course_code',
        'semester_id',
        'internal',
        'total',
        'paper_type',
        'paper_type_id',
        'exam_course_type',
        'exam_course_type',
        'is_active',
        'is_nme_subject',
        'ugc_course_type',
        'is_compulsary',
        'course_order',
        'is_deleted'



    ];


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
        return  $this->hasOne(PaperTypeMaster::class, 'id', 'paper_type_id');
    }

    function csos()
    {
        return $this->hasMany(CoHasCso::class, 'co_id', 'id');
    }
}

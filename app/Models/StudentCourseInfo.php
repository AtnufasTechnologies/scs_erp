<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseInfo extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'student_course_infos';

    protected $fillable = [
        'student_id',
        'course_id',
        'semester',
        'allocation_group_id',
        'campus_id',
        'is_active',
        'academic_year',
        'class_id',
        's_class_id',
        'is_elective',
        'paper_code_id',
        'staff_id',
    ];

    function coursemaster()
    {
        return  $this->hasOne(ProgramCourseMaster::class, 'id', 'course_id')->with('papertypemaster');
    }
}

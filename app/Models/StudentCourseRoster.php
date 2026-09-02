<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseRoster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'routine_id',
        'ta_id',
        'course_id',
        'subject_id',
        'syllabus_id',
        'batch_id',
        'semester_id',
        'program_type',
        'student_id',
    ];

    function studentmaster()
    {
        return $this->hasOne(StudentMaster::class, 'id', 'student_id')->with('campusmaster');
    }

    function teaching_assign()
    {
        return $this->hasOne(TeachingAssignment::class, 'id', 'ta_id');
    }

    function course()
    {
        return $this->hasOne(ProgramCourseMaster::class, 'id', 'course_id')->with('coursetypemaster');
    }
}

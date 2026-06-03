<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDualtoSingleCourseInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'code',
        'name',
    ];

    function coursemaster()
    {
        return $this->hasOne(ProgramCourseMaster::class, 'course_id', 'id');
    }
}

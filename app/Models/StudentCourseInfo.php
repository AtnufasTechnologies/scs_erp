<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourseInfo extends Model
{
    use HasFactory;

    function coursemaster()
    {
        return  $this->hasOne(ProgramCourseMaster::class, 'id', 'course_id');
    }
}

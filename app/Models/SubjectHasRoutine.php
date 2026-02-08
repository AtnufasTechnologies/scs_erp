<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectHasRoutine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'syllabus_id',
        'weekday_id',
        'hour_id',
        'lecturehall_id',
        'faculty_id',
        'subject_course_id',
        'substitution_faculty_id'
    ];

    function weekdaymaster()
    {
        return $this->hasOne(Weekday::class, 'id', 'weekday_id');
    }

    function hourmaster()
    {
        return $this->hasOne(HourMaster::class, 'id', 'hour_id');
    }

    function lecturehallmaster()
    {
        return $this->hasOne(LectureHallMaster::class, 'id', 'lecturehall_id');
    }

    function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
    }

    function substitutionFaculty()
    {
        return $this->belongsTo(Faculty::class, 'substitution_faculty_id', 'id');
    }

    function subjectCourse()
    {
        return $this->belongsTo(SubjectCourseMaster::class, 'subject_course_id', 'id');
    }
}

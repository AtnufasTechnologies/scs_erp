<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasSyllabus extends Model
{
    use HasFactory;

    function sessionmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'session_id');
    }

    function semestermaster()
    {
        return $this->hasOne(Semester::class, 'id', 'semester_id');
    }
    function subtypemaster()
    {
        return $this->hasOne(SubjectTypeMaster::class, 'id', 'subject_type_id');
    }

    function timetable()
    {
        return $this->hasOne(SubjectHasRoutine::class, 'syllabus_id', 'id');
    }
}

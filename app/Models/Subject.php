<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function syllabus()
    {
        return $this->hasMany(SubjectHasSyllabus::class, 'subject_id', 'id');
    }

    function semesters()
    {
        return $this->hasMany(SubjectHasSemester::class, 'subject_id', 'id');
    }


    function programs()
    {
        return $this->hasMany(SubjectHasStudentProgam::class, 'subject_id', 'id');
    }
}

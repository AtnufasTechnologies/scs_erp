<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    function program_master()
    {
        return $this->hasOne(ProgramMaster::class, 'id', 'program_id');
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
